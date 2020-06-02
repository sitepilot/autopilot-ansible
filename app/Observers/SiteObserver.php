<?php

namespace App\Observers;

use App\Site;
use App\Sysuser;

class SiteObserver
{
    /**
     * Handle the site "creating" event.
     *
     * @param  \App\Server  $server
     * @return void
     */
    public function creating(Site $site)
    {
        if (empty($site->sysuser_id)) {
            $sysuser = Sysuser::create([
                'name' => $site->name
            ]);
            
            $site->sysuser_id = $sysuser->id;
        }
    }

    /**
     * Handle the site "created" event.
     *
     * @param  \App\Site  $site
     * @return void
     */
    public function created(Site $site)
    {
        if (!$site->isReady()) {
            $site->provision();
        }
    }

    /**
     * Handle the site "updated" event.
     *
     * @param  \App\Site  $site
     * @return void
     */
    public function updated(Site $site)
    {
        if ($site->wasChanged(['php_version'])) {
            $site->provision();
        }
    }

    /**
     * Handle the site "deleted" event.
     *
     * @param  \App\Site  $site
     * @return void
     */
    public function deleted(Site $site)
    {
        if (!$site->isDestroyed()) {
            $site->deleteFromServer();
        }
    }

    /**
     * Handle the site "restored" event.
     *
     * @param  \App\Site  $site
     * @return void
     */
    public function restored(Site $site)
    {
        //
    }

    /**
     * Handle the site "force deleted" event.
     *
     * @param  \App\Site  $site
     * @return void
     */
    public function forceDeleted(Site $site)
    {
        //
    }
}
