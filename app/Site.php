<?php

namespace App;

use App\Sysuser;
use App\Jobs\SiteDestroyJob;
use App\Traits\Provisionable;
use App\Jobs\SiteProvisionJob;
use App\Jobs\SiteMountToSysuserJob;
use App\Jobs\SiteUnmountFromSysuserJob;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model implements ProvisionableResource
{
    use Provisionable, SoftDeletes;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'certificate' => false,
        'php_version' => 74,
        'status' => 'pending'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sysuser_id'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'name' => ['required', 'alpha_dash', 'min:3', 'max:32', 'unique:sites,name,{{resourceId}}'],
        'username' => ['required', 'min:3', 'unique:sysusers,name'],
        'php_version' => ['in:73,74']
    ];

    /**
     * Get the server that owns the site.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->sysuser->server();
    }

    /**
     * Get the sysuser that owns the site.
     * 
     * @return BelongsTo
     */
    public function sysuser()
    {
        return $this->belongsTo(Sysuser::class, 'sysuser_id');
    }

    /**
     * Get the primary domain of the site.
     * 
     * @return string
     */
    public function getDomainAttribute()
    {
        return $this->name . '.local';
    }

    /**
     * Get the domains for the site.
     * 
     * @return HasMany
     */
    public function domains()
    {
        return $this->hasMany(Domain::class, 'site_id');
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
     * Get the databases for the site.
     * 
     * @return HasMany
     */
    public function databases()
    {
        return $this->hasMany(Database::class, 'site_id');
    }

    /**
     * Get the backup path for the resource.
     * 
     * 
     * @return string
     */
    public function getBackupPath()
    {
        return '/opt/sitepilot/users/' . $this->sysuser->name . '/sites/' . $this->name;
    }

    /**
     * Get the WordPress installs for the site.
     * 
     * @return HasOne
     */
    public function wpInstalls()
    {
        return $this->hasMany(WpInstall::class, 'site_id');
    }

    /**
     * Dispatch site provision job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(SiteProvisionJob::class);
    }

    /**
     * Dispatch mount site to sysuser job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function mountToSysuser($user)
    {
        return $this->dispatchJob(SiteMountToSysuserJob::class, [], [
            'mount_user' => (string) $user,
            'mount_site' => (string) $this->name,
            'source_user' => (string) $this->sysuser->name,
            'source_site' => (string) $this->name
        ]);
    }

    /**
     * Dispatch unmount site from sysuser job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function unmountFromSysuser($user)
    {
        return $this->dispatchJob(SiteUnmountFromSysuserJob::class, [], [
            'mount_user' => (string) $user,
            'mount_site' => (string) $this->name
        ]);
    }

    /**
     * Dispatch site destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(SiteDestroyJob::class);
    }
}
