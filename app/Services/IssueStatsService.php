<?php


namespace App\Services;


use App\Models\Issue;
use App\Models\Project;
use Carbon\Carbon;
use Hamcrest\Core\Is;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IssueStatsService
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function getIssuesSummaryPerDay()
    {
        $startDate = $this->request->input('period_from_date',Carbon::now()->subDays(7)->toDateString());
        $endDate = $this->request->input('period_to_date',Carbon::now()->toDateString());

        $issuesQuery = Issue::query();

        if ($this->request->has('project_id')) {
            $projects = Project::with(['children', 'children.children'])->where('id', $this->request->project_id)->get()->recursivePluck('id')->toArray();
            $issuesQuery->whereIn('project_id', $projects);
        }

        if ($this->request->has('tracker_id')) {
            $issuesQuery->whereIn('tracker_id', Arr::wrap($this->request->tracker_id));
        }

        if ($this->request->has('has_service')) {
            $issuesQuery->whereNotNull('service_id');
        }

        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        if ($endDateCarbon->lt($startDateCarbon)) {
            return [];
        }

        $zeroDates = collect();
        for ($d = $endDateCarbon->copy(); $d->gte($startDateCarbon); $d->subDays(1)) {
            $date = $d->toDateString();
            $zeroDates[$date] = [
                'x' => $date,
                'y' => 0
            ];
        }

        $issuesCreated = (clone $issuesQuery)->whereDate('created_on', '>=', $startDate)
            ->whereDate('created_on', '<=', $endDate)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $issuesClosed = (clone $issuesQuery)->whereDate('closed_on', '>=', $startDate)
            ->whereDate('closed_on', '<=', $endDate)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $issuesClosedFirstLine = (clone $issuesQuery)->whereDate('closed_on', '>=', $startDate)
            ->whereDate('closed_on', '<=', $endDate)
            ->where('status_id', 8)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $overDueIssues = (clone $issuesQuery)->closed()
            ->whereDate('closed_on', '>=', $startDate)
            ->whereDate('closed_on', '<=', $endDate)
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

        $closedInTimeIssues = (clone $issuesQuery)->closed()
            ->whereDate('closed_on', '>=', $startDate)
            ->whereDate('closed_on', '<=', $endDate)
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

    public function getIssuesReportPerProject($startDate, $endDate)
    {
        $issuesCreated = $this->getCountOfIssuesInStatusPerProject('created', $startDate, $endDate);
        $issuesClosed = $this->getCountOfIssuesInStatusPerProject('closed', $startDate, $endDate);
        $issuesClosedInTime = $this->getCountOfIssuesInStatusPerProject('closed_in_time', $startDate, $endDate);
        $issuesClosedOverdue = $this->getCountOfIssuesInStatusPerProject('closed_overdue', $startDate, $endDate);

        $projects = Project::all()
            ->map(function ($project) use ($issuesCreated, $issuesClosed, $issuesClosedInTime, $issuesClosedOverdue) {
                return [
                    'project' => $project->name,
                    'project_id' => $project->id,
                    'parent_project_id' => $project->parent_id,
                    'children' => collect(),
                    'created' => $issuesCreated->get($project->id, 0),
                    'closed' => $issuesClosed->get($project->id, 0),
                    'closed_in_time' => $issuesClosedInTime->get($project->id, 0),
                    'closed_overdue' => $issuesClosedOverdue->get($project->id, 0)
                ];
            });

        $projectsTree = $projects->filter(function ($project) {
            return $project['parent_project_id'] === null;
        })->map(function ($project) use ($projects) {
            return $this->addChildProjectIssuesRecursive($project, $projects);
        });

        return $projectsTree->sortByDesc('created')->values();
    }

    public function getCountOfIssuesInStatusPerProject($status, $startDate, $endDate)
    {
        $issues = collect();
        if ($status === 'created') {
            $issues = Issue::createdWithin($startDate, $endDate)
                ->join('projects', 'issues.project_id', '=', 'projects.id')
                ->selectRaw('projects.id as project_id, COUNT(*) as count')
                ->groupBy('projects.id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item['project_id'] => (int)$item['count']];
                });
        } elseif ($status === 'closed') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->join('projects', 'issues.project_id', '=', 'projects.id')
                ->selectRaw('projects.id as project_id, COUNT(*) as count')
                ->groupBy('projects.id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item['project_id'] => (int)$item['count']];
                });
        } elseif ($status === 'closed_in_time') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->with('project')
                ->get()
                ->filter(function (Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left >= 0;
                })->groupBy(function (Issue $issue) {
                    return optional($issue->project)->id;
                })->map->count();
        } elseif ($status === 'closed_overdue') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->with('project')
                ->get()
                ->filter(function (Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left < 0;
                })->groupBy(function (Issue $issue) {
                    return optional($issue->project)->id;
                })->map->count();
        }
        return $issues;
    }

    private function addChildProjectIssuesRecursive($project, $projects)
    {
        $childProjects = $projects->where('parent_project_id', $project['project_id']);
        foreach ($childProjects as $childProject) {
            $childProject = $this->addChildProjectIssuesRecursive($childProject, $projects);
            $project['created'] += $childProject['created'];
            $project['closed'] += $childProject['closed'];
            $project['closed_in_time'] += $childProject['closed_in_time'];
            $project['closed_overdue'] += $childProject['closed_overdue'];
            $project['children'][] = $childProject;
        }
        return $project;
    }

}
