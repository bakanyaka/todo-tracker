<?php

namespace App\Http\Controllers\Api;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssueStatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $openIssues = Issue::open()->get();
        $pausedIssuesCount = Issue::paused()->count();
        $overDueIssuesCount = $openIssues->filter(function(Issue $issue) {
            return $issue->due_date !== null && $issue->time_left < 0;
        })->count();
        $dueSoonIssuesCount = $openIssues->filter(function (Issue $issue) {
            if ($issue->due_date === null) {
                return false;
            }
            return $issue->due_date->toDateString() === Carbon::now()->toDateString() && $issue->percent_of_time_left < 30 && $issue->percent_of_time_left > 0;
        })->count();
        $createdTodayIssuesCount = Issue::where('created_on','>=', Carbon::today())->count();
        $closedTodayIssuesCount = Issue::where('closed_on','>=', Carbon::today())->count();
        $inProcurementIssuesCount = Issue::open()->where('assigned_to', 'Отдел Закупок')->count();

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
