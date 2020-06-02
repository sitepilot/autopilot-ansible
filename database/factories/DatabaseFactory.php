<?php

use Faker\Generator as Faker;

$factory->define(\App\Database::class, function (Faker $faker) {
    return [
        'password' => 'supersecret',
        'sysuser_id' => factory(\App\Sysuser::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Database::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a sysuser
$factory->state(\App\Database::class, 'withProvisionedSysuser', function (Faker $faker) {
    return [
        'sysuser_id' => factory(\App\Sysuser::class)
    ];
});
