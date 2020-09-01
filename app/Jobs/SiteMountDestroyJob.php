<?php

namespace App\Jobs;

use Exception;
use App\SiteMount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Playbooks\SiteMountDestroyPlaybook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SiteMountDestroyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The site mount instance.
     *
     * @var SiteMount
     */
    public $siteMount;

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
    public function __construct(SiteMount $siteMount)
    {
        $this->siteMount = $siteMount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->siteMount->isDestroying()) {
            return $this->delete();
        }

        $this->siteMount->markAsDestroying();

        $task = $this->siteMount->run(
            new SiteMountDestroyPlaybook($this->siteMount)
        );

        if ($task->successful()) {
            $this->siteMount->markAsDestroyed();
            $this->siteMount->forceDelete();
            return $this->delete();
        }

        $this->siteMount->markAsError();
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed($exception)
    {
        $this->siteMount->markAsError();
        $this->siteMount->restore();
    }
}
