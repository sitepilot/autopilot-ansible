<?php

namespace App;

use App\Services\Custom;
use App\Services\UpCloud;
use InvalidArgumentException;
use App\Contracts\ServerProviderClient;

class ServerProviderClientFactory
{
    /**
     * Create a server provider client instance for the given server.
     *
     * @param  Server  $server
     * @return ServerProviderClient
     */
    public static function make(Server $server)
    {
        switch ($server->provider) {
            case 'upcloud':
                return new UpCloud($server);
            case 'custom':
                return new Custom($server);
            default:
                throw new InvalidArgumentException("Invalid provider type.");
        }
    }
}
