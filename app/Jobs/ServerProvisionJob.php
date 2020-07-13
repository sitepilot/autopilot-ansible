<?php

namespace App\Jobs;

use Exception;
use App\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Playbooks\ServerProvisionPlaybook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ServerProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The ansible tags.
     *
     * @var array
     */
    public $tags;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 20;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1260;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Server $server, $tags = [])
    {
        $this->server = $server;
        $this->tags = $tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->server->provider_server_id) {
            // Create server at server provider and wait
            $this->server->provider_server_id = $this->server->withProvider()->createServer();
            $this->server->save();
        }

        if ($this->server->isReadyForProvisioning() && !$this->server->isBusy(['connecting'])) {
            $this->server->markAsProvisioning();

            $task = $this->server->run(
                new ServerProvisionPlaybook($this->server, $this->tags)
            );

            if ($task->successful()) {
                $this->server->markAsReady();
                return $this->delete();
            }

            $this->server->markAsError();
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
