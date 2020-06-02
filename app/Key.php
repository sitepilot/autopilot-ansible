<?php

namespace App;

use App\Jobs\KeyDestroyJob;
use App\Jobs\KeyProvisionJob;
use App\Traits\Provisionable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class Key extends Model implements ProvisionableResource
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
        'name', 'key'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'name' => ['required', 'email'],
        'key' => ['required', 'min:32']
    ];

    /**
     * Get the server that the key belongs to.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Get the sysuser that the key belongs to.
     * 
     * @return BelongsTo
     */
    public function sysuser()
    {
        return $this->belongsTo(Sysuser::class, 'sysuser_id');
    }

    /**
     * Dispatch key provision job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(KeyProvisionJob::class);
    }

    /**
     * Dispatch key destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(KeyDestroyJob::class);
    }
}
