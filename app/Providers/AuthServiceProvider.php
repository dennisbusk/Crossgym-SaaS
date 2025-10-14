<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\GymClass;
use App\Models\ClassType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\StripeWebhookLog;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Policies\GymClassPolicy;
use App\Policies\ClassTypePolicy;
use App\Policies\PlanPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\StripeWebhookLogPolicy;
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
        $this->registerPolicies();

        // Global before hook to honor dynamic permissions
        Gate::before(function (User $user, string $ability, ?array $arguments = null) {

            if($this->checkForIsSuperAdmin($user)) return true;

            if (! $arguments || count($arguments) === 0) return null; // abilities without a model are not handled here

            $subject = $arguments[0];
            $className = is_object($subject) ? class_basename($subject) : (is_string($subject) ? class_basename($subject) : null);
            if (! $className) {
                return null;
            }

            return $user->hasPermission($className, $ability) ? true : null;
        });
    }
    private function registerPolicies(): void {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(GymClass::class, GymClassPolicy::class);
        Gate::policy(ClassType::class, ClassTypePolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(StripeWebhookLog::class, StripeWebhookLogPolicy::class);
}
private function checkForIsSuperAdmin($user): bool {
    static $isSuperadmin = [];
    if (!array_key_exists($user->id, $isSuperadmin)) {
        $isSuperadmin[$user->id] = $user->role()
                                        ->withoutGlobalScopes()
                                        ->where('slug', 'superadmin')
                                        ->exists();
    }
    return ($isSuperadmin[$user->id]);
}
}
