<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TaskAssignmentService as singleton
        $this->app->singleton(\App\Services\TaskAssignmentService::class, function ($app) {
            return new \App\Services\TaskAssignmentService();
        });

        // Register NotificationService as singleton
        $this->app->singleton(\App\Services\NotificationService::class, function ($app) {
            return new \App\Services\NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
