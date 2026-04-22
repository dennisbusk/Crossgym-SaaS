<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Se en liste over alle centre (tenants).
     * Eksempel: /admin/tenants
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Tenant', 'viewAny');
    }

    /**
     * Se detaljer for et specifikt center.
     */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'view');
    }

    /**
     * Opret et nyt center i systemet.
     * Eksempel: /admin/tenants/create
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('Tenant', 'create');
    }

    /**
     * Rediger oplysninger for et eksisterende center.
     * Eksempel: /admin/tenants/{id}/edit
     */
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'update');
    }

    /**
     * Slet et center fra systemet.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->hasPermission('Tenant', 'delete');
    }
}
