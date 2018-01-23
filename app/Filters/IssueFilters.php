<?php


namespace App\Filters;


use App\User;
use Carbon\Carbon;

class IssueFilters extends Filters
{
    /**
     * Registered filters to operate upon
     *
     * @var array
     */
    protected $filters = ['user', 'status','created_after','created_before','period'];

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
            return $this->builder->markedForControl();
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
        if($status === 'all') {
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
        if($date === null) {
            return $this->builder;
        }
        $dt = Carbon::parse($date);
        return $this->builder->where('created_on', '>=', $dt->toDateString());
    }

    /**
     * Filter the issues according to those that were created before given date
     *
     * @param string $date
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function created_before($date)
    {
        if($date === null) {
            return $this->builder;
        }
        $dt = Carbon::parse($date);
        return $this->builder->where('created_on', '<=', $dt->toDateString());
    }

    /**
     * Filter the issues according to those that were created or closed within given period
     *
     * @param int $days
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    public function period($days)
    {
        if($days === null) {
            return $this->builder;
        }
        $date = now()->subDays($days-1)->toDateString();
        if ($this->request->status === 'closed') {
            return $this->builder->where('closed_on', '>', $date);
        }
        return $this->builder->where('created_on', '>', $date);
    }
}