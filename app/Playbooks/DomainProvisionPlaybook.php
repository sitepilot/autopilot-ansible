<?php

namespace App\Playbooks;

use App\Domain;

class DomainProvisionPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Provision Domain';

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
        return 'ansible/playbooks/domain/provision.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        $backends[] = '127.0.0.1:7082';

        if (!empty($this->domain->site->server->private_address)) {
            $backends[] = $this->domain->site->server->private_address . ':443';
        }

        $backends[] =  $this->domain->site->server->address . ':443';

        return array_merge(parent::vars(), [
            'config_name' => (string) 'autopilot-domain-' . $this->domain->id,
            'site_name' => (string) $this->domain->site->name,
            'domain' => (string) $this->domain->name,
            'email' => (string) app()->environment(['testing', 'local']) ? 'internal' : 'admin@' . $this->domain->name,
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
