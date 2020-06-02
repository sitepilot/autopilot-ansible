<?php

namespace App\Playbooks;

use App\Sysuser;

class UserDestroyPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Destroy User';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The sysuser instance.
     *
     * @var Sysuser
     */
    public $sysuser;

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
     * @param  Sysuser  $restore
     * @return void
     */
    public function __construct(Sysuser $sysuser)
    {
        $this->sysuser = $sysuser;
        $this->server = $sysuser->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/user/destroy.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'user' => (string) $this->sysuser->name
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
