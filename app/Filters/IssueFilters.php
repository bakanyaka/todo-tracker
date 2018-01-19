<?php


namespace App\Filters;


use App\User;

class IssueFilters extends Filters
{
    /**
     * Registered filters to operate upon
     *
     * @var array
     */
    protected $filters = ['user', 'status'];

    public function user($username)
    {
        if ($username === null) {
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
            $user = User::whereUsername($username)->firstOrFail();
            return $this->builder->where('user_id', $user->id);
        }
    }

    public function status($status)
    {
        if($status === 'all') {
            return $this->builder;
        } elseif ($status === 'closed') {
            return $this->builder->closed();
        }
        return $this->builder->open();
    }
}