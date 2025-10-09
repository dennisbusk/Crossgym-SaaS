<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Grant all abilities to SuperAdmin before other checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Tenant', 'viewAny');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Tenant', 'create');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'update');
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'delete');
    }
}
