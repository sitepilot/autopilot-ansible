<?php

namespace App\Jobs;

use Exception;
use App\SiteMount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Playbooks\SiteMountProvisionPlaybook;

class SiteMountProvisionJob implements ShouldQueue
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
        if ($this->siteMount->sysuser->isReady() && $this->siteMount->site->isReady()) {
            $this->siteMount->markAsProvisioning();

            if ($this->siteMount->isRemote()) {
                $key = $this->siteMount->site->sysuser->keys()->where('key', $this->siteMount->sysuser->public_key)->first();
                if (!$key) {
                    $this->siteMount->site->sysuser->keys()->create([
                        'name' => $this->siteMount->sysuser->name . '@' . $this->siteMount->sysuser->server->name,
                        'key' => $this->siteMount->sysuser->public_key
                    ]);

                    return $this->release(30);
                } elseif (!$key->isReady()) {
                    return $this->release(30);
                }
            }

            $task = $this->siteMount->run(
                new SiteMountProvisionPlaybook($this->siteMount)
            );

            if ($task->successful()) {
                $this->siteMount->markAsReady();
                return $this->delete();
            }

            $this->siteMount->markAsError();
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
        $this->siteMount->markAsError();
    }
}
