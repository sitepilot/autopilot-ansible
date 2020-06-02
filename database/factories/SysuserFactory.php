<?php

use Faker\Generator as Faker;

$factory->define(\App\Sysuser::class, function (Faker $faker) {
    return [
        'name' => $faker->domainWord,
        'full_name' => $faker->name,
        'email' => $faker->email,
        'isolated' => true,
        'password' => 'supersecret',
        'mysql_password' => 'supersecret',
        'server_id' => factory(\App\Server::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Sysuser::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a server
$factory->state(\App\Sysuser::class, 'withProvisionedServer', function (Faker $faker) {
    return [
        'server_id' => factory(\App\Server::class)
    ];
});

