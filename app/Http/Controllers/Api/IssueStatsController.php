<?php

namespace App\Http\Controllers\Api;

use App\Models\Issue;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssueStatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'project_id' => 'integer|exists:projects,id'
        ]);
        $issues = Issue::query();
        if ($request['project_id']) {
            $project_with_children_ids = Project::with(['children', 'children.children'])->where('id', $request['project_id'])->get()->recursivePluck('id')->toArray();
            $issues->whereIn('project_id', $project_with_children_ids);
        }
        $openIssues = with(clone $issues)->open()->get();
        $pausedIssuesCount = with(clone $issues)->paused()->count();
        $overDueIssuesCount = $openIssues->filter(function(Issue $issue) {
            return $issue->due_date !== null && $issue->time_left < 0;
        })->count();
        $dueSoonIssuesCount = $openIssues->filter(function (Issue $issue) {
            if ($issue->due_date === null) {
                return false;
            }
            return $issue->due_date->toDateString() === Carbon::now()->toDateString() && $issue->percent_of_time_left < 30 && $issue->percent_of_time_left > 0;
        })->count();
        $createdTodayIssuesCount = with(clone $issues)->where('created_on','>=', Carbon::today())->count();
        $closedTodayIssuesCount = with(clone $issues)->closed()->where('closed_on','>=', Carbon::today())->count();
        $inProcurementIssuesCount = with(clone $issues)->withoutGlobalScopes()->open()->inProcurement()->count();

        return response()->json([
            'data' => [
                'open' => $openIssues->count(),
                'paused' => $pausedIssuesCount,
                'overdue' => $overDueIssuesCount,
                'due_soon' => $dueSoonIssuesCount,
                'created_today' => $createdTodayIssuesCount,
                'closed_today' => $closedTodayIssuesCount,
                'in_procurement' => $inProcurementIssuesCount
            ]
        ]);
    }

}
