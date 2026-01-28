<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\TableSessionObserver;
use Illuminate\Support\Facades\URL; 
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\TableSession;
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
        if (env('APP_ENV') !== 'local' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
        Order::observe(OrderObserver::class);
        TableSession::observe(TableSessionObserver::class);
    }
}
