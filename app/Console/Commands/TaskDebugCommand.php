<?php

namespace App\Console\Commands;

use App\Task;
use Illuminate\Console\Command;

class TaskDebugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump task output.';

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
        $tasks = Task::orderBy('id', 'desc')->limit(10)->get();

        foreach($tasks as $task) {
            $this->line("");
            $this->info("========= $task->name =========");
            $this->line("Status: $task->status");
            $this->line("Exit Code: $task->exit_code");
            $this->line("Playbook: $task->playbook");
            $this->line("");
            $this->line($task->output);
            $this->line("");
            $this->line("Vars: " . print_r($task->vars, true));
            $this->info("========= End $task->name =========");
        }
    }
}
