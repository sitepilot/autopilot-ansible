<?php

namespace App\Jobs;

use App\Key;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Playbooks\KeyProvisionPlaybook;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class KeyProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The key instance.
     *
     * @var Key
     */
    public $key;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    /**
     * Create a new job instance.
     *
     * @param Key $key
     * @return void
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->key->isProvisioning()) {
            return $this->delete();
        }

        if ($this->key->server->isReady() && !$this->key->isBusy()) {
            $this->key->markAsProvisioning();

            $task = $this->key->run(
                new KeyProvisionPlaybook($this->key)
            );

            if ($task->successful()) {
                $this->key->markAsReady();
                return $this->delete();
            }

            $this->key->markAsError();
        }

        $this->release(30);
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed($exception)
    {
        $this->key->markAsError();
    }
}
