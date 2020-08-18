<?php

namespace App\Jobs;

use Exception;
use App\Domain;
use App\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Playbooks\DomainDestroyPlaybook;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DomainDestroyJob implements ShouldQueue
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
            $this->domain->markAsDestroying();

            $task = $this->domain->run(
                new DomainDestroyPlaybook($this->domain),
                [],
                Server::where('type', 'loadbalancer')->get()
            );

            if ($task->successful()) {
                $this->domain->markAsDestroyed();
                $this->domain->forceDelete();
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
