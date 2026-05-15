<?php

namespace App\Providers;

use App\Models\Channel;
use App\Models\Server;
use App\Observers\ChannelObserver;
use App\Observers\ServerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Server::observe(ServerObserver::class);
        Channel::observe(ChannelObserver::class);
    }
}
