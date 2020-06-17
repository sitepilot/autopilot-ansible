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
        $servers = Server::get();
        $content = [];

        foreach ($servers as $server) {
            $item = new stdClass;
            $item->labels = new stdClass;
            $item->targets[] = $server->address . ':9100';
            $item->labels->name = $server->name;
            $content[] = $item;
        }

        Storage::disk('local')->put('monitor/servers.json', json_encode($content));

        $domains = Domain::get();
        $content = [];

        foreach ($domains as $domain) {
            $item = new stdClass;
            $item->labels = new stdClass;
            $item->targets[] = 'https://' . $domain->name;
            $item->labels->name = $domain->name;
            $content[] = $item;
        }

        Storage::disk('local')->put('monitor/domains.json', json_encode($content));
    }
}
