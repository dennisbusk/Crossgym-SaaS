<?php

declare( strict_types=1 );

namespace App\Providers;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ClassTypePolicy;
use App\Policies\GymClassPolicy;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(GymClass::class, GymClassPolicy::class);
        Gate::policy(ClassType::class, ClassTypePolicy::class);

        // Stripe admin actions
        Gate::define('refundPayment', function ( User $user, Payment $payment ) {
            return in_array($user->role?->name, [ 'Admin', 'SuperAdmin' ]);
        });
        Gate::define('updateSubscription', function ( User $user, Subscription $subscription ) {
            return in_array($user->role?->name, [ 'Admin', 'SuperAdmin' ]);
        });

        // Global before hook to honor dynamic permissions
        Gate::before(function ( User $user, string $ability, ?array $arguments = null ) {
            if ( !$arguments || count($arguments) === 0 ) {
                return null; // abilities without a model are not handled here
            }

            $subject   = $arguments[0];
            $className = is_object($subject) ? class_basename($subject) : ( is_string($subject) ? class_basename($subject) : null );
            if ( !$className ) {
                return null;
            }

            return $user->hasPermission($className, $ability) ? true : null;
        });
    }
}
