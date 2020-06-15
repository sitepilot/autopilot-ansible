<?php

namespace Tests\Unit;

use Tests\TestCase;

class ServerTest extends TestCase
{
    private static $server;

    public static function getServer($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$server || $forceCreate) {
            return self::$server = factory(\App\Server::class)->states($states)->create($attributes);
        } else {
            return self::$server->fresh();
        }
    }

    public function test_server_is_provisioned_on_create()
    {
        $server = self::getServer(true);

        $this->assertEquals($server->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_server_can_be_tested()
    {
        $server = self::getServer();

        $server->test();

        $this->assertEquals($server->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_server_can_renew_certificates()
    {
        $server = self::getServer();

        $server->certRenew();

        $this->assertEquals($server->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_server_is_destroyed_on_delete()
    {
        $server = self::getServer();

        $server->delete();

        $this->assertEquals($server->fresh()->status, 'destroyed');
        $this->assertLastTask();
    }

    public function test_loadbalancer_is_provisioned_on_create()
    {
        $server = self::getServer(true, [
            'address' => 'autopilot-test'
        ]);

        $this->assertEquals($server->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_loadbalancer_is_destroyed_on_delete()
    {
        $server = self::getServer();

        $server->delete();

        $this->assertEquals($server->fresh()->status, 'destroyed');
        $this->assertLastTask();
    }
}
