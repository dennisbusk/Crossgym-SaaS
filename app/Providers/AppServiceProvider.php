<?php

declare( strict_types=1 );

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ( app()->environment('local') ) {
            // Keep permissions in sync with policy methods during local development
            try {
                Artisan::callSilently('permissions:sync');
            } catch ( Throwable $e ) {
                // Ignore during early boot/migrations not yet run
            }
        }
    }
}
