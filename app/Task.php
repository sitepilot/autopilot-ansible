<?php

namespace App;

use App\Server;
use App\Traits\Prunable;
use App\Traits\InteractsWithAnsible;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

class Task extends Model
{
    use InteractsWithAnsible, Prunable;

    /**
     * The default timeout for tasks.
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 3600;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'vars' => 'json',
        'tags' => 'json'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'options',
        'output',
        'playbook',
        'vars'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'vars' => '[]'
    ];

    /** 
     * Get the servers where the task needs to run on.
     * 
     * @return Server
     */
    public function servers()
    {
        return $this->belongsToMany(Server::class);
    }

    /**
     * Get the owning provisionable model.
     * 
     * @return MorphTo
     */
    public function provisionable()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Determine if the task was successful.
     *
     * @return bool
     */
    public function successful()
    {
        return (int) $this->exit_code === 0;
    }

    /**
     * Get the maximum execution time for the task.
     *
     * @return int
     */
    public function timeout()
    {
        return (int) ($this->options['timeout'] ?? self::DEFAULT_TIMEOUT);
    }

    /**
     * Get the path to the task's inventory file.
     *
     * @return string
     */
    public function inventoryPath()
    {
        return AnsibleInventory::storeForServers($this->servers);
    }

    /**
     * Get the value of the options array.
     *
     * @param  string  $value
     * @return array
     */
    public function getOptionsAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Set the value of the options array.
     *
     * @param  array  $value
     * @return array
     */
    public function setOptionsAttribute(array $value)
    {
        $this->attributes['options'] = serialize($value);
    }

    /**
     * Get private key attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getVarsAttribute($value)
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
    public function setVarsAttribute($value)
    {
        $this->attributes['vars'] = encrypt($value);
    }

    /**
     * Mark the task as finished and gather its output.
     *
     * @param  int  $exitCode
     * @return void
     */
    public function finish($exitCode = 0)
    {
        $this->markAsFinished($exitCode);

        foreach ($this->options['then'] ?? [] as $callback) {
            is_object($callback)
                ? $callback->handle($this)
                : app($callback)->handle($this);
        }
    }

    /**
     * Mark the task as running.
     *
     * @return $this
     */
    protected function markAsRunning()
    {
        return tap($this)->update([
            'status' => 'running',
        ]);
    }

    /**
     * Determine if the task is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->status === 'running';
    }

    /**
     * Mark the task as timed out.
     *
     * @param  string  $output
     * @return $this
     */
    protected function markAsTimedOut($output = '')
    {
        return tap($this)->update([
            'exit_code' => 1,
            'status' => 'timeout',
            'output' => $output,
        ]);
    }

    /**
     * Mark the task as finished.
     *
     * @param  int  $exitCode
     * @param  string  $output
     * @return $this
     */
    protected function markAsFinished($exitCode = 0, $output = '')
    {
        return tap($this)->update([
            'exit_code' => $exitCode,
            'status' => 'finished',
            'output' => $output,
        ]);
    }
}
