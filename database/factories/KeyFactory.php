<?php

use Faker\Generator as Faker;

$factory->define(\App\Key::class, function (Faker $faker) {
    return [
        'name' => $faker->email,
        'key' => trim(file_get_contents(base_path('docker/test/ssh/test_key.pub'))),
        'server_id' => factory(\App\Server::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Key::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a server
$factory->state(\App\Key::class, 'withProvisionedServer', function (Faker $faker) {
    return [
        'server_id' => factory(\App\Server::class)
    ];
});

// Create and provision a sysuser
$factory->state(\App\Key::class, 'withProvisionedSysuser', function (Faker $faker) {
    return [
        'sysuser_id' => factory(\App\Sysuser::class)
    ];
});
