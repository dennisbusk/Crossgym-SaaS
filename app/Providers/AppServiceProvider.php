<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\BookingCreated;
use App\Events\PaymentFailed;
use App\Events\RetentionTriggered;
use App\Events\SubscriptionCreated;
use App\Events\UserRegistered;
use App\Listeners\AchievementListener;
use App\Listeners\SendDynamicEmail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
        if (app()->environment('local')) {
            // Keep permissions in sync with policy methods during local development
            try {
                Artisan::callSilently('permissions:sync');
            } catch (Throwable $e) {
                // Ignore during early boot/migrations not yet run
            }
        }

        $this->ensureSuperAdminExists();

        Event::listen(
            [
                UserRegistered::class,
                \Illuminate\Auth\Events\Registered::class,
                SubscriptionCreated::class,
                PaymentFailed::class,
                BookingCreated::class,
                RetentionTriggered::class,
            ],
            SendDynamicEmail::class
        );

        Event::subscribe(AchievementListener::class);
    }

    /**
     * Ensure a superadmin user and role exist in the system.
     */
    protected function ensureSuperAdminExists(): void
    {
        // Using cache to avoid DB hits on every request
        if (Cache::has('superadmin_ensured')) {
            return;
        }

        try {
            DB::transaction(function () {
                $superAdminRole = Role::withoutGlobalScopes()->firstOrCreate(['slug' => Str::slug('Superadmin')], [
                    'name' => 'Superadmin',
                    'slug' => Str::slug('Superadmin'),
                    'tenant_id' => null,
                ]);

                User::withoutGlobalScopes()->firstOrCreate([
                    'email' => 'dennis@db-development.dk',
                ], [
                    'name' => 'Super Admin User',
                    'password' => Hash::make('made42Mice'),
                    'role_id' => $superAdminRole->id,
                    'tenant_id' => null,
                ]);
            });

            Cache::put('superadmin_ensured', true, now()->addHour());
        } catch (Throwable $e) {
            // Silence errors during early boot or when tables don't exist
        }
    }
}
