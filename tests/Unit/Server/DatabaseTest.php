<?php

namespace Tests\Unit\Server;

use Tests\TestCase;

class DatabaseTest extends TestCase
{
    private static $database;

    public static function getDatabase($forceCreate = false, $attributes = [], $states = [])
    {
        if (!self::$database || $forceCreate) {
            return self::$database = factory(\App\Database::class)->states($states)->create($attributes);
        } else {
            return self::$database->fresh();
        }
    }

    public function test_database_is_provisioned_on_create()
    {
        $database = self::getDatabase(true);

        $this->assertEquals($database->fresh()->status, 'ready');
        $this->assertLastTask();
    }

    public function test_database_is_destroyed_on_delete()
    {
        $database = self::getDatabase();

        $database->delete();

        $this->assertEquals($database->fresh()->status, 'destroyed');
        $this->assertLastTask();
    }
}
