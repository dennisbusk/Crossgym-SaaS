<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    /**
     * Giver adgang til at se kontrolpanelet.
     * Eksempel: /admin/dashboard
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Dashboard', 'viewAny');
    }

    /**
     * Giver adgang til at se omsætningstal på kontrolpanelet.
     */
    public function view_revenue(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_revenue');
    }

    /**
     * Giver adgang til at se antal bookinger på kontrolpanelet.
     */
    public function view_bookings(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_bookings');
    }

    /**
     * Giver adgang til at se antal abonnenter på kontrolpanelet.
     */
    public function view_subscribers(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_subscribers');
    }

    /**
     * Giver adgang til at se kommende hold på kontrolpanelet.
     */
    public function view_upcoming_classes(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_upcoming_classes');
    }

    /**
     * Giver adgang til at se seneste aktiviteter på kontrolpanelet.
     */
    public function view_recent_activity(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_recent_activity');
    }

    /**
     * Giver adgang til at se grafer og statistikker på kontrolpanelet.
     */
    public function view_charts(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_charts');
    }

    /**
     * Giver adgang til at se træner-widget på kontrolpanelet.
     */
    public function view_trainer_widget(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_trainer_widget');
    }

    /**
     * Giver adgang til at eksportere data fra kontrolpanelet.
     */
    public function view_export(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_export');
    }

    /**
     * Giver adgang til at se Stripe-status på kontrolpanelet.
     */
    public function view_stripe_status(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_stripe_status');
    }
}
