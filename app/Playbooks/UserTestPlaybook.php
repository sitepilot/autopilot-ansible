<?php

namespace App\Playbooks;

use App\Sysuser;
use Illuminate\Support\Facades\Log;

class UserTestPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Test User';

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
        'shared', 'dedicated', 'development'
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
        return 'ansible/playbooks/user/test.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        $sites = [];
        $domains = [];
        $databases = [];
        $sshKeys = [];

        foreach ($this->sysuser->sites as $site) {
            $sites[] = $site->name;

            foreach ($site->domains as $domain) {
                $domains[] = $domain->name;
            }

            foreach ($site->databases as $database) {
                $databases[] = $database->name;
            }
        }

        foreach ($this->sysuser->keys as $key) {
            $sshKeys[] = $key->key;
        }

        return array_merge(parent::vars(), [
            'user' => (string) $this->sysuser->name,
            'password' => (string) $this->sysuser->password,
            'mysql_password' => (string) $this->sysuser->mysql_password,
            'sites' => (array) $sites,
            'databases' => (array) $databases,
            'domains' => (array) $domains,
            'ssh_keys' => (array) $sshKeys
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
