<?php

namespace App\Jobs;

use Exception;
use App\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Playbooks\SiteProvisionPlaybook;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SiteProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The site instance.
     *
     * @var Site
     */
    public $site;

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
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->site->server->isReady() && $this->site->sysuser->isReady() && !$this->site->isBusy()) {
            $this->site->markAsProvisioning();

            $task = $this->site->run(
                new SiteProvisionPlaybook($this->site)
            );

            if ($task->successful()) {
                $this->site->markAsReady();
                return $this->delete();
            }

            $this->site->markAsError();
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
        $this->site->markAsError();
    }
}
