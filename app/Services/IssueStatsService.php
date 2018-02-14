<?php


namespace App\Services;


use App\Models\Issue;
use App\Models\Project;
use Carbon\Carbon;

class IssueStatsService
{
    public function getIssuesSummaryPerDay($startDate, $endDate)
    {
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        if ($endDateCarbon->lt($startDateCarbon)) {
            return [];
        }

        $zeroDates = collect();
        for ($d = $endDateCarbon->copy()->subDays(1); $d->gte($startDateCarbon); $d->subDays(1)) {
            $date = $d->toDateString();
            $zeroDates[$date] = [
                'x' => $date,
                'y' => 0
            ];
        }

        $issuesCreated = Issue::where('created_on', '>', $startDate)
            ->where('created_on', '<', $endDate)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $issuesClosed = Issue::where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $issuesClosedFirstLine = Issue::where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
            ->where('status_id', 8)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map([$this, "mapToChartData"])->keyBy('x');

        $overDueIssues = Issue::Closed()
            ->where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
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
            ->where('closed_on', '>', $startDate)
            ->where('closed_on', '<', $endDate)
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
        $issuesClosed =  $this->getCountOfIssuesInStatusPerProject('closed', $startDate, $endDate);
        $issuesClosedInTime = $this->getCountOfIssuesInStatusPerProject('closed_in_time', $startDate, $endDate);
        $issuesClosedOverdue = $this->getCountOfIssuesInStatusPerProject('closed_overdue', $startDate, $endDate);

        $projects = Project::all()
            ->map(function ($project) use ($issuesCreated, $issuesClosed, $issuesClosedInTime, $issuesClosedOverdue) {
                return [
                    'project' => $project->name,
                    'project_id' => $project->id,
                    'parent_project_id' => $project->parent_id,
                    'children' => collect(),
                    'created' => $issuesCreated->get($project->name, 0),
                    'closed' => $issuesClosed->get($project->name, 0),
                    'closed_in_time' => $issuesClosedInTime->get($project->name, 0),
                    'closed_overdue' => $issuesClosedOverdue->get($project->name, 0)
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
                ->selectRaw('projects.name as project, COUNT(*) as count')
                ->groupBy('project')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item['project'] => (int)$item['count']];
                });
        } elseif ($status === 'closed') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->join('projects', 'issues.project_id', '=', 'projects.id')
                ->selectRaw('projects.name as project, COUNT(*) as count')
                ->groupBy('project')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item['project'] => (int)$item['count']];
                });
        } elseif ($status === 'closed_in_time') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->with('project')
                ->get()
                ->filter(function (Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left >= 0;
                })->groupBy(function (Issue $issue) {
                    return optional($issue->project)->name;
                })->map->count();
        } elseif ($status === 'closed_overdue') {
            $issues = Issue::closedWithin($startDate, $endDate)
                ->with('project')
                ->get()
                ->filter(function (Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left < 0;
                })->groupBy(function (Issue $issue) {
                    return optional($issue->project)->name;
                })->map->count();
        }
        return $issues;
    }

    private function addChildProjectIssuesRecursive($project, $projects)
    {
        $childProjects = $projects->where('parent_project_id',$project['project_id']);
        foreach ($childProjects as $childProject) {
            $childProject = $this->addChildProjectIssuesRecursive($childProject, $projects);
            $project['created'] += $childProject['created'];
            $project['closed'] += $childProject['closed'];
            $project['closed_in_time'] += $childProject['closed_in_time' ];
            $project['closed_overdue'] += $childProject['closed_overdue'];
            $project['children'][] = $childProject;
        }
        return $project;
    }

}