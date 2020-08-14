<?php

namespace App\Observers;

use App\Key;

class KeyObserver
{
    /**
     * Handle the key "creating" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function creating(Key $key)
    {
        if ($key->sysuser) $key->server_id = $key->sysuser->server_id;
    }

    /**
     * Handle the key "created" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function created(Key $key)
    {
        if (!$key->isReady()) {
            $key->provision();
        }
    }

    /**
     * Handle the key "updated" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function updated(Key $key)
    {
        if ($key->wasChanged(['name'])) {
            $key->provision();
        }
    }

    /**
     * Handle the key "deleted" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function deleted(Key $key)
    {
        if (!$key->isForceDeleting()) {
            $key->deleteFromServer();
        }
    }

    /**
     * Handle the key "restored" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function restored(Key $key)
    {
        //
    }

    /**
     * Handle the key "force deleted" event.
     *
     * @param  \App\Key  $key
     * @return void
     */
    public function forceDeleted(Key $key)
    {
        //
    }
}
