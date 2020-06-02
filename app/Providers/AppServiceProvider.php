<?php

namespace App\Providers;

use App\Key;
use App\Site;
use App\Domain;
use App\Server;
use App\Sysuser;
use App\Database;
use App\Observers\KeyObserver;
use App\Observers\SiteObserver;
use App\Observers\DomainObserver;
use App\Observers\ServerObserver;
use App\Observers\SysuserObserver;
use App\Observers\DatabaseObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        # Model observers
        Server::observe(ServerObserver::class);
        Key::observe(KeyObserver::class);
        Sysuser::observe(SysuserObserver::class);
        Site::observe(SiteObserver::class);
        Domain::observe(DomainObserver::class);
        Database::observe(DatabaseObserver::class);
    }
}
