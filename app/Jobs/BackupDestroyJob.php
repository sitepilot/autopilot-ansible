<?php

namespace App\Jobs;

use Exception;
use App\Backup;
use App\Playbooks\BackupDestroyPlaybook;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BackupDestroyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The backup instance.
     *
     * @var Backup
     */
    public $backup;

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
     * @return void
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->backup->isDestroying()) {
            return $this->delete();
        }

        if ($this->backup->server->isReady() && !$this->backup->isBusy()) {
            $this->backup->markAsDestroying();

            $task = $this->backup->run(
                new BackupDestroyPlaybook($this->backup)
            );

            if ($task->successful()) {
                $this->backup->markAsDestroyed();
                $this->backup->forceDelete();
                return $this->delete();
            }

            $this->backup->markAsError();
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
        $this->backup->markAsError();
        $this->backup->restore();
    }
}
