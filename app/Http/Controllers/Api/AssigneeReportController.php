<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IssueCollection;
use App\Models\Issue;
use App\Models\Assignee;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssigneeReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $periodStartDate = $request->input('period_from_date', Carbon::now()->subDays(7)->toDateString());
        $periodEndDate = $request->input('period_to_date', Carbon::now()->toDateString());

        $spentIssuesCount = TimeEntry::spentWithin($periodStartDate, $periodEndDate)
            ->join('issues', 'time_entries.issue_id', '=', 'issues.id')
            ->groupBy('assignee_id')
            ->select(DB::raw('count(DISTINCT issue_id) as spent_issues_count'))
            ->addSelect('assignee_id')
            ->get()
            ->keyBy('assignee_id');

        $assignedIssuesCount = Issue::active()
            ->whereDate('created_on', '<', Carbon::yesterday()->toDateString())
            ->groupBy('assigned_to_id')
            ->select(DB::raw('count(*) as assigned_issues_count'))
            ->addSelect('assigned_to_id as assignee_id')
            ->get()
            ->keyBy('assignee_id');

        $result = Assignee::all()->map(function ($item) use ($spentIssuesCount, $assignedIssuesCount) {
            $item['spent_issues_count'] = (int)data_get($spentIssuesCount, $item['id'] . '.spent_issues_count', 0);
            $item['participated_issues_count'] = (int)data_get($spentIssuesCount, $item['id'] . '.spent_issues_count', 0)
                + (int)data_get($assignedIssuesCount, $item['id'] . '.assigned_issues_count', 0);
            return $item;
        })->sortBy('lastname')->values();

        return response()->json([
            'data' => $result
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param Assignee $assignee
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Assignee $assignee, Request $request)
    {
        $periodStartDate = $request->input('period_from_date', Carbon::now()->subDays(7)->toDateString());
        $periodEndDate = $request->input('period_to_date', Carbon::now()->toDateString());

        $spentIssues = Issue::whereIn('id', function ($query) use ($periodStartDate, $periodEndDate) {
            $query->select('issue_id')
                ->from('time_entries')
                ->whereDate('spent_on', '>=', $periodStartDate)
                ->whereDate('spent_on', '<=', $periodEndDate)
                ->distinct();
        })
            ->get()
            ->sortBy('id')
            ->values();

        return response()->json(['data' => [
            'id' => (int)$assignee->id,
            'issues' => [
                'spent' => \App\Http\Resources\Issue::collection($spentIssues)
            ]
        ]], 200);
    }

}
