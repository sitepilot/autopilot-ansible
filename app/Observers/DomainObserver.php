<?php

namespace App\Observers;

use App\Site;
use App\Domain;

class DomainObserver
{
    /**
     * Handle the domain "created" event.
     *
     * @param  \App\Domain  $domain
     * @return void
     */
    public function created(Domain $domain)
    {
        $domain->site->provision();

        $domain->provision();
    }

    /**
     * Handle the domain "updated" event.
     *
     * @param  \App\Domain  $domain
     * @return void
     */
    public function updated(Domain $domain)
    {
        if ($domain->wasChanged(['name'])) {
            $domain->site->provision();
        }

        if ($domain->wasChanged(['site_id'])) {
            $oldSite = Site::find($domain->getOriginal('site_id'));
            if ($oldSite) {
                $oldSite->provision();
            }
            $domain->site->provision();
        }
        if ($domain->wasChanged(['name', 'site_id'])) {
            $domain->provision();
        }
    }

    /**
     * Handle the domain "deleted" event.
     *
     * @param  \App\Domain  $domain
     * @return void
     */
    public function deleted(Domain $domain)
    {
        if (!$domain->isForceDeleting()) {
            $domain->site->provision();
            $domain->deleteFromServer();
        }
    }

    /**
     * Handle the domain "restored" event.
     *
     * @param  \App\Domain  $domain
     * @return void
     */
    public function restored(Domain $domain)
    {
        //
    }

    /**
     * Handle the domain "force deleted" event.
     *
     * @param  \App\Domain  $domain
     * @return void
     */
    public function forceDeleted(Domain $domain)
    {
        //
    }
}
