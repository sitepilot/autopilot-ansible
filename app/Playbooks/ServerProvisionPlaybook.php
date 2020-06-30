<?php

namespace App\Playbooks;

use App\Server;

class ServerProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Server';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The ansible tags.
     *
     * @var array
     */
    public $tags;

    /**
     * Allowed server types the playbook can run on.
     *
     * @return void
     */
    public $serverTypes = [
        'shared', 'dedicated', 'loadbalancer'
    ];

    /**
     * Create a new playbook instance.
     *
     * @param  Server  $restore
     * @return void
     */
    public function __construct(Server $server, $tags = [])
    {
        $this->server = $server;
        $this->tags = $tags;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        if ($this->server->type == 'loadbalancer') {
            return 'ansible/playbooks/server/loadbalancer.yml';
        }

        return 'ansible/playbooks/server/webserver.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'hostname' => (string) $this->server->name,
            'admin_pass' => (string) $this->server->admin_password,
            'mysql_root_pass' => (string) $this->server->mysql_password,
            'timezone' => (string) $this->server->timezone,
            'admin_email' => (string) $this->server->admin_email,
            'health_email' => (string) $this->server->health_email,
            'php_post_max_size' => (int) $this->server->php_post_max_size,
            'php_upload_max_filesize' => (int) $this->server->php_upload_max_filesize,
            'php_memory_limit' => (int) $this->server->php_memory_limit,
            'smtp_relay_host' => (string) $this->server->smtp_relay_host,
            'smtp_relay_domain' => (string) $this->server->smtp_relay_domain,
            'smtp_relay_user' => (string) $this->server->smtp_relay_user,
            'smtp_relay_password' => (string) $this->server->smtp_relay_password,
            'backup_s3_key' => (string) $this->server->backup_s3_key,
            'backup_s3_secret' => (string) $this->server->backup_s3_secret,
            'backup_password' => (string) $this->server->backup_password,
            'server_type' => (string) $this->server->type == 'shared' || $this->server->type == 'dedicated' ? 'webserver' : $this->server->type,
            'authorized_addresses' => (array) $this->server->authorized_addresses
        ]);
    }

    /**
     * Get the tags for the playbook.
     *
     * @return array
     */
    public function tags()
    {
        return array_merge(parent::tags(), $this->tags);
    }

    /**
     * Get the timeout for the playbook.
     *
     * @return int|null
     */
    public function timeout()
    {
        return 1200;
    }
}
