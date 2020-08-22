<?php

namespace App\Playbooks;

use App\Site;

class SiteDestroyPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Destroy Site';

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
        'shared', 'dedicated', 'development'
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
        return 'ansible/playbooks/site/destroy.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'user' => (string) $this->site->sysuser->name,
            'site' => (string) $this->site->name
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
