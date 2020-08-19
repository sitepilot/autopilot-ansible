<?php

namespace App\Jobs;

use Exception;
use App\Domain;
use App\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Playbooks\DomainProvisionPlaybook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DomainProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The domain instance.
     *
     * @var Domain
     */
    public $domain;

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
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->domain->isBusy()) {
            $this->domain->markAsProvisioning();

            $task = $this->domain->run(
                new DomainProvisionPlaybook($this->domain),
                [],
                Server::where('id', $this->domain->site->server->id)->get()
            );

            if ($task->successful()) {
                $this->domain->markAsReady();
                return $this->delete();
            }

            $this->domain->markAsError();
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
        $this->domain->markAsError();
    }
}
