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
