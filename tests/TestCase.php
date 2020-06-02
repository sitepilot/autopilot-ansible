<?php

namespace Tests;

use App\Task;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    /**
     * If true, setup has run at least once.
     * @var boolean
     */
    protected static $setUpHasRunOnce = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call(
                'db:seed'
            );
            static::$setUpHasRunOnce = true;
        }

        $this->user = factory(\App\User::class)->create();

        $this->actingAs($this->user);
    }

    public function assertLastTask()
    {
        $task = Task::latest()->first();

        $this->assertEquals($task->successful(), true);
    }
}
