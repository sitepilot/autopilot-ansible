<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProvisionTest extends TestCase
{
    private static $webServer;
    private static $sysuser;
    private static $site;
    private static $siteMount;
    private static $database;
    private static $domain;
    private static $serverKey;
    private static $sysuserKey;

    /* ========== Web Server Tests ========== */

    public function test_server_is_provisioned_on_create()
    {
        $webServer = self::getServer();

        $this->assertEquals($webServer->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_server_can_be_tested()
    {
        $webServer = self::getServer();

        $webServer->test();

        $this->assertEquals($webServer->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Sysuser Tests ========== */

    public function test_sysuser_is_provisioned_on_create()
    {
        $sysuser = self::getSysuser();

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

    /* ========== Site Tests ========== */

    public function test_site_is_provisioned_on_create()
    {
        $site = self::getSite();

        $this->assertEquals($site->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Site Mount Tests ========== */

    public function test_site_mount_is_provisioned_on_create()
    {
        $siteMount = self::getSiteMount();

        $this->assertEquals($siteMount->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Database Tests ========== */

    public function test_database_is_provisioned_on_create()
    {
        $database = self::getDatabase();

        $this->assertEquals($database->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Domain Tests ========== */

    public function test_domain_is_provisioned_on_create()
    {
        $domain = self::getDomain();

        $this->assertEquals($domain->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Key Tests ========== */

    public function test_key_is_provisioned_on_create()
    {
        $key = self::getServerKey();

        $this->assertEquals($key->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_sysuser_key_is_provisioned_on_create()
    {
        $key = self::getSysuserKey();

        $this->assertEquals($key->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    /* ========== Destroy Tests ========== */

    public function test_sysuser_key_is_destroyed_on_delete()
    {
        $key = self::getSysuserKey();

        $key->delete();

        $this->assertIsBool($key->trashed());
        $this->assertLastTask();
    }

    public function test_key_is_destroyed_on_delete()
    {
        $key = self::getServerKey();

        $key->delete();

        $this->assertIsBool($key->trashed());
        $this->assertLastTask();
    }

    public function test_domain_is_destroyed_on_delete()
    {
        $domain = self::getDomain();

        $domain->delete();

        $this->assertIsBool($domain->trashed());
        $this->assertLastTask();
    }

    public function test_database_is_destroyed_on_delete()
    {
        $database = self::getDatabase();

        $database->delete();

        $this->assertIsBool($database->trashed());
        $this->assertLastTask();
    }

    public function test_site_mount_is_destroyed_on_delete()
    {
        $siteMount = self::getSiteMount();

        $siteMount->delete();

        $this->assertIsBool($siteMount->trashed());
        $this->assertLastTask();
    }

    public function test_site_is_destroyed_on_delete()
    {
        $site = self::getSite();

        $site->delete();

        $this->assertIsBool($site->trashed());
        $this->assertLastTask();
    }

    public function test_sysuser_is_destroyed_on_delete()
    {
        $sysuser = self::getSysuser();

        $sysuser->delete();

        $this->assertIsBool($sysuser->trashed());
        $this->assertLastTask();
    }

    public function test_server_is_destroyed_on_delete()
    {
        $webServer = self::getServer();

        $webServer->delete();

        $this->assertIsBool($webServer->trashed());
        $this->assertLastTask();
    }

    /* ========== Getters ========== */

    public static function getServer($attributes = [])
    {
        if (!self::$webServer) {
            return self::$webServer = factory(\App\Server::class)->create($attributes);
        } else {
            return self::$webServer->fresh();
        }
    }

    public static function getSysuser($attributes = [])
    {
        if (!self::$sysuser) {
            if (isset(self::$webServer->id)) {
                $attributes['server_id'] = self::$webServer->id;
            }

            return self::$sysuser = factory(\App\Sysuser::class)->create($attributes);
        } else {
            return self::$sysuser->fresh();
        }
    }

    public static function getSite($attributes = [])
    {
        if (!self::$site) {
            if (isset(self::$sysuser->id)) {
                $attributes['sysuser_id'] = self::$sysuser->id;
            }

            return self::$site = factory(\App\Site::class)->create($attributes);
        } else {
            return self::$site->fresh();
        }
    }

    public static function getSiteMount($attributes = [])
    {
        if (!self::$siteMount) {
            if (isset(self::$site->id)) {
                $attributes['site_id'] = self::$site->id;
            }

            $attributes['sysuser_id'] = factory(\App\Sysuser::class)->create([
                'server_id' => self::$webServer->id
            ]);

            return self::$siteMount = factory(\App\SiteMount::class)->create($attributes);
        } else {
            return self::$siteMount->fresh();
        }
    }

    public static function getDatabase($attributes = [])
    {
        if (!self::$database) {
            if (isset(self::$site->id)) {
                $attributes['site_id'] = self::$site->id;
            }

            return self::$database = factory(\App\Database::class)->create($attributes);
        } else {
            return self::$database->fresh();
        }
    }

    public static function getDomain($attributes = [])
    {
        if (!self::$domain) {
            if (isset(self::$site->id)) {
                $attributes['site_id'] = self::$site->id;
            }

            return self::$domain = factory(\App\Domain::class)->create($attributes);
        } else {
            return self::$domain->fresh();
        }
    }

    public static function getServerKey($attributes = [])
    {
        if (!self::$serverKey) {
            if (isset(self::$webServer->id)) {
                $attributes['server_id'] = self::$webServer->id;
            }

            return self::$serverKey = factory(\App\Key::class)->create($attributes);
        } else {
            return self::$serverKey->fresh();
        }
    }

    public static function getSysuserKey($attributes = [])
    {
        if (!self::$sysuserKey) {
            if (isset(self::$sysuser->id)) {
                $attributes['server_id'] = self::$sysuser->server_id;
                $attributes['sysuser_id'] = self::$sysuser->id;
            }

            return self::$sysuserKey = factory(\App\Key::class)->create($attributes);
        } else {
            return self::$sysuserKey->fresh();
        }
    }
}
