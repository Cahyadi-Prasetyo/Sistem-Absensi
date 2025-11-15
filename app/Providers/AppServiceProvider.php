<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            \App\Repositories\AbsensiRepositoryInterface::class,
            \App\Repositories\AbsensiRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix untuk MySQL versi lama di WAMP
        // Set default string length untuk index
        Schema::defaultStringLength(191);

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AbsensiCreated::class,
            \App\Listeners\LogAbsensiEvent::class,
        );
    }
}
