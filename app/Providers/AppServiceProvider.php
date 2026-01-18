<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {   
        // Tell Laravel to use /tmp for its internal storage needs
        if (config('app.env') === 'production') {
            $this->app->useStoragePath('/tmp');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for "Inertia/Vite assets not loading" (Mixed Content Error)
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // Ensure your views are compiled in a writable directory
        config(['view.compiled' => '/tmp/views']);

        // Ensure the directory exists if it's the first time running
        if (!is_dir('/tmp/views')) {
            mkdir('/tmp/views', 0755, true);
        }
    }
}
