<?php

namespace Tests\Feature;

use Tests\TestCase;

class BackendAccessTest extends TestCase
{
    public function test_user_has_backend_access()
    {
        $response = $this->get('/nova-api/users/' . $this->user->id);

        $response->assertStatus(200);
    }

    public function test_backend_is_named_correctly()
    {
        $this->assertEquals(config('nova.name'), 'Autopilot');
    }

    public function test_backend_is_at_the_correct_url()
    {
        $this->assertEquals(config('nova.path'), '/admin');
    }
}
