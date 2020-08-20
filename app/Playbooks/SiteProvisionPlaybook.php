<?php

namespace App\Playbooks;

use App\Site;

class SiteProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Site';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The site instance.
     *
     * @var Site
     */
    public $site;

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
     * @param  Site  $restore
     * @return void
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->server = $site->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/site/provision.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        $domains = [];
        foreach ($this->site->domains as $domain) {
            $domains[] = $domain->name;
            $domains[] = "www." . $domain->name;
        }

        $backends[] = '127.0.0.1:7082';
        if (!empty($this->server->private_address)) {
            $backends[] = $this->site->server->private_address . ':7082';
        }
        $backends[] =  $this->site->server->address . ':7082';

        return array_merge(parent::vars(), [
            'user' => (string) $this->site->sysuser->name,
            'site' => (string) $this->site->name,
            'domain' => (string) $this->site->domain,
            'domains' => (array) $domains,
            'php_version' => (int) $this->site->php_version,
            'email' => (string) app()->environment(['testing', 'local']) ? 'internal' : $this->site->sysuser->email,
            'backends' => (array) $backends
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
