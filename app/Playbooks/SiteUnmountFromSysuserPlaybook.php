<?php

namespace App\Playbooks;

use App\Site;

class SiteUnmountFromSysuserPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Unmount Site From User';

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
     * The playbook variables.
     *
     * @var array
     */
    public $vars;

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
    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/site/unmountFromUser.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), $this->vars);
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
