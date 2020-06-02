<?php

namespace App\Playbooks;

use App\Key;

class KeyDestroyPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Destroy Key';

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
        'shared', 'dedicated'
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
        return 'ansible/playbooks/key/destroy.yml';
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
            'key' => (string) $this->key->key
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
