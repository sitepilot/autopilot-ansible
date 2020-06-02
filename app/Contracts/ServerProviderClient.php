<?php

namespace App\Contracts;

use App\Server;

interface ServerProviderClient
{
    /**
     * Create a new provider API instance.
     *
     * @return void
     */
    public function __construct(Server $server);
    
    /**
     * Determine if the provider credentials are valid.
     *
     * @return bool
     */
    public function valid();

    /**
     * Get all of the valid regions for the provider.
     *
     * @return array
     */
    public function regions();

    /**
     * Get all of the valid server sizes for the provider.
     *
     * @return array
     */
    public function sizes();

    /**
     * Create a new server.
     *
     * @param  string  $name
     * @param  string  $size
     * @param  string  $region
     * @return string
     */
    public function createServer();

    /**
     * Get the public Ipv4 address for a server by ID.
     *
     * @param  Server  $server
     * @return string|null
     */
    public function getAddress();

    /**
     * Get the public Ipv6 address for a server by ID.
     *
     * @param  Server  $server
     * @return string|null
     */
    public function getIpv6Address();

    /**
     * Get the private IP address for a server by ID.
     *
     * @param  Server  $server
     * @return string|null
     */
    public function getPrivateIpAddress();

    /**
     * Delete the given server.
     *
     * @param  Server  $server
     * @return void
     */
    public function deleteServer();
}