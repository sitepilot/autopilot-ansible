<?php

namespace App\Observers;

use App\Server;
use App\SecureShellKey;
use Illuminate\Support\Str;

class ServerObserver
{
    /**
     * Handle the server "creating" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function creating(Server $server)
    {
        $server->generateKeypair();

        if (!$server->isUnmanaged()) {
            // Generate passwords
            if (empty($server->admin_password)) $server->admin_password = Str::random(12);
            if (empty($server->mysql_password)) $server->mysql_password = Str::random(12);

            // General configuration
            if (empty($server->timezone)) $server->timezone = 'Europe/Amsterdam';
            if (empty($server->admin_email)) $server->admin_email = 'support@sitepilot.io';
            if (empty($server->health_email)) $server->health_email = 'health@sitepilot.io';

            // PHP configuration
            if (empty($server->php_post_max_size)) $server->php_post_max_size = 64;
            if (empty($server->php_upload_max_filesize)) $server->php_upload_max_filesize = 32;
            if (empty($server->php_memory_limit)) $server->php_memory_limit = 256;

            // SMTP Relay configuration
            if (empty($server->smtp_relay_host)) $server->smtp_relay_host = 'smtp.eu.mailgun.org';
            if (empty($server->smtp_relay_domain)) $server->smtp_relay_domain = 'mg.example.com';
            if (empty($server->smtp_relay_user)) $server->smtp_relay_user = 'postmaster@mg.example.com';
            if (empty($server->smtp_relay_password)) $server->smtp_relay_password = 'supersecret';

            // Backup configuration
            if (empty($server->backup_password)) $server->backup_password = Str::random(12);
        }
    }

    /**
     * Handle the server "created" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function created(Server $server)
    {
        if (!$server->isReady()) {
            $server->provision();
        }
    }

    /**
     * Handle the server "updated" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function updated(Server $server)
    {
        if ($server->wasChanged([
            'name',
            'timezone',
            'admin_email',
            'health_email',
            'php_post_max_size',
            'php_upload_max_filesize',
            'php_memory_limit',
            'smtp_relay_host',
            'smtp_relay_domain',
            'smtp_relay_user',
            'smtp_relay_password',
            'admin_password',
            'backup_s3_key',
            'backup_s3_secret',
            'backup_s3_bucket',
            'backup_password'
        ])) {
            $server->provision();
        }
    }

    /**
     * Handle the server "deleted" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function deleted(Server $server)
    {
        if (!$server->isDestroyed()) {
            $server->deleteFromProvider();
        }
    }

    /**
     * Handle the server "restored" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function restored(Server $server)
    {
        //
    }

    /**
     * Handle the server "force deleted" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function forceDeleted(Server $server)
    {
        //
    }
}
