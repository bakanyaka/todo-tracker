<?php

namespace App\Http\Controllers\Api;

use App\Enums\OverdueState;
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
        $openIssues = $issues->clone()->open()->get();
        $pausedIssuesCount = $issues->clone()->paused()->count();
        $overDueIssuesCount = $openIssues->filter(fn(Issue $issue) => $issue->getOverdueState()->is(OverdueState::Yes))->count();
        $dueSoonIssuesCount = $openIssues->filter(fn(Issue $issue) => $issue->getOverdueState()->is(OverdueState::Soon))->count();
        $createdTodayIssuesCount = $issues->clone()->where('created_on','>=', Carbon::today())->count();
        $closedTodayIssuesCount = $issues->clone()->closed()->where('closed_on','>=', Carbon::today())->count();
        $inProcurementIssuesCount = $issues->clone()->withoutGlobalScopes()->open()->inProcurement()->count();

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
