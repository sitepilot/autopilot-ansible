<?php

namespace App;

use App\Jobs\UserTestJob;
use App\Jobs\UserDestroyJob;
use App\Traits\Provisionable;
use App\Jobs\UserProvisionJob;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sysuser extends Model implements ProvisionableResource
{
    use Provisionable, SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'isolated' => true,
        'status' => 'pending',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'config'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'mysql_password', 'private_key'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'name' => ['required', 'alpha_dash', 'min:3', 'max:32', 'not_in:root,admin,sitepilot,autopilot', 'unique:sysusers,name,{{resourceId}}'],
        'password' => ['nullable', 'min:8'],
        'mysql_password' => ['nullable', 'min:8'],
        'isolated' => ['boolean'],
        'full_name' => ['required', 'min:3'],
        'email' => ['required', 'email']
    ];

    /**
     * Returns an array with the default config.    
     *
     * @return void
     */
    public function getDefaultConfig()
    {
        return [
            'full_name' => [
                'value' => $this->name ?? '',
                'validation' => 'min:3'
            ],
            'email' => [
                'value' => '',
                'validation' => 'email'
            ]
        ];
    }

    /**
     * Get the server that owns the sysuser.
     * 
     * @return BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Get the sites for the sysuser.
     * 
     * @return HasMany
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'sysuser_id');
    }

    /**
     * Get the site mounts for the sysuser.
     * 
     * @return HasMany
     */
    public function siteMounts()
    {
        return $this->hasMany(SiteMount::class, 'sysuser_id');
    }

    /**
     * Get the databases for the sysuser.
     * 
     * @return HasMany
     */
    public function databases()
    {
        return $this->hasMany(Database::class, 'sysuser_id');
    }

    /**
     * Get the keys for the sysuser.
     * 
     * @return HasMany
     */
    public function keys()
    {
        return $this->hasMany(Key::class, 'sysuser_id');
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
     * Get mysql password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getMysqlPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set the SSH key attributes on the model.
     *
     * @param  object  $value
     * @return void
     */
    public function setKeypairAttribute($value)
    {
        $this->attributes = [
            'public_key' => trim($value->publicKey),
            'private_key' => encrypt(trim($value->privateKey)),
        ] + $this->attributes;
    }

    /**
     * Set mysql password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setMysqlPasswordAttribute($value)
    {
        $this->attributes['mysql_password'] = encrypt($value);
    }

    /**
     * Get private key attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getPrivateKeyAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set private key attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setPrivateKeyAttribute($value)
    {
        $this->attributes['private_key'] = encrypt($value);
    }

    /**
     * Dispatch user provision job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision()
    {
        return $this->dispatchJob(UserProvisionJob::class);
    }

    /**
     * Dispatch user test job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function test()
    {
        return $this->dispatchJob(UserTestJob::class);
    }

    /**
     * Generate keypair.
     *
     * @return bool
     */
    public function generateKeypair()
    {
        return $this->keypair = SecureShellKey::forSysuser($this);
    }

    /**
     * Delete user from server.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromServer()
    {
        return $this->dispatchJob(UserDestroyJob::class);
    }
}
