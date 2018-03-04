<?php

namespace App\Policies;

use App\User;
use App\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create, update or delete services.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function touch(User $user)
    {
        return $user->is_admin;
    }


}
