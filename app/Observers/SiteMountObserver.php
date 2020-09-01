<?php

namespace App\Observers;

use App\SiteMount;

class SiteMountObserver
{
    /**
     * Handle the site mount "created" event.
     *
     * @param  siteMount  $siteMount
     * @return void
     */
    public function created(SiteMount $siteMount)
    {
        if (!$siteMount->isReady()) {
            $siteMount->provision();
        }
    }

    /**
     * Handle the site "deleted" event.
     *
     * @param  siteMount  $siteMount
     * @return void
     */
    public function deleted(SiteMount $siteMount)
    {
        if (!$siteMount->isForceDeleting()) {
            $siteMount->deleteFromServer();
        }
    }
}
