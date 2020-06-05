<?php

namespace App\Observers;

use App\Database;
use Illuminate\Support\Str;

class DatabaseObserver
{
    /**
     * Handle the database "creating" event.
     *
     * @param  \App\Sysuser  $key
     * @return void
     */
    public function creating(Database $database)
    {
        if (!empty($database->site)) $database->sysuser_id = $database->site->sysuser_id;

        $unique = true;
        while ($unique) {
            $database->name = $database->sysuser->name . '_db' . ucfirst(Str::random(4));
            $unique = $database->where('name', $database->name)->count();
        }

        $unique = true;
        while ($unique) {
            $database->user = $database->sysuser->name . '_u' . ucfirst(Str::random(4));
            $unique = $database->where('user', $database->user)->count();
        }

        $database->password = Str::random(12);
    }

    /**
     * Handle the database "created" event.
     *
     * @param  \App\Database  $database
     * @return void
     */
    public function created(Database $database)
    {
        if (!$database->isReady()) {
            $database->provision();
        }
    }

    /**
     * Handle the database "updated" event.
     *
     * @param  \App\Database  $database
     * @return void
     */
    public function updated(Database $database)
    {
        if ($database->wasChanged(['password'])) {
            $database->provision();
        }
    }

    /**
     * Handle the database "deleted" event.
     *
     * @param  \App\Database  $database
     * @return void
     */
    public function deleted(Database $database)
    {
        if (!$database->isDestroyed()) {
            $database->deleteFromServer();
        }
    }

    /**
     * Handle the database "restored" event.
     *
     * @param  \App\Database  $database
     * @return void
     */
    public function restored(Database $database)
    {
        //
    }

    /**
     * Handle the database "force deleted" event.
     *
     * @param  \App\Database  $database
     * @return void
     */
    public function forceDeleted(Database $database)
    {
        //
    }
}
