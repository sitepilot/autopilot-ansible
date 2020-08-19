<?php

namespace App\Jobs;

use App\Key;
use Exception;
use Illuminate\Bus\Queueable;
use App\Playbooks\KeyDestroyPlaybook;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class KeyDestroyJob implements ShouldQueue
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
        if ($this->key->isDestroying()) {
            return $this->delete();
        }

        if ($this->key->server->isReady() && !$this->key->isBusy()) {
            $this->key->markAsDestroying();

            $task = $this->key->run(
                new KeyDestroyPlaybook($this->key)
            );

            if ($task->successful()) {
                $this->key->markAsDestroyed();
                $this->key->forceDelete();
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
