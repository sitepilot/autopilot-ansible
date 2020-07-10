<?php

use Faker\Generator as Faker;

$factory->define(\App\Server::class, function (Faker $faker) {
    return [
        'name' => $faker->domainWord . '-' . $faker->domainWord,
        'provider' => 'custom',
        'type' => 'shared',
        'port' => 22,
        'address' => env('TEST_WEB_IP', 'undefined'),
        'admin_password' => 'supersecret',
        'mysql_password' => 'supersecret'
    ];
});

// Set status to ready
$factory->state(\App\Server::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Set type to loadbalancer
$factory->state(\App\Server::class, 'loadbalancer', function (Faker $faker) {
    return [
        'type' => 'loadbalancer',
        'address' => env('TEST_LB_IP', 'undefined'),
    ];
});
