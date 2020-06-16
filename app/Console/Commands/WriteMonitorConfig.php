<?php

namespace App\Console\Commands;

use stdClass;
use App\Domain;
use App\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WriteMonitorConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump monitor config to json files for monitoring.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $servers = Server::where('status', 'ready')->get();

        $content[] = new stdClass;
        $content[0]->targets = [];

        foreach ($servers as $server) {
            $content[0]->targets[] = $server->address . ':9100';
        }

        Storage::disk('local')->put('monitor/servers.json', json_encode($content));

        $domains = Domain::where('status', 'ready')->get();

        $content[] = new stdClass;
        $content[0]->targets = [];

        foreach ($domains as $domain) {
            $content[0]->targets[] = 'https://' . $domain->name;
        }

        Storage::disk('local')->put('monitor/domains.json', json_encode($content));
    }
}
