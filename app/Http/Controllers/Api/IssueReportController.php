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
        $periodStartDate = $request->input('period_from_date',Carbon::now()->subDays(7)->toDateString());
        $periodEndDate = $request->input('period_to_date',Carbon::now()->toDateString());

        return response()->json([
            'data' => $this->issueStatsService->getIssuesSummaryPerDay($periodStartDate, $periodEndDate)
        ]);
    }

    public function byProject(Request $request)
    {
        $periodStartDate = $request->input('period_from_date',Carbon::now()->subDays(7)->toDateString());
        $periodEndDate = $request->input('period_to_date',Carbon::now()->toDateString());

        return response()->json([
            'data' => $this->issueStatsService->getIssuesReportPerProject($periodStartDate,$periodEndDate)
        ]);
    }
}
