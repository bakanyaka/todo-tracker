<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IssueReportController extends Controller
{
    public function index(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStart = Carbon::now()->subDays($periodDays)->toDateString();
        $periodEnd = Carbon::now()->toDateString();

        $zeroDates = collect();
        for ($d = $periodDays; $d > 0; $d--) {
            $date = now()->subDays($d)->toDateString();
            $zeroDates[$date] = [
                'x' => $date,
                'y' => 0
            ];
        }

        $issuesCreated = Issue::where('created_on', '>', $periodStart)
            ->where('created_on', '<', $periodEnd)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => (int)$item->count
                ];
            })->keyBy('x');

        $issuesClosed = Issue::where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => (int)$item->count
                ];
            })->keyBy('x');

        $issuesClosedFirstLine = Issue::where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->where('status_id', 8)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => (int)$item->count
                ];
            })->keyBy('x');

        $overDueIssues = Issue::Closed()
            ->where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
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
            ->where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
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

        return response()->json([
            'data' => [
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
            ]
        ]);
    }

    public function byProject(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStart = Carbon::now()->subDays($periodDays)->toDateString();
        $periodEnd = Carbon::now()->toDateString();

        $issuesOpen = Issue::where('created_on', '>', $periodStart)
            ->where('created_on', '<', $periodEnd)
            ->join('projects', 'issues.project_id', '=', 'projects.id')
            ->selectRaw('projects.name as project, COUNT(*) as count')
            ->groupBy('project')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item['project'] => (int)$item['count']];
            });

        $issuesClosed = Issue::closed()
            ->where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->join('projects', 'issues.project_id', '=', 'projects.id')
            ->selectRaw('projects.name as project, COUNT(*) as count')
            ->groupBy('project')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item['project'] => (int)$item['count']];
            });

        $issuesClosedInTime = Issue::closed()
            ->where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->with('project')
            ->get()
            ->filter(function (Issue $issue) {
                return $issue->due_date !== null && $issue->time_left >= 0;
            })->groupBy(function(Issue $issue) {
                return optional($issue->project)->name;
            })->map->count();

        $issuesClosedOverdue = Issue::closed()
            ->where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->with('project')
            ->get()
            ->filter(function (Issue $issue) {
                return $issue->due_date !== null && $issue->time_left < 0;
            })->groupBy(function(Issue $issue) {
                return optional($issue->project)->name;
            })->map->count();



        $issues = Project::all()
            ->pluck('name')
            ->unique()
            ->map(function ($project) use ($issuesOpen, $issuesClosed, $issuesClosedInTime, $issuesClosedOverdue) {
                return [
                    'project' => $project,
                    'created' => $issuesOpen->get($project, 0),
                    'closed' => $issuesClosed->get($project, 0),
                    'closed_in_time' => $issuesClosedInTime->get($project, 0),
                    'closed_overdue' => $issuesClosedOverdue->get($project, 0)
                ];
            })
            ->sortByDesc('created')
            ->values();

        return response()->json([
            'data' => $issues
        ]);
    }
}
