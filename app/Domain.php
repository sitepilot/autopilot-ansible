<?php

namespace App;

use App\Traits\Provisionable;
use App\Jobs\DomainDestroyJob;
use App\Jobs\DomainProvisionJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
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
        'name'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'name' => ['required', 'min:3', 'unique:domains,name,{{resourceId}}'],
        'monitor' => ['boolean']
    ];

    /**
     * Get the site that owns the domain.
     * 
     * @return BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Provision domain on server(s).
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(DomainProvisionJob::class);
    }

    /**
     * Destroy domain on on server(s).
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(DomainDestroyJob::class);
    }
}
