<?php

namespace App\Jobs;

use Exception;
use App\Site;
use Illuminate\Bus\Queueable;
use App\Playbooks\SiteDestroyPlaybook;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SiteDestroyJob implements ShouldQueue
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
        if ($this->site->isDestroying()) {
            return $this->delete();
        }

        if ($this->site->server->isReady() && !$this->site->isBusy()) {
            $this->site->markAsDestroying();

            $task = $this->site->run(
                new SiteDestroyPlaybook($this->site)
            );

            if ($task->successful()) {
                $this->site->markAsDestroyed();
                $this->site->forceDelete();
                return $this->delete();
            }

            $this->site->markAsError();
            $this->site->restore();
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
        $this->site->restore();
    }
}
