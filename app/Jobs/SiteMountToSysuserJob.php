<?php

namespace App\Jobs;

use App\Site;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Playbooks\SiteMountToSysuserPlaybook;

class SiteMountToSysuserJob implements ShouldQueue
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
     * The variables which will be passed to the playbook.
     *
     * @var array
     */
    public $vars;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Site $site, $tags = [], $vars)
    {
        $this->site = $site;
        $this->vars = $vars;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->site->server->isReady() && $this->site->isReady()) {
            $task = $this->site->run(
                new SiteMountToSysuserPlaybook($this->vars)
            );

            if ($task->successful()) {
                return $this->delete();
            }
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
        //
    }
}
