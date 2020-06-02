<?php

namespace App\Playbooks;

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
     * @return void
     */
    public $serverTypes = [
        //
    ];

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
            'ansible_user' => (string) $this->server->user,
            'ansible_host' => (string) $this->server->address,
            'ansible_port' => (string) $this->server->port,
            'ansible_ssh_private_key_file' => (string) $this->server->keyPath(),
            'ansible_ssh_common_args' => '-o StrictHostKeyChecking=no',
            'ansible_python_interpreter' => '/usr/bin/python3',
            'sitepilot_managed' => 'WARNING: This file is managed by Sitepilot, any changes will be overwritten (updated at: {{ansible_date_time.date}} {{ansible_date_time.time}}).'
        ];
    }

    /**
     * Checks if the playbook is allowed to run on the server.
     *
     * @return boolean
     */
    public function allowedToRun()
    {
        return in_array($this->server->type, $this->serverTypes);
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
