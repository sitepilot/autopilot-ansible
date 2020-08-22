<?php

namespace App\Playbooks;

use App\Backup;
use App\Database;

class BackupRunPlaybook extends Playbook
{
    /**
     * The displayable name of the playbook.
     *
     * @var string
     */
    public $name = 'Run Backup';

    /**
     * The server instance.
     *
     * @var Server
     */
    public $server;

    /**
     * The backup instance.
     *
     * @var Backup
     */
    public $backup;

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
     * @param  Backup  $restore
     * @return void
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
        $this->server = $backup->server;
    }

    /**
     * Get the contents of the playbook.
     *
     * @return string
     */
    public function playbook()
    {
        return 'ansible/playbooks/backup/run.yml';
    }

    /**
     * Get the variables for the playbook.
     *
     * @return array
     */
    public function vars()
    {
        return array_merge(parent::vars(), [
            'backup_id' => (int) $this->backup->id,
            'backup_s3_key' => (string) $this->backup->server->backup_s3_key,
            'backup_s3_secret' => (string) $this->backup->server->backup_s3_secret,
            'backup_s3_bucket' => (string) $this->backup->server->backup_s3_bucket,
            'backup_password' => (string) $this->backup->server->backup_password,
            'backup_path' => (string) $this->backup->path,
            'backup_tag' => (string) $this->backup->getBackupTag(),
            'backup_resource_tag' => (string) $this->backup->getBackupResourceTag(),
            'backup_database_name' => (string) ($this->backup->backupable_type == Database::class ? $this->backup->backupable->name : '')
        ]);
    }

    /**
     * Get the timeout for the playbook.
     *
     * @return int|null
     */
    public function timeout()
    {
        return 900;
    }
}
