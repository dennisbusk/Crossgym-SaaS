<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User as AuthUser;

class UserPolicy
{
    public function viewAny(AuthUser $user): bool
    {
        return $user->hasPermission('User', 'viewAny');
    }

    public function view(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'view');
    }

    public function create(AuthUser $user): bool
    {
        return $user->hasPermission('User', 'create');
    }

    public function update(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'update');
    }

    public function delete(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'delete');
    }

    public function view_admin_dashboard(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'view_admin_dashboard');
    }

    public function view_calendar(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'view_calendar');
    }

    public function impersonate(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'impersonate');
    }

    public function view_stripe_status(AuthUser $user, User $model): User|bool
    {
        return $user->hasPermission('User', 'view_stripe_status');
    }

    public function view_tenant_stats(AuthUser $user, User $model): User|bool
    {
        return $user->hasPermission('User', 'view_tenant_stats');
    }
}
