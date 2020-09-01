<?php

namespace App;

use App\Rules\SiteMountRule;
use App\Traits\Provisionable;
use App\Jobs\SiteMountDestroyJob;
use App\Jobs\SiteMountProvisionJob;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteMount extends Model implements ProvisionableResource
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
        'sysuser_id', 'site_id'
    ];

    public static function getValidationRules($key)
    {
        $validationRules = [
            'sysuser_id' => ['required', 'exists:sysusers,id'],
            'site_id' => ['required', 'exists:sites,id', new SiteMountRule]
        ];

        return $validationRules[$key];
    }

    /**
     * Get the sysuser that owns this mount.
     * 
     * @return BelongsTo
     */
    public function sysuser()
    {
        return $this->belongsTo(Sysuser::class, 'sysuser_id');
    }

    /**
     * Get the site that belongs to this mount.
     * 
     * @return BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get the server the site mount belongs to.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->sysuser->server();
    }

    /**
     * Dispatch site mount provision job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(SiteMountProvisionJob::class);
    }

    /**
     * Dispatch site mount destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(SiteMountDestroyJob::class);
    }
}
