<?php

namespace App\Jobs;

use Exception;
use App\WpInstall;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Playbooks\WpInstallConnectPlaybook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WpInstallConnectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The WordPress install instance.
     *
     * @var WpInstall
     */
    public $wpInstall;

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
    public function __construct(WpInstall $wpInstall)
    {
        $this->wpInstall = $wpInstall;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->wpInstall->isConnecting()) {
            return $this->delete();
        }

        if (!$this->wpInstall->isBusy()) {
            $this->wpInstall->markAsConnecting();

            $task = $this->wpInstall->run(
                new WpInstallConnectPlaybook($this->wpInstall)
            );

            if ($task->successful()) {
                $this->wpInstall->markAsReady();
                return $this->delete();
            }

            $this->wpInstall->markAsError();
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
        $this->server->markAsError();
    }
}
