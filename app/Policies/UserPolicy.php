<?php

namespace App\Policies;

use App\Models\User as AuthUser;
use App\Models\User;

class UserPolicy
{
    /**
     * Grant all abilities to SuperAdmin before other checks.
     */
    public function before(AuthUser $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

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
}
