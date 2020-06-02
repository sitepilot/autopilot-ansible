<?php

namespace Tests\Unit\Server;

use Tests\TestCase;

class SiteTest extends TestCase
{
    private static $site;

    public static function getSite($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$site || $forceCreate) {
            return self::$site = factory(\App\Site::class)->states($states)->create($attributes);
        } else {
            return self::$site->fresh();
        }
    }

    public function test_site_is_provisioned_on_create()
    {
        $site = self::getSite(true, [], 'withProvisionedSysuser');

        $this->assertEquals($site->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_site_is_destroyed_on_delete()
    {
        $site = self::getSite();

        $site->delete();

        $this->assertEquals($site->fresh()->status, 'destroyed');
        $this->assertLastTask();
    }
}
