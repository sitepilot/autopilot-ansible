<?php

namespace App\Playbooks;

use App\Server;

class Playbook
{
    /**
     * The user that the playbook should be run as.
     *
     * @var string
     */
    public $sshAs = 'root';

    /**
     * Allowed server types the playbook can run on.
     *
     * @var array
     */
    public $serverTypes = [];

    /**
     * Get the name of the playbook.
     *
     * @return string
     */
    public function name()
    {
        return $this->name ?? '';
    }

    /**
     * Get the timeout for the playbook.
     *
     * @return int|null
     */
    public function timeout()
    {
        return 10;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return '';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return [
            'ansible_ssh_common_args' => '-o StrictHostKeyChecking=no',
            'ansible_python_interpreter' => '/usr/bin/python3',
            'sitepilot_managed' => 'WARNING: This file is managed by Sitepilot, any changes will be overwritten (updated at: {{ansible_date_time.date}} {{ansible_date_time.time}}).'
        ];
    }

    /**
     * Get the tags for the playbook.
     *
     * @return array
     */
    public function tags()
    {
        return [];
    }

    /**
     * Checks if the playbook is allowed to run on the server.
     *
     * @param Server $server
     * @return boolean
     */
    public function allowedToRun(Server $server)
    {
        return in_array($server->type, $this->serverTypes);
    }

    /**
     * Render the playbook as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->playbook();
    }
}
