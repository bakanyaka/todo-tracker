<?php


namespace App\Services;


use App\Models\Issue;
use Carbon\Carbon;

class IssueStatsService
{
    public function getIssuesSummaryPerDay($startDate, $endDate)
    {
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        if ($endDateCarbon->lt($startDateCarbon)) {
            return [];
        }

        $zeroDates = collect();
        for ($d = $endDateCarbon->copy()->subDays(1); $d->gte($startDateCarbon); $d->subDays(1)) {
            $date = $d->toDateString();
            $zeroDates[$date] = [
                'x' => $date,
                'y' => 0
            ];
        }

        $issuesCreated = Issue::where('created_on', '>', $startDate)
            ->where('created_on', '<', $endDate)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this,"mapToChartData"])->keyBy('x');

        $issuesClosed = Issue::where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this,"mapToChartData"])->keyBy('x');

        $issuesClosedFirstLine = Issue::where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->where('status_id', 8)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this,"mapToChartData"])->keyBy('x');

        $overDueIssues = Issue::Closed()
            ->where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->get()->filter(function (Issue $issue) {
                return $issue->due_date !== null && $issue->time_left < 0;
            })->groupBy(function (Issue $issue) {
                return $issue->closed_on->toDateString();
            })->map(function ($items, $key) {
                return [
                    'x' => $key,
                    'y' => $items->count()
                ];
            });

        $closedInTimeIssues = Issue::Closed()
            ->where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->get()->filter(function (Issue $issue) {
                return $issue->due_date !== null && $issue->time_left >= 0;
            })->groupBy(function (Issue $issue) {
                return $issue->closed_on->toDateString();
            })->map(function ($items, $key) {
                return [
                    'x' => $key,
                    'y' => $items->count()
                ];
            });

        $issuesCreated = $zeroDates->merge($issuesCreated)->values();
        $issuesClosed = $zeroDates->merge($issuesClosed)->values();
        $issuesClosedFirstLine = $zeroDates->merge($issuesClosedFirstLine)->values();
        $overDueIssues = $zeroDates->merge($overDueIssues)->values();
        $closedInTimeIssues = $zeroDates->merge($closedInTimeIssues)->values();

        return [
            'created' => [
                'total' => $issuesCreated->sum('y'),
                'data' => $issuesCreated
            ],
            'closed' => [
                'total' => $issuesClosed->sum('y'),
                'data' => $issuesClosed
            ],
            'closed_first_line' => [
                'total' => $issuesClosedFirstLine->sum('y'),
                'data' => $issuesClosedFirstLine
            ],
            'closed_overdue' => [
                'total' => $overDueIssues->sum('y'),
                'data' => $overDueIssues
            ],
            'closed_in_time' => [
                'total' => $closedInTimeIssues->sum('y'),
                'data' => $closedInTimeIssues
            ]
        ];
    }


    public function mapToChartData($item)
    {
        return [
            'x' => $item->date,
            'y' => (int)$item->count
        ];
    }
}