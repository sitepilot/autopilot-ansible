<?php

namespace App\Jobs;

use Exception;
use App\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ServerStartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

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
     * @return string
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->server->isStarting()) {
            return $this->delete();
        }

        if (!$this->server->isBusy(['stopped'])) {
            $this->server->markAsStarting();

            if (!$this->server->withProvider()->isStarted()) {
                $this->server->withProvider()->startServer();
                while (!$this->server->withProvider()->isStarted()) {
                    sleep(60);
                }
            }

            $this->server->markAsReady();
            return $this->delete();
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
