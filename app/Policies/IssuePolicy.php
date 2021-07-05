<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IssuePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any issues.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the issue.
     *
     * @param  User  $user
     * @param  Issue  $issue
     * @return bool
     */
    public function view(User $user, Issue $issue)
    {
        //
    }

    /**
     * Determine whether the user can create issues.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the issue.
     *
     * @param  User  $user
     * @param  Issue  $issue
     * @return bool
     */
    public function update(User $user, Issue $issue)
    {
        //
    }

    /**
     * Determine whether the user can delete the issue.
     *
     * @param  User  $user
     * @param  Issue  $issue
     * @return bool
     */
    public function delete(User $user, Issue $issue)
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the issue.
     *
     * @param  User  $user
     * @param  Issue  $issue
     * @return bool
     */
    public function restore(User $user, Issue $issue)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the issue.
     *
     * @param  User  $user
     * @param  Issue  $issue
     * @return bool
     */
    public function forceDelete(User $user, Issue $issue)
    {
        //
    }
}
