<?php

namespace App;

use App\Jobs\BackupRunJob;
use Illuminate\Support\Str;
use App\Traits\Provisionable;
use App\Jobs\BackupDestroyJob;
use App\Jobs\BackupRestoreJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use Provisionable, SoftDeletes;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending'
    ];

    /** 
     * Get the server that owns the backup.
     * 
     * @return Server
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Get the owning backupable model.
     * 
     * @return MorphTo
     */
    public function backupable()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Returns the backup tag.
     *
     * @return string
     */
    public function getBackupTag()
    {
        return 'backup-id-' . $this->id;
    }

    /**
     * Returns the backup resource tag.
     *
     * @return string
     */
    public function getBackupResourceTag()
    {
        return Str::slug($this->backupable_type) . '-id-' . $this->backupable_id;
    }

    /**
     * Dispatch backup run job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function start()
    {
        return $this->dispatchJob(BackupRunJob::class);
    }

    /**
     * Dispatch backup restore job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function restore()
    {
        return $this->dispatchJob(BackupRestoreJob::class);
    }

    /**
     * Dispatch backup destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(BackupDestroyJob::class);
    }
}
