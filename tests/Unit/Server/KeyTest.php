<?php

namespace Tests\Unit\Server;

use Tests\TestCase;

class KeyTest extends TestCase
{
    private static $key;

    public static function getKey($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$key || $forceCreate) {
            return self::$key = factory(\App\Key::class)->states($states)->create($attributes);
        } else {
            return self::$key->fresh();
        }
    }

    public function test_key_is_provisioned_on_create()
    {
        $key = self::getKey(true);

        $this->assertEquals($key->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_key_is_destroyed_on_delete()
    {
        $key = self::getKey();

        $key->delete();

        $this->assertIsBool($key->trashed());
        $this->assertLastTask();
    }

    public function test_sysuser_key_is_provisioned_on_create()
    {
        $key = self::getKey(true, [], ['withProvisionedSysuser']);

        $this->assertEquals($key->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_sysuser_key_is_destroyed_on_delete()
    {
        $key = self::getKey();

        $key->delete();

        $this->assertIsBool($key->trashed());
        $this->assertLastTask();
    }
}
