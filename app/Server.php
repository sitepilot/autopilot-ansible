<?php

namespace App;

use App\SecureShellKey;
use App\Jobs\ServerStopJob;
use App\Jobs\ServerTestJob;
use App\Jobs\ServerStartJob;
use App\Traits\Provisionable;
use App\Jobs\ServerDestroyJob;
use App\Jobs\ServerProvisionJob;
use App\Rules\CommaSeparatedIpsRule;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ProvisionableResource;
use App\Playbooks\ServerConnectPlaybook;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Server extends Model implements ProvisionableResource
{
    use Provisionable, SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'shared',
        'port' => 22,
        'user' => 'root',
        'status' => 'pending'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'provider', 'region', 'size', 'address', 'ipv6_address', 'private_address', 'type', 'port', 'user', 'backup_s3_key', 'backup_s3_secret', 'backup_s3_bucket'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'private_key', 'admin_password', 'mysql_password', 'backup_s3_secret', 'backup_password'
    ];

    /**
     * The model's default validation rules.
     *
     * @var array
     */
    public static $validationRules = [
        'name' => ['required', 'alpha_dash', 'min:3', 'max:32', 'unique:servers,name,{{resourceId}}'],
        'provider' => ['required', 'in:upcloud,custom'],
        'region' => ['required_unless:provider,custom'],
        'size' => ['required_unless:provider,custom'],
        'address' => ['required_if:provider,custom'],
        'ipv6_address' => ['nullable', 'ipv6'],
        'private_address' => ['nullable', 'ipv4'],
        'type' => ['required_if:provider,custom', 'in:shared,dedicated,loadbalancer'],
        'port' => ['required', 'numeric'],
        'user' => ['required', 'min:3'],
        'admin_password' => ['nullable', 'min:8'],
        'mysql_password' => ['nullable', 'min:8'],
        'timezone' => ['nullable', 'min:3'],
        'admin_email' => ['nullable', 'email'],
        'health_email' => ['nullable', 'email'],
        'php_post_max_size' => ['nullable', 'numeric', 'min:25', 'max:2048'],
        'php_upload_max_filesize' => ['nullable', 'numeric', 'min:25', 'max:1024'],
        'php_memory_limit' => ['nullable', 'numeric', 'min:64', 'max:2048'],
        'smtp_relay_host' => ['nullable', 'min:3'],
        'smtp_relay_domain' => ['nullable', 'min:3'],
        'smtp_relay_user' => ['nullable', 'min:3'],
        'smtp_relay_password' => ['nullable', 'min:6'],
        'backup_s3_key' => ['nullable', 'min:3', 'max:255'],
        'backup_s3_secret' => ['nullable', 'min:3', 'max:255'],
        'backup_s3_bucket' => ['nullable', 'min:3', 'max:255'],
        'backup_password' => ['nullable', 'min:6', 'max:255'],
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Extend validation rules
        self::$validationRules['authorized_addresses'] = new CommaSeparatedIpsRule;
    }

    /**
     * Get the sysusers for the server.
     * 
     * @return HasMany
     */
    public function sysusers()
    {
        return $this->hasMany(Sysuser::class, 'server_id');
    }

    /**
     * Get the sites for the server.
     * 
     * @return HasManyThrough
     */
    public function sites()
    {
        return $this->hasManyThrough(Site::class, Sysuser::class);
    }

    /**
     * Get the databases for the server.
     * 
     * @return HasManyThrough
     */
    public function databases()
    {
        return $this->hasManyThrough(Database::class, Sysuser::class);
    }

    /**
     * Get the keys for the server.
     * 
     * @return HasMany
     */
    public function keys()
    {
        return $this->hasMany(Key::class, 'server_id')->where('sysuser_id', null);
    }

    /**
     * Get the tasks for the server.
     * 
     * @return MorphMany
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    /**
     * Get the provider gateway for the server.
     *
     * @return mixed
     */
    public function withProvider()
    {
        return ServerProviderClientFactory::make($this);
    }

    /**
     * Determine if the server is a shared hosting server.
     * 
     * @return boolean
     */
    public function isShared()
    {
        return $this->type == 'shared';
    }

    /**
     * Determine if the server is a dedicated hosting server.
     * 
     * @return boolean
     */
    public function isDedicated()
    {
        return $this->type == 'dedicated';
    }

    /**
     * Determine if the server is a loadbalancer.
     * 
     * @return boolean
     */
    public function isLoadbalancer()
    {
        return $this->type == 'loadbalancer';
    }

    /**
     * Determine if the server is ready for provisioning.
     *
     * @return bool
     */
    public function isReadyForProvisioning()
    {
        if (!$this->provider_server_id) {
            return false;
        }

        if (!$this->address) {
            $this->retrieveIpAddresses();
        }

        if ($this->fresh()->address) {
            $this->markAsConnecting();
            $connect = $this->run(new ServerConnectPlaybook($this));
            return $connect->successful();
        }

        return false;
    }

    /**
     * Attempt to retrieve and store the server's IP addresses.
     *
     * @return void
     */
    protected function retrieveIpAddresses()
    {
        list($address, $ipv6Address, $privateAddress) = [
            $this->withProvider()->getAddress(),
            $this->withProvider()->getIpv6Address(),
            $this->withProvider()->getPrivateIpAddress(),
        ];

        if (!$address) {
            return;
        }

        $this->address = $address;
        $this->ipv6_address = $ipv6Address;
        $this->private_address = $privateAddress;

        $this->save();
    }

    /**
     * Get the path to the server's worker SSH key.
     *
     * @return string
     */
    public function keyPath()
    {
        return SecureShellKey::storeFor($this);
    }

    /**
     * Check if server is configurated for backups.
     *
     * @return boolean
     */
    public function backupConfigured()
    {
        return !empty($this->backup_s3_key)
            && !empty($this->backup_s3_secret)
            && !empty($this->backup_s3_bucket)
            && !empty($this->backup_password);
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
        if ($value != $this->private_key) {
            $this->attributes['private_key'] = encrypt($value);
        }
    }

    /**
     * Get admin password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getAdminPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set admin password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setAdminPasswordAttribute($value)
    {
        if ($value != $this->admin_password) {
            $this->attributes['admin_password'] = encrypt($value);
        }
    }

    /**
     * Get database password attribute.
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
     * Set database password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setMysqlPasswordAttribute($value)
    {
        if ($value != $this->mysql_password) {
            $this->attributes['mysql_password'] = encrypt($value);
        }
    }

    /**
     * Get SMTP releay password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getSmtpRelayPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set SMTP relay password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setSmtpRelayPasswordAttribute($value)
    {
        if ($value != $this->smtp_relay_password) {
            $this->attributes['smtp_relay_password'] = encrypt($value);
        }
    }

    /**
     * Get backup S3 secret attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getBackupS3SecretAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set backup S3 secret attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setBackupS3SecretAttribute($value)
    {
        if ($value != $this->backup_s3_secret) {
            $this->attributes['backup_s3_secret'] = encrypt($value);
        }
    }

    /**
     * Get backup password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getBackupPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Set backup password attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setBackupPasswordAttribute($value)
    {
        if ($value != $this->backup_password) {
            $this->attributes['backup_password'] = encrypt($value);
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
     * Set authorized addresses attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setAuthorizedAddressesAttribute($value)
    {
        $this->attributes['authorized_addresses'] = implode(',', array_map('trim', explode(',', $value)));
    }

    /**
     * Get authorized addresses attribute.
     *
     * @param  string  $value
     * @return array
     */
    public function getAuthorizedAddressesAttribute($value)
    {
        return array_filter(array_map('trim', explode(',', $value)));
    }

    /**
     * Create server and dispatch the job to provision the server.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function provision($tags = [])
    {
        return $this->dispatchJob(ServerProvisionJob::class, $tags);
    }

    /**
     * Dispatch server stop job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function stop()
    {
        return $this->dispatchJob(ServerStopJob::class);
    }

    /**
     * Dispatch server start job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function start()
    {
        return $this->dispatchJob(ServerStartJob::class);
    }

    /**
     * Dispatch server test job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function test()
    {
        return $this->dispatchJob(ServerTestJob::class);
    }

    /**
     * Generate keypair.
     *
     * @return bool
     */
    public function generateKeypair()
    {
        return $this->keypair = SecureShellKey::forServer();
    }

    /**
     * Dispatch server destroy job.
     *
     * @return bool|PendingDispatch|mixed
     */
    public function deleteFromProvider()
    {
        return $this->dispatchJob(ServerDestroyJob::class);
    }

    /**
     * Get a list of supported timezones.
     *
     * @return array
     */
    public static function getTimezones()
    {
        return [
            'Pacific/Midway'       => "(GMT-11:00) Midway Island",
            'US/Samoa'             => "(GMT-11:00) Samoa",
            'US/Hawaii'            => "(GMT-10:00) Hawaii",
            'US/Alaska'            => "(GMT-09:00) Alaska",
            'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
            'America/Tijuana'      => "(GMT-08:00) Tijuana",
            'US/Arizona'           => "(GMT-07:00) Arizona",
            'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
            'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
            'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
            'America/Mexico_City'  => "(GMT-06:00) Mexico City",
            'America/Monterrey'    => "(GMT-06:00) Monterrey",
            'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
            'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
            'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
            'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
            'America/Bogota'       => "(GMT-05:00) Bogota",
            'America/Lima'         => "(GMT-05:00) Lima",
            'America/Caracas'      => "(GMT-04:30) Caracas",
            'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
            'America/La_Paz'       => "(GMT-04:00) La Paz",
            'America/Santiago'     => "(GMT-04:00) Santiago",
            'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
            'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
            'Greenland'            => "(GMT-03:00) Greenland",
            'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
            'Atlantic/Azores'      => "(GMT-01:00) Azores",
            'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
            'Africa/Casablanca'    => "(GMT) Casablanca",
            'Europe/Dublin'        => "(GMT) Dublin",
            'Europe/Lisbon'        => "(GMT) Lisbon",
            'Europe/London'        => "(GMT) London",
            'Africa/Monrovia'      => "(GMT) Monrovia",
            'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
            'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
            'Europe/Berlin'        => "(GMT+01:00) Berlin",
            'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
            'Europe/Brussels'      => "(GMT+01:00) Brussels",
            'Europe/Budapest'      => "(GMT+01:00) Budapest",
            'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
            'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
            'Europe/Madrid'        => "(GMT+01:00) Madrid",
            'Europe/Paris'         => "(GMT+01:00) Paris",
            'Europe/Prague'        => "(GMT+01:00) Prague",
            'Europe/Rome'          => "(GMT+01:00) Rome",
            'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
            'Europe/Skopje'        => "(GMT+01:00) Skopje",
            'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
            'Europe/Vienna'        => "(GMT+01:00) Vienna",
            'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
            'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
            'Europe/Athens'        => "(GMT+02:00) Athens",
            'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
            'Africa/Cairo'         => "(GMT+02:00) Cairo",
            'Africa/Harare'        => "(GMT+02:00) Harare",
            'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
            'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
            'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
            'Europe/Kiev'          => "(GMT+02:00) Kyiv",
            'Europe/Minsk'         => "(GMT+02:00) Minsk",
            'Europe/Riga'          => "(GMT+02:00) Riga",
            'Europe/Sofia'         => "(GMT+02:00) Sofia",
            'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
            'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
            'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
            'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
            'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
            'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
            'Europe/Moscow'        => "(GMT+03:00) Moscow",
            'Asia/Tehran'          => "(GMT+03:30) Tehran",
            'Asia/Baku'            => "(GMT+04:00) Baku",
            'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
            'Asia/Muscat'          => "(GMT+04:00) Muscat",
            'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
            'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
            'Asia/Kabul'           => "(GMT+04:30) Kabul",
            'Asia/Karachi'         => "(GMT+05:00) Karachi",
            'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
            'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
            'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
            'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
            'Asia/Almaty'          => "(GMT+06:00) Almaty",
            'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
            'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
            'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
            'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
            'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
            'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
            'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
            'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
            'Australia/Perth'      => "(GMT+08:00) Perth",
            'Asia/Singapore'       => "(GMT+08:00) Singapore",
            'Asia/Taipei'          => "(GMT+08:00) Taipei",
            'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
            'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
            'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
            'Asia/Seoul'           => "(GMT+09:00) Seoul",
            'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
            'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
            'Australia/Darwin'     => "(GMT+09:30) Darwin",
            'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
            'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
            'Australia/Canberra'   => "(GMT+10:00) Canberra",
            'Pacific/Guam'         => "(GMT+10:00) Guam",
            'Australia/Hobart'     => "(GMT+10:00) Hobart",
            'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
            'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
            'Australia/Sydney'     => "(GMT+10:00) Sydney",
            'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
            'Asia/Magadan'         => "(GMT+12:00) Magadan",
            'Pacific/Auckland'     => "(GMT+12:00) Auckland",
            'Pacific/Fiji'         => "(GMT+12:00) Fiji",
        ];
    }
}
