<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => 'Captain',
        'email' => (string) $faker->email,
        'email_verified_at' => now(),
        'password' => Hash::make('supersecret'),
        'remember_token' => Str::random(10),
    ];
});
