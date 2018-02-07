<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Project;
use App\Services\IssueStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IssueReportController extends Controller
{
    /**
     * @var IssueStatsService
     */
    protected $issueStatsService;


    /**
     * IssueReportController constructor.
     * @param IssueStatsService $issueStatsService
     */
    public function __construct(IssueStatsService $issueStatsService)
    {
        $this->issueStatsService = $issueStatsService;
    }

    public function index(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStartDate = Carbon::now()->subDays($periodDays)->toDateString();
        $periodEndDate = Carbon::now()->toDateString();

        return response()->json([
            'data' => $this->issueStatsService->getIssuesSummaryPerDay($periodStartDate, $periodEndDate)
        ]);
    }

    public function byProject(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStart = Carbon::now()->subDays($periodDays)->toDateString();
        $periodEnd = Carbon::now()->toDateString();

        return response()->json([
            'data' => $this->issueStatsService->getIssuesReportPerProject($periodStart,$periodEnd)
        ]);
    }
}
