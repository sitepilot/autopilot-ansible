<?php

namespace App\Playbooks;

use App\Database;

class DatabaseProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Database';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The database instance.
     *
     * @var Database
     */
    public $database;

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
     * @param  Database  $database
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->server = $database->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/database/provision.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'user' => (string) $this->database->user,
            'password' => (string) $this->database->password,
            'database' => (string) $this->database->name
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
