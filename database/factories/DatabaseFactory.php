<?php

use Faker\Generator as Faker;

$factory->define(\App\Database::class, function (Faker $faker) {
    return [
        'password' => 'supersecret',
        'site_id' => factory(\App\Site::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Database::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a sysuser
$factory->state(\App\Database::class, 'withProvisionedSite', function (Faker $faker) {
    return [
        'site_id' => factory(\App\Site::class)
    ];
});
