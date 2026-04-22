<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Dashboard', 'viewAny');
    }

    public function view_revenue(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_revenue');
    }

    public function view_bookings(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_bookings');
    }

    public function view_subscribers(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_subscribers');
    }

    public function view_upcoming_classes(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_upcoming_classes');
    }

    public function view_recent_activity(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_recent_activity');
    }

    public function view_charts(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_charts');
    }

    public function view_trainer_widget(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_trainer_widget');
    }

    public function view_export(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_export');
    }

    public function view_stripe_status(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('Dashboard', 'view_stripe_status');
    }
}
