<?php

namespace App\Traits;

use App\Task;
use App\Nova\Resource;
use App\Playbooks\Playbook;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Status;

trait Provisionable
{
    use DeterminesAge;

    /**
     * Dispatch a job.
     *
     * @param string $jobClass
     * @return void
     */
    public function dispatchJob($jobClass)
    {
        return $jobClass::dispatch($this->withTrashed()->find($this->id));
    }

    /**
     * Get the tasks for the resource.
     * 
     * @return MorphMany
     */
    public function tasks()
    {
        return $this->morphMany(Task::class, 'provisionable');
    }

    /**
     * Determine if the resource is currently connecting.
     *
     * @return bool
     */
    public function isConnecting()
    {
        return $this->status == 'connecting';
    }

    /**
     * Mark the resource as connecting.
     *
     * @return $this
     */
    public function markAsConnecting()
    {
        $this->status = 'connecting';
        return $this->save();
    }

    /**
     * Determine if the resource is currently provisioning.
     *
     * @return bool
     */
    public function isProvisioning()
    {
        return $this->status == 'provisioning';
    }

    /**
     * Mark the resource as provisioning.
     *
     * @return $this
     */
    public function markAsProvisioning()
    {
        $this->status = 'provisioning';
        return $this->save();
    }

    /**
     * Determine if the resource is currently destroying.
     *
     * @return bool
     */
    public function isDestroying()
    {
        return $this->status == 'destroying';
    }

    /**
     * Mark the resource as destroying.
     *
     * @return $this
     */
    public function markAsDestroying()
    {
        $this->status = 'destroying';
        return $this->save();
    }

    /**
     * Determine if the resource is currently destroyed.
     *
     * @return bool
     */
    public function isDestroyed()
    {
        return $this->status == 'destroyed';
    }

    /**
     * Mark the resource as destroyed.
     *
     * @return $this
     */
    public function markAsDestroyed()
    {
        $this->status = 'destroyed';
        return $this->save();
    }

    /**
     * Determine if the resource is currently ready.
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->status == 'ready';
    }

    /**
     * Mark the resource as ready.
     *
     * @return $this
     */
    public function markAsReady()
    {
        $this->status = 'ready';
        return $this->save();
    }

    /**
     * Determine if the resource is currently in error state.
     *
     * @return bool
     */
    public function isError()
    {
        return $this->status == 'error';
    }

    /**
     * Mark the resource as error.
     *
     * @return $this
     */
    public function markAsError()
    {
        $this->status = 'error';
        return $this->save();
    }

    /**
     * Determine if the resource is currently in stopping state.
     *
     * @return bool
     */
    public function isStopping()
    {
        return $this->status == 'stopping';
    }

    /**
     * Mark the resource as stopping.
     *
     * @return $this
     */
    public function markAsStopping()
    {
        $this->status = 'stopping';
        return $this->save();
    }

    /**
     * Determine if the resource is currently in stopped state.
     *
     * @return bool
     */
    public function isStopped()
    {
        return $this->status == 'stopped';
    }

    /**
     * Mark the resource as stopped.
     *
     * @return $this
     */
    public function markAsStopped()
    {
        $this->status = 'stopped';
        return $this->save();
    }

    /**
     * Determine if the resource is currently in starting state.
     *
     * @return bool
     */
    public function isStarting()
    {
        return $this->status == 'starting';
    }

    /**
     * Mark the resource as starting.
     *
     * @return $this
     */
    public function markAsStarting()
    {
        $this->status = 'starting';
        return $this->save();
    }

    /**
     * Determine if the resource is currently in testing state.
     *
     * @return bool
     */
    public function isTesting()
    {
        return $this->status == 'testing';
    }

    /**
     * Mark the resource as testing.
     *
     * @return $this
     */
    public function markAsTesting()
    {
        $this->status = 'testing';
        return $this->save();
    }

    /**
     * Determine if the resource is currently renewing certificates.
     *
     * @return bool
     */
    public function isCertRenew()
    {
        return $this->status == 'cert-renew';
    }

    /**
     * Mark the resource as cert renew.
     *
     * @return $this
     */
    public function markAsCertRenew()
    {
        $this->status = 'cert-renew';
        return $this->save();
    }

    /**
     * Determine if the resource is currently requesting a certificate.
     *
     * @return bool
     */
    public function isCertRequest()
    {
        return $this->status == 'cert-request';
    }

    /**
     * Mark the resource as cert request.
     *
     * @return $this
     */
    public function markAsCertRequest()
    {
        $this->status = 'cert-request';
        return $this->save();
    }

    /**
     * Determine if the resource is currently running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->status == 'running';
    }

    /**
     * Mark the resource as running.
     *
     * @return $this
     */
    public function markAsRunning()
    {
        $this->status = 'running';
        return $this->save();
    }

    /**
     * Determine if the resource is currently restoring.
     *
     * @return bool
     */
    public function isRestoring()
    {
        return $this->status == 'restoring';
    }

    /**
     * Mark the resource as restoring.
     *
     * @return $this
     */
    public function markAsRestoring()
    {
        $this->status = 'restoring';
        return $this->save();
    }

    /**
     * Determine if the resource is busy processing other requests.
     *
     * @return bool
     */
    public function isBusy($exclude = [])
    {;
        return (!in_array('connecting', $exclude) && $this->isConnecting())
            || (!in_array('provisioning', $exclude) && $this->isProvisioning())
            || (!in_array('destroying', $exclude) && $this->isDestroying())
            || (!in_array('stopping', $exclude) && $this->isStopping())
            || (!in_array('stopped', $exclude) && $this->isStopped())
            || (!in_array('starting', $exclude) && $this->isStarting())
            || (!in_array('testing', $exclude) && $this->isTesting())
            || (!in_array('cert-renew', $exclude) && $this->isCertRenew())
            || (!in_array('cert-request', $exclude) && $this->isCertRequest())
            || (!in_array('running', $exclude) && $this->isRunning())
            || (!in_array('restoring', $exclude) && $this->isRestoring());
    }

    /**
     * Returns a Nova status field for provisionables.
     *
     * @param Resource $resource
     * @return static
     */
    public static function getNovaStatusField(Resource $resource)
    {
        return Status::make(
            'Status',
            function () use ($resource) {
                return Str::studly($resource->status);
            }
        )
            ->loadingWhen(['Connecting', 'Provisioning', 'Destroying', 'Stopping', 'Starting', 'Testing', 'CertRenew', 'CertRequest', 'Running', 'Restoring'])
            ->failedWhen(['Error']);
    }

    /**
     * Run the given playbook on the server.
     *
     * @param  Playbook  $playbook
     * @param  array     $options
     * @return Task
     */
    public function run(Playbook $playbook, array $options = [])
    {
        if (!array_key_exists('timeout', $options)) {
            $options['timeout'] = $playbook->timeout();
        }

        if (!$playbook->allowedToRun()) {
            return $this->tasks()->create([
                'name' => $playbook->name(),
                'user' => $playbook->sshAs,
                'options' => $options,
                'playbook' => (string) $playbook,
                'vars' => $playbook->vars(),
                'output' => 'The dispatched task is not allowed to run on the server.',
                'status' => 'forbidden',
                'exit_code' => 403
            ]);
        }

        return $this->tasks()->create([
            'name' => $playbook->name(),
            'user' => $playbook->sshAs,
            'options' => $options,
            'playbook' => (string) $playbook,
            'vars' => $playbook->vars(),
            'output' => '',
        ])->run();
    }
}
