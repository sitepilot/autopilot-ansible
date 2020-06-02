<?php

use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (!User::count()) {
            factory(User::class)->create([
                'name' => 'Admin',
                'email' => 'admin@sitepilot.io'
            ]);
        }
    }
}
