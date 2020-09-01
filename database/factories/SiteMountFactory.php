<?php

use Faker\Generator as Faker;

$factory->define(\App\SiteMount::class, function (Faker $faker) {
    return [
        'sysuser_id' => factory(\App\Sysuser::class)->states('ready'),
        'site_id' => factory(\App\Site::class)->states('ready')
    ];
});

// Set status to ready
$factory->state(\App\SiteMount::class, 'ready', function (Faker $faker) {
    return [
        'status' => 'ready'
    ];
});

// Create and provision a sysuser
$factory->state(\App\SiteMount::class, 'withProvisionedSysuser', function (Faker $faker) {
    return [
        'sysuser_id' => factory(\App\Sysuser::class)
    ];
});

// Create and provision a site
$factory->state(\App\SiteMount::class, 'withProvisionedSite', function (Faker $faker) {
    return [
        'site_id' => factory(\App\Site::class)
    ];
});
