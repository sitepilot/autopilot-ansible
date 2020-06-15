<?php

use Faker\Generator as Faker;

$factory->define(\App\Domain::class, function (Faker $faker) {
    return [
        'name' => $faker->domainWord . '.com',
        'site_id' => factory(\App\Site::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\Domain::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a site
$factory->state(\App\Domain::class, 'withProvisionedSite', function (Faker $faker) {
    return [
        'site_id' => factory(\App\Site::class)
    ];
});
