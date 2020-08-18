<?php

namespace Tests\Unit\Server;

use Tests\TestCase;

class SysuserTest extends TestCase
{
    private static $sysuser;

    public static function getSysuser($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$sysuser || $forceCreate) {
            return self::$sysuser = factory(\App\Sysuser::class)->states($states)->create($attributes);
        } else {
            return self::$sysuser->fresh();
        }
    }

    public function test_sysuser_is_provisioned_on_create()
    {
        $sysuser = self::getSysuser(true);

        $this->assertEquals($sysuser->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_sysuser_can_be_tested()
    {
        $sysuser = self::getSysuser();

        $sysuser->test();

        $this->assertEquals($sysuser->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_name_has_to_be_unique_on_create()
    {
        $sysuser = self::getSysuser();

        $response = $this->post('/nova-api/sysusers/', ['name' =>  $sysuser->name]);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
    }

    public function test_sysuser_is_destroyed_on_delete()
    {
        $sysuser = self::getSysuser();

        $sysuser->delete();

        $this->assertIsBool($sysuser->trashed());
        $this->assertLastTask();
    }
}
