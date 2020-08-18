<?php

namespace Tests\Unit\Server;

use Tests\TestCase;

class DomainTest extends TestCase
{
    private static $domain;

    public static function getDomain($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$domain || $forceCreate) {
            return self::$domain = factory(\App\Domain::class)->states($states)->create($attributes);
        } else {
            return self::$domain->fresh();
        }
    }

    public function test_domain_is_provisioned_on_create()
    {
        factory(\App\Server::class)->states(['ready', 'loadbalancer']);
        $domain = self::getDomain(true, [], 'withProvisionedSite');

        $this->assertEquals($domain->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_domain_is_destroyed_on_delete()
    {
        $domain = self::getDomain();

        $domain->delete();

        $this->assertIsBool($domain->trashed());
        $this->assertLastTask();
    }
}
