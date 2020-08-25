<?php

namespace App;

use App\Traits\Provisionable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use App\Jobs\WpInstallConnectJob;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WpInstall extends Model implements ProvisionableResource
{
    use Provisionable;

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
        'name', 'path'
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function (WpInstall $wpInstall) {
            if ($wpInstall->site) $wpInstall->server_id = $wpInstall->site->server->id;
        });
    }

    /**
     * Get the server that owns the WordPress install.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Get the site that owns the WordPress install.
     * 
     * @return BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get the WordPress install path.
     * 
     * @return BelongsTo
     */
    public function getPath()
    {
        if ($this->site) {
            return '/opt/sitepilot/users/' . $this->site->sysuser->name . '/sites/' . $this->site->name . '/public';
        }

        return $this->path;
    }

    /**
     * Dispatch connect job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function connect()
    {
        return $this->dispatchJob(WpInstallConnectJob::class);
    }
}
