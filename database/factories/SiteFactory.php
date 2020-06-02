<?php

use Faker\Generator as Faker;

$factory->define(\App\Site::class, function (Faker $faker) {
    return [
        'name' => $faker->domainWord,
        'sysuser_id' => factory(\App\Sysuser::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Site::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a sysuser
$factory->state(\App\Site::class, 'withProvisionedSysuser', function (Faker $faker) {
    return [
        'sysuser_id' => factory(\App\Sysuser::class)
    ];
});
