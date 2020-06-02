<?php

namespace App\Traits;

use App\ShellResponse;
use App\ShellProcessRunner;
use Symfony\Component\Process\Process;

trait InteractsWithAnsible
{
    /**
     * Run the given playbook on a remote server.
     *
     * @return $this
     */
    public function run()
    {
        $this->markAsRunning();

        return $this->updateForResponse(
            $this->runPlaybook($this->options['timeout'] ?? 60)
        );
    }

    /**
     * Update the model for the given SSH response.
     *
     * @param  ShellResponse $response
     * @return $this
     */
    protected function updateForResponse(ShellResponse $response)
    {
        $this->update([
            'status' => $response->timedOut ? 'timeout' : 'finished',
            'exit_code' => $response->exitCode,
            'output' => $response->output,
        ]);

        foreach ($this->options['handle'] ?? [] as $callback) {
            is_object($callback)
                ? $callback->handle($this)
                : app($callback)->handle($this);
        }

        return $this;
    }

    /**
     * Run a given playbook.
     *
     * @param  int $timeout
     * @return ShellResponse
     */
    protected function runPlaybook($timeout = 60)
    {
        $inventoryFile = $this->server->inventoryPath();
        $playbookFile = base_path($this->playbook);

        return ShellProcessRunner::run(
            $this->toProcess($inventoryFile, $playbookFile, $timeout)
        );
    }

    /**
     * Create a Process instance for the given playbook.
     *
     * @param  string  $inventoryFile
     * @param  string  $playbookFile
     * @param  int     $timeout
     * @return Process
     */
    protected function toProcess($inventoryFile, $playbookFile, $timeout)
    {
        $cmd = [
            "ansible-playbook", $playbookFile, "-i", $inventoryFile
        ];

        if (is_array($this->vars)) {
            $cmd = array_merge($cmd, ["--extra-vars", json_encode($this->vars)]);
        }

        return (new Process($cmd, null, ['DEFAULT_LOCAL_TMP' => '/tmp/ansible/']))->setTimeout($timeout);
    }
}
