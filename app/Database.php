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
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function (Database $database) {
            // Generate unique database name based on server user
            $unique = true;
            while ($unique) {
                $database->name = $database->sysuser->name . '_db' . ucfirst(Str::random(4));
                $unique = $database->where('name', $database->name)->count();
            }

            // Generate unique database user based on server user
            $unique = true;
            while ($unique) {
                $database->user = $database->sysuser->name . '_u' . ucfirst(Str::random(4));
                $unique = $database->where('user', $database->user)->count();
            }

            $database->password = Str::random(12);
        });
    }

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
