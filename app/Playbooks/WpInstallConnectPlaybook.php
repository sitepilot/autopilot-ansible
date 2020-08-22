<?php

namespace App\Playbooks;

use App\Server;
use App\WpInstall;

class WpInstallConnectPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Check WordPress Connection';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The WordPress install instance.
     *
     * @var WpInstall
     */
    public $wpInstall;

    /**
     * Allowed server types the playbook can run on.
     *
     * @return void
     */
    public $serverTypes = [
        'shared', 'dedicated', 'development'
    ];

    /**
     * Create a new playbook instance.
     *
     * @param  Server  $restore
     * @return void
     */
    public function __construct(WpInstall $wpInstall)
    {
        $this->wpInstall = $wpInstall;
        $this->server = $wpInstall->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/wordpress/connect.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'path' => (string) $this->wpInstall->getPath()
        ]);
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
