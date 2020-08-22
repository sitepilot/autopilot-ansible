<?php

namespace App\Playbooks;

use App\Key;
use App\Sysuser;

class KeyProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Key';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The key instance.
     *
     * @var Key
     */
    public $key;

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
     * @param  Key  $database
     * @return void
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
        $this->server = $key->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/key/provision.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'user' => (string) $this->key->sysuser ? $this->key->sysuser->name : 'sitepilot',
            'key' => (string) $this->key->key,
            'comment' => (string) $this->key->name
        ]);
    }

    /**
     * Get the timeout for the playbook.
     *
     * @return int|null
     */
    public function timeout()
    {
        return 300;
    }
}
