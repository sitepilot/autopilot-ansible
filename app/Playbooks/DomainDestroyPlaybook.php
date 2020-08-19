<?php

namespace App\Playbooks;

use App\Domain;

class DomainDestroyPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Destroy Domain';

    /**
     * The domain instance.
     *
     * @var Domain
     */
    public $domain;

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
     * @param  Domain  $restore
     * @return void
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/domain/destroy.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'config_name' => (string) 'autopilot-domain-' . $this->domain->id,
            'domain' => (string) $this->domain->name
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
