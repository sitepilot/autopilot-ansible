<?php

namespace App\Policies;

use App\Backup;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupPolicy
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
     * @param  \App\Backup  $backup
     * @return mixed
     */
    public function view(User $user, Backup $backup)
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
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Backup  $backup
     * @return mixed
     */
    public function update(User $user, Backup $backup)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Backup  $backup
     * @return mixed
     */
    public function delete(User $user, Backup $backup)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Backup  $backup
     * @return mixed
     */
    public function restore(User $user, Backup $backup)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Backup  $backup
     * @return mixed
     */
    public function forceDelete(User $user, Backup $backup)
    {
        return false;
    }
}
