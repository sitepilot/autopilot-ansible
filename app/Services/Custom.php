<?php

namespace App\Services;

use App\Server;
use Illuminate\Support\Str;
use App\Contracts\ServerProviderClient;

class Custom implements ServerProviderClient
{
    /**
     * Holds the server instance.
     *
     * @var Server
     */
    private $server;

    /**
     * Create a new provider API instance.
     *
     * @return void
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Determine if the provider credentials are valid.
     *
     * @return string
     */
    public function valid()
    {
        return true;
    }

    /**
     * Get all of the valid regions for the provider.
     *
     * @return array
     */
    public function regions()
    {
        return [];
    }

    /**
     * Get all of the valid server sizes for the provider.
     *
     * @return array
     */
    public function sizes()
    {
        return [];
    }

    /**
     * Create a new server.
     *
     * @return string $id
     */
    public function createServer()
    {
        return 'custom-' . Str::random(6);
    }

    /**
     * Get the public Ipv4 address for a server by ID.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->server->address;
    }

    /**
     * Get the public Ipv6 address for a server by ID.
     *
     * @return string|null
     */
    public function getIpv6Address()
    {
        return $this->server->ipv6_address;
    }

    /**
     * Get the private IP address for a server by ID.
     *
     * @return string|null
     */
    public function getPrivateIpAddress()
    {
        return $this->server->private_address;
    }

    /**
     * Stop the given server.
     *
     * @return void
     */
    public function stopServer()
    {
        //
    }

    /**
     * Check if the server is stopped.
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->server->isStopping();
    }

    /**
     * Start the given server.
     *
     * @return void
     */
    public function startServer()
    {
        //
    }

    /**
     * Check if the server is started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->server->isStarting();
    }

    /**
     * Delete the given server.
     *
     * @return void
     */
    public function deleteServer()
    {
        //
    }
}
