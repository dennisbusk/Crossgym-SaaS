<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AICoachSettings;
use App\Models\ClassType;
use App\Models\Dashboard;
use App\Models\GymClass;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\StripeWebhookLog;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\AICoachSettingsPolicy;
use App\Policies\ClassTypePolicy;
use App\Policies\DashboardPolicy;
use App\Policies\GymClassPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PlanPolicy;
use App\Policies\RolePolicy;
use App\Policies\StripeWebhookLogPolicy;
use App\Policies\SubscriptionPolicy;
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
        $this->registerPolicies();

        Gate::before(function (User $user): ?bool {
            // During impersonation, do NOT grant superadmin bypass or any
            // elevation based on the impersonator. Checks must reflect the
            // currently authenticated (impersonated) user only.
            if (method_exists($user, 'isImpersonated') && $user->isImpersonated()) {
                return null;
            }

            return $this->checkForIsSuperAdmin($user) ? true : null;
        });
    }

    private function registerPolicies(): void
    {
        Gate::policy(AICoachSettings::class, AICoachSettingsPolicy::class);
        Gate::policy(Dashboard::class, DashboardPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(GymClass::class, GymClassPolicy::class);
        Gate::policy(ClassType::class, ClassTypePolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(StripeWebhookLog::class, StripeWebhookLogPolicy::class);
    }

    private function checkForIsSuperAdmin($user): bool
    {
        return $user->role()
            ->withoutGlobalScopes()
            ->where('slug', 'superadmin')
            ->exists();
    }
}
