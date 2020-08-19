<?php

namespace App\Playbooks;

use App\Server;

class ServerConnectPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Check Server Connection';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * Allowed server types the playbook can run on.
     *
     * @return void
     */
    public $serverTypes = [
        'shared', 'dedicated'
    ];

    /**
     * Create a new playbook instance.
     *
     * @param  Server  $restore
     * @return void
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/server/connect.yml';
    }

    /**
     * Get the timeout for the playbook.
     *
     * @return int|null
     */
    public function timeout()
    {
        return 20;
    }
}
