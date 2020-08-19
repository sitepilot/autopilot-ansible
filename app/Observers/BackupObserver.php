<?php

namespace App\Observers;

use App\Backup;

class BackupObserver
{
    /**
     * Handle the backup "creating" event.
     *
     * @param  \App\Sysuser  $key
     * @return void
     */
    public function creating(Backup $backup)
    {
        if ($backup->server_id && !$backup->backupable_id) {
            $backup->backupable_id = $backup->server_id;
            $backup->backupable_type = Server::class;
        } else {
            $backup->server_id = $backup->backupable->server->id;
        }

        if (empty($backup->path)) $backup->path = $backup->backupable->getBackupPath();
    }

    /**
     * Handle the backup "created" event.
     *
     * @param  \App\Backup  $backup
     * @return void
     */
    public function created(Backup $backup)
    {
        if (!$backup->isReady()) {
            $backup->start();
        }
    }

    /**
     * Handle the backup "updated" event.
     *
     * @param  \App\Backup  $backup
     * @return void
     */
    public function updated(Backup $backup)
    {
        //
    }

    /**
     * Handle the backup "deleted" event.
     *
     * @param  \App\Backup  $backup
     * @return void
     */
    public function deleted(Backup $backup)
    {
        if (!$backup->isForceDeleting()) {
            $backup->deleteFromServer();
        }
    }

    /**
     * Handle the backup "restored" event.
     *
     * @param  \App\Backup  $backup
     * @return void
     */
    public function restored(Backup $backup)
    {
        //
    }

    /**
     * Handle the backup "force deleted" event.
     *
     * @param  \App\Backup  $backup
     * @return void
     */
    public function forceDeleted(Backup $backup)
    {
        //
    }
}
