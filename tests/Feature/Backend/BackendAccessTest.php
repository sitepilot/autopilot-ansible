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
}
