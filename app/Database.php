<?php

namespace App;

use Illuminate\Support\Str;
use App\Traits\Provisionable;
use App\Jobs\DatabaseDestroyJob;
use App\Jobs\DatabaseProvisionJob;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Encryption\DecryptException;

class Database extends Model implements ProvisionableResource
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'password' => ['nullable', 'min:8']
    ];

    /**
     * Get the server that owns the database.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->sysuser->server();
    }

    /**
     * Get the sysuser that the database belongs to.
     * 
     * @return BelongsTo
     */
    public function sysuser()
    {
        return $this->belongsTo(Sysuser::class, 'sysuser_id');
    }

    /**
     * Get the site that the database belongs to.
     * 
     * @return BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = encrypt($value);
    }

    /**
     * Get the backup path for the resource.
     * 
     * @return string
     */
    public function getBackupPath()
    {
        return '/opt/sitepilot/backups/mysql/' . $this->name . '.sql';
    }

    /**
     * Get the backups for the resource.
     * 
     * @return MorphMany
     */
    public function backups()
    {
        return $this->morphMany(Backup::class, 'backupable');
    }

    /**
     * Dispatch database provision job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(DatabaseProvisionJob::class);
    }

    /**
     * Dispatch database destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(DatabaseDestroyJob::class);
    }
}
