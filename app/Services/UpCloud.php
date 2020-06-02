<?php

namespace App\Services;

use Exception;
use App\Server;
use Httpful\Http;
use Httpful\Request;
use App\Contracts\ServerProviderClient;

class UpCloud implements ServerProviderClient
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

        $template = Request::init()
            ->method(Http::POST)
            ->expectsJson()
            ->sendsJson()
            ->authenticateWithBasic(env('UPCLOUD_API_USERNAME'), env('UPCLOUD_API_PASSWORD'));

        Request::ini($template);
    }

    /**
     * Call UpCloud API.
     *
     * @param string $method Request method (e.g. get)
     * @param string $query Request query (url).
     * @param array $body Request body (data).
     *
     * @throws Exception In case an error occurs in api call.
     *
     * @return array
     */
    private function callApi(string $method, string $query, array $body = [])
    {
        $endpoint = 'https://api.upcloud.com/1.3/';

        try {
            switch ($method) {
                case 'get':
                    $response = Request::get($endpoint . $query);
                    break;
                case 'post':
                    $response = Request::post($endpoint . $query);
                    break;
                case 'put':
                    $response = Request::put($endpoint . $query);
                    break;
                case 'patch':
                    $response = Request::patch($endpoint . $query);
                    break;
                case 'delete':
                    $response = Request::delete($endpoint . $query);
                    break;
            }

            if (!empty($body)) {
                $response->body(json_encode($body));
            }

            $response = $response->send();

            if ($response->hasErrors()) {
                throw new Exception("Could not communicate with UpCloud API.");
            }

            return $response->body;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Determine if the provider credentials are valid.
     *
     * @return string
     */
    public function valid()
    {
        return $this->callApi('get', 'account')->account->username ?? false;
    }

    /**
     * Get all of the valid regions for the provider.
     *
     * @return array
     */
    public function regions()
    {
        $regions = [];
        $zones = $this->callApi('get', 'zone')->zones->zone ?? [];

        foreach ($zones as $zone) {
            $regions[] = $zone->id;
        }

        return $regions;
    }

    /**
     * Get all of the valid server sizes for the provider.
     *
     * @return array
     */
    public function sizes()
    {
        $sizes = [];
        $plans = $this->callApi('get', 'plan')->plans->plan ?? [];

        foreach ($plans as $plan) {
            $sizes[] = $plan->name;
        }

        return $sizes;
    }

    /**
     * Get storage size by plan.
     *
     * @return int
     */
    public function getStorageSizeByPlan($size)
    {
        $plans = $this->callApi('get', 'plan')->plans->plan ?? [];

        foreach ($plans as $plan) {
            if ($plan->name === $size) {
                return $plan->storage_size;
            }
        }

        return 10;
    }

    /**
     * Create a new server.
     *
     * @return string $id
     */
    public function createServer()
    {
        $response = $this->callApi('post', 'server', [
            "server" => [
                "zone" => $this->server->region,
                "title" => $this->server->name,
                "hostname" => $this->server->name,
                "plan" => $this->server->size,
                "storage_devices" => [
                    "storage_device" => [
                        "action" => "clone",
                        "storage" => "01000000-0000-4000-8000-000030200200", // Ubuntu 20.04
                        "title" => $this->server->name,
                        "size" => $this->getStorageSizeByPlan($this->server->size)
                    ]
                ],
                "login_user" => [
                    "ssh_keys" => [
                        "ssh_key" => [
                            trim($this->server->public_key)
                        ]
                    ]
                ]
            ]
        ]);

        return $response->server->uuid;
    }

    /**
     * Get a specific address of the server.
     * 
     * @param  string $type
     * @param  string $family
     * @return string 
     */
    public function getIpAddress($type = 'public', $family = 'IPv4')
    {
        $response = $this->callApi('get', 'server/' . $this->server->provider_server_id);

        return collect($response->server->ip_addresses->ip_address)->filter(function ($address) use ($type, $family) {
            return ($address->access ?? null) == $type && ($address->family ?? null) == $family;
        })->first()->address ?? null;
    }

    /**
     * Get the public Ipv4 address for a server by ID.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->getIpAddress();
    }

    /**
     * Get the public Ipv6 address for a server by ID.
     *
     * @return string|null
     */
    public function getIpv6Address()
    {
        return $this->getIpAddress('public', 'IPv6');
    }

    /**
     * Get the private IP address for a server by ID.
     *
     * @return string|null
     */
    public function getPrivateIpAddress()
    {
        return $this->getIpAddress('utility');
    }

    /**
     * Stop the given server.
     *
     * @return void
     */
    public function stopServer()
    {
        $this->callApi('post', 'server/' . $this->server->provider_server_id . '/stop');
    }

    /**
     * Check if the server is stopped.
     *
     * @return void
     */
    public function isStopped()
    {
        $response = $this->callApi('get', 'server/' . $this->server->provider_server_id);

        return $response->server->state == 'stopped' ?? false;
    }

    /**
     * Start the given server.
     *
     * @return void
     */
    public function startServer()
    {
        $this->callApi('post', 'server/' . $this->server->provider_server_id . '/start');
    }

    /**
     * Check if the server is started.
     *
     * @return void
     */
    public function isStarted()
    {
        $response = $this->callApi('get', 'server/' . $this->server->provider_server_id);

        return $response->server->state == 'started' ?? false;
    }

    /**
     * Delete the given server.
     *
     * @return void
     */
    public function deleteServer()
    {
        $this->stopServer();

        sleep(60);

        $this->callApi('delete', 'server/' . $this->server->provider_server_id . '/?storages=1');
    }
}
