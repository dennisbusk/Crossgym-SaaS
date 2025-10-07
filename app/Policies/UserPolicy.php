<?php

namespace App\Policies;

use App\Models\User as AuthUser;
use App\Models\User;

class UserPolicy
{
    /**
     * Grant all abilities to SuperAdmin before other checks.
     */
    public function before(AuthUser $user, string $ability)
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return false;
    }

    public function viewAny(AuthUser $user): bool
    {
        return false;
    }

    public function view(AuthUser $user, User $model): bool
    {
        return false;
    }

    public function create(AuthUser $user): bool
    {
        return false;
    }

    public function update(AuthUser $user, User $model): bool
    {
        return false;
    }

    public function delete(AuthUser $user, User $model): bool
    {
        return false;
    }
}
