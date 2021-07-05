<?php


namespace App\Filters;


use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class IssueFilters extends Filters
{
    /**
     * Registered filters to operate upon
     *
     * @var array
     */
    protected $filters = [
        'user',
        'assigned_to',
        'status',
        'created_after',
        'created_before',
        'period',
        'period_from_date',
        'period_to_date',
        'project',
        'tracker',
        'has_service',
    ];

    /**
     * Filter issues by assignee it is assigned to
     * @param $assigneeName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function assigned_to($assigneeName)
    {
        if ($assigneeName === null) {
            return $this->builder;
        }
        if ($assigneeName === 'Отдел Закупок') {
            return $this->builder->withoutGlobalScope('notInProcurement')->inProcurement();
        }
        return $this->builder->where('assigned_to', $assigneeName);
    }

    /**
     * Filter issues by user who tracks it
     *
     * @param string $username
     * @return $this|\Illuminate\Database\Eloquent\Builder|static
     */
    public function user($username)
    {
        if ($username === null) {
            return $this->builder;
        } elseif ($username === 'me') {
            $user = auth()->user();
            /*            return $this->builder->whereHas('users', function ($query) use ($user) {
                            $query->where('id', $user->id);
                        });*/
            $this->builder = $user->issues()->getQuery()->mergeConstraintsFrom($this->builder);
            return $this->builder;
        } elseif ($username === 'all') {
            return $this->builder->has('users');
        } elseif ($username === 'control') {
            return $this->builder;
        } else {
            return $this->builder->whereHas('users', function ($query) use ($username) {
                $query->where('name', $username);
            });
        }
    }

    /**
     * Filter the issues by status
     *
     * @param $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function status($status)
    {
        if ($status === 'all') {
            return $this->builder;
        } elseif ($status === 'closed') {
            return $this->builder->closed();
        } elseif ($status === 'paused') {
            return $this->builder->paused();
        }
        return $this->builder->open();
    }

    /**
     * Filter the issues according to those that were created after given date
     *
     * @param string $date
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function created_after($date)
    {
        if ($date === null) {
            return $this->builder;
        }
        $dt = Carbon::parse($date);
        return $this->builder->whereDate('created_on', '>=', $dt->toDateString());
    }

    /**
     * Filter the issues according to those that were created before given date
     *
     * @param string $date
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function created_before($date)
    {
        if ($date === null) {
            return $this->builder;
        }
        $dt = Carbon::parse($date);
        return $this->builder->whereDate('created_on', '<=', $dt->toDateString());
    }

    /**
     * Filter the issues according to those that were created or closed within given period
     *
     * @param int $days
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function period($days)
    {
        if ($days === null) {
            return $this->builder;
        }
        $dateFrom = now()->subDays($days)->toDateString();
        $dateTo = $days === "0" ? now() : now()->toDateString();
        if ($this->request->status === 'closed') {
            $this->builder->where('closed_on', '>', $dateFrom);
            $this->builder->where('closed_on', '<', $dateTo);
            return $this->builder;
        }
        $this->builder->where('created_on', '<', $dateTo);
        $this->builder->where('created_on', '>', $dateFrom);
        return $this->builder;
    }

    /**
     * Filter the issues according to those that were created or closed after given date
     *
     * @param string $dateFrom
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function period_from_date($dateFrom)
    {
        if ($dateFrom === null) {
            return $this->builder;
        }

        if ($this->request->status === 'closed') {
            $this->builder->whereDate('closed_on', '>=', $dateFrom);
            return $this->builder;
        }
        $this->builder->whereDate('created_on', '>=', $dateFrom);
        return $this->builder;
    }

    /**
     * Filter the issues according to those that were created or closed before given date
     *
     * @param int $days
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function period_to_date($dateTo)
    {
        if ($dateTo === null) {
            return $this->builder;
        }
        if ($this->request->status === 'closed') {
            $this->builder->whereDate('closed_on', '<=', $dateTo);
            return $this->builder;
        }
        $this->builder->whereDate('created_on', '<=', $dateTo);
        return $this->builder;
    }

    public function project($projectId)
    {
        if ($projectId === null) {
            return $this->builder;
        }
        if ($this->request->include_subprojects === 'yes') {
            $projects = Project::with(['children', 'children.children'])->where('id', $projectId)->get()->recursivePluck('id')->toArray();
            return $this->builder->whereIn('project_id', $projects);
        } else {
            return $this->builder->where('project_id', $projectId);
        }
    }

    public function tracker($trackerId)
    {
        if ($trackerId === null) {
            return $this->builder;
        }
        return $this->builder->whereIn('tracker_id', Arr::wrap($trackerId));
    }

    public function has_service($hasServiceFlag)
    {
        if ($hasServiceFlag === null) {
            return $this->builder;
        }
        if ($hasServiceFlag) {
            $this->builder->whereNotNull('service_id');
        } else {
            $this->builder->whereNull('service_id');
        }
    }
}
