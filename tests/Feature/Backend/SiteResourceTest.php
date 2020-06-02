<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiteResourceTest extends TestCase
{
    public function test_name_has_to_be_under_255_chars_on_create()
    {
        $site = factory(\App\Site::class)->make([
            'name' => str_repeat('J', 33),
        ]);

        $response = $this->post('/nova-api/sites/', $site->toArray());
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name may not be greater than 32 characters.',
        ]);
    }

    public function test_name_must_not_contain_invalid_characters()
    {
        $site = factory(\App\Site::class)->make([
            'name' => 'Test Site',
        ]);

        $response = $this->post('/nova-api/sites/', $site->toArray());
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name may only contain letters, numbers, dashes and underscores.',
        ]);
    }

    public function test_name_has_to_be_unique_on_create()
    {
        $site = factory(\App\Site::class)->states('ready')->create();

        $response = $this->post('/nova-api/sites/', ['name' =>  $site->name]);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
    }
}
