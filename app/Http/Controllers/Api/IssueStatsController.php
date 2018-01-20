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
            return $issue->time_left < 0;
        })->count();
        $dueTodayIssuesCount = $openIssues->filter(function (Issue $issue) {
            return $issue->due_date->toDateString() === Carbon::now()->toDateString();
        })->count();
        $createdTodayIssuesCount = Issue::where('created_on','>=', Carbon::today())->count();
        $closedTodayIssuesCount = Issue::where('closed_on','>=', Carbon::today())->count();

        return response()->json([
            'data' => [
                'open' => $openIssues->count(),
                'paused' => $pausedIssuesCount,
                'overdue' => $overDueIssuesCount,
                'due_today' => $dueTodayIssuesCount,
                'created_today' => $createdTodayIssuesCount,
                'closed_today' => $closedTodayIssuesCount
            ]
        ]);
    }

}
