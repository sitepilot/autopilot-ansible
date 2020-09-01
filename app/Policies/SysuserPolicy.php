<?php

namespace App\Policies;

use App\Sysuser;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SysuserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Sysuser  $sysuser
     * @return mixed
     */
    public function view(User $user, Sysuser $sysuser)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Sysuser  $sysuser
     * @return mixed
     */
    public function update(User $user, Sysuser $sysuser)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Sysuser  $sysuser
     * @return mixed
     */
    public function delete(User $user, Sysuser $sysuser)
    {
        return !$sysuser->sites->count() && !$sysuser->siteMounts->count();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Sysuser  $sysuser
     * @return mixed
     */
    public function restore(User $user, Sysuser $sysuser)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Sysuser  $sysuser
     * @return mixed
     */
    public function forceDelete(User $user, Sysuser $sysuser)
    {
        return false;
    }
}
