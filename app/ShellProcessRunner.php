<?php

namespace App;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class ShellProcessRunner
{
    /**
     * Run the given process and return it.
     *
     * @param  Process $process
     * @param  mixed $output
     * @return ShellResponse
     */
    public static function run(Process $process)
    {
        try {
            $process = tap($process)->run($output = new ShellOutput);
        } catch (ProcessTimedOutException $e) {
            $timedOut = true;
        }

        return new ShellResponse(
            $process->getExitCode(), (string) ($output ?? ''), $timedOut ?? false
        );
    }
}