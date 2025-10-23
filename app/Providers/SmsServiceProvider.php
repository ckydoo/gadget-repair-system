<?php

namespace App\Providers;

use App\Services\AfricasTalkingSmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AfricasTalkingSmsService::class, function ($app) {
            return new AfricasTalkingSmsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
