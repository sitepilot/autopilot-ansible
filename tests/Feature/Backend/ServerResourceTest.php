<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServerResourceTest extends TestCase
{
    public function test_required_fields_when_provider_is_custom()
    {
        $response = $this->post('/nova-api/servers/', ['provider' => 'custom']);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'type' => 'The type field is required when provider is custom.',
            'address' => 'The address field is required when provider is custom.',
        ]);
    }

    public function test_required_fields_when_provider_is_not_custom()
    {
        $response = $this->post('/nova-api/servers/', ['provider' => 'upcloud']);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'region' => 'The region field is required unless provider is in custom.',
            'size' => 'The size field is required unless provider is in custom.',
        ]);
    }

    public function test_name_has_to_be_under_255_chars_on_create()
    {
        $server = factory(\App\Server::class)->make([
            'name' => str_repeat('J', 33),
        ]);

        $response = $this->post('/nova-api/servers/', $server->toArray());
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name may not be greater than 32 characters.',
        ]);
    }

    public function test_name_must_not_contain_invalid_characters()
    {
        $server = factory(\App\Server::class)->make([
            'name' => 'Test Server',
        ]);

        $response = $this->post('/nova-api/servers/', $server->toArray());
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name may only contain letters, numbers, dashes and underscores.',
        ]);
    }

    public function test_name_has_to_be_unique_on_create()
    {
        $server = factory(\App\Server::class)->states('ready')->create();

        $response = $this->post('/nova-api/servers/', ['name' =>  $server->name]);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
    }
}
