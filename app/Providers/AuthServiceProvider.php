<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\GymClass;
use App\Models\ClassType;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Policies\GymClassPolicy;
use App\Policies\ClassTypePolicy;
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

        // Global before hook to honor dynamic permissions
        Gate::before(function (User $user, string $ability, ?array $arguments = null) {
            if (! $arguments || count($arguments) === 0) {
                return null; // abilities without a model are not handled here
            }

            $subject = $arguments[0];
            $className = is_object($subject) ? class_basename($subject) : (is_string($subject) ? class_basename($subject) : null);
            if (! $className) {
                return null;
            }

            return $user->hasPermission($className, $ability) ? true : null;
        });
    }
}
