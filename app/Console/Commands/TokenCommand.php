<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class TokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token 
        {--user= : The user name (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create authentication token.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();

        $options = [];
        foreach ($users as $user) {
            $options[] = $user->name;
        }

        if (count($options) > 0) {
            $user = User::where('name', $this->choice('Select user', $options))->first();
        }

        if ($user) {
            $this->info("Generating token for $user->name...");
            $token = $user->createToken('token-name');
            $this->line($token->plainTextToken);
        }
    }
}
