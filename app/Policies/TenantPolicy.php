<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Grant all abilities to SuperAdmin before other checks.
     */
    public function before(User $user, string $ability)
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return false;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return false;
    }
}
