<?php

namespace App\Observers;

use App\Server;
use App\Sysuser;
use Illuminate\Support\Str;

class SysuserObserver
{
    /**
     * Handle the key "creating" event.
     *
     * @param  \App\Sysuser  $key
     * @return void
     */
    public function creating(Sysuser $sysuser)
    {
        if (empty($sysuser->server_id)) {
            $server = Server::where('type', 'shared')
                ->orderBy('sites_count', 'asc')
                ->withCount('sites')
                ->first();

            $sysuser->server_id = $server->id;
        }

        $sysuser->generateKeypair();

        if (empty($sysuser->password)) $sysuser->password = Str::random(12);
        if (empty($sysuser->mysql_password)) $sysuser->mysql_password = Str::random(12);
    }

    /**
     * Handle the sysuser "created" event.
     *
     * @param  \App\Sysuser  $sysuser
     * @return void
     */
    public function created(Sysuser $sysuser)
    {
        if (!$sysuser->isReady()) {
            $sysuser->provision();
        }
    }

    /**
     * Handle the sysuser "updated" event.
     *
     * @param  \App\Sysuser  $sysuser
     * @return void
     */
    public function updated(Sysuser $sysuser)
    {
        if ($sysuser->wasChanged([
            'full_name',
            'email',
            'password',
            'mysql_password',
            'isolated',
            'private_key',
            'public_key'
        ])) {
            $sysuser->provision();
        }
    }

    /**
     * Handle the sysuser "deleted" event.
     *
     * @param  \App\Sysuser  $sysuser
     * @return void
     */
    public function deleted(Sysuser $sysuser)
    {
        if (!$sysuser->isDestroyed()) {
            $sysuser->deleteFromServer();
        }
    }

    /**
     * Handle the sysuser "restored" event.
     *
     * @param  \App\Sysuser  $sysuser
     * @return void
     */
    public function restored(Sysuser $sysuser)
    {
        //
    }

    /**
     * Handle the sysuser "force deleted" event.
     *
     * @param  \App\Sysuser  $sysuser
     * @return void
     */
    public function forceDeleted(Sysuser $sysuser)
    {
        //
    }
}
