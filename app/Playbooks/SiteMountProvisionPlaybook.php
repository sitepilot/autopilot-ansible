<?php

namespace App\Playbooks;

use App\SiteMount;

class SiteMountProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Site Mount';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The site mount instance.
     *
     * @var SiteMount
     */
    public $siteMount;

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
     * @param  array  $vars
     * @return void
     */
    public function __construct(SiteMount $siteMount)
    {
        $this->siteMount = $siteMount;
        $this->server = $siteMount->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/site/mount/provision.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'mount_user' => (string) $this->siteMount->sysuser->name,
            'mount_site' => (string) $this->siteMount->site->name,
            'source_user' => (string) $this->siteMount->site->sysuser->name,
            'source_site' => (string)  $this->siteMount->site->name
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
