<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Se en liste over alle roller og deres rettigheder.
     * Eksempel: /admin/roles
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Role', 'viewAny');
    }

    /**
     * Se detaljer for en specifik rolle.
     * Eksempel: /admin/roles/{id}
     */
    public function view(User $user, Role $role): bool
    {
        if ($role->tenant_id !== null && $user->tenant_id !== $role->tenant_id) {
            return false;
        }

        return $user->hasPermission('Role', 'view');
    }

    /**
     * Opret en ny rolle.
     * Eksempel: /admin/roles/create
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('Role', 'create');
    }

    /**
     * Rediger en eksisterende rolle og dens tilladelser.
     * Eksempel: /admin/roles/{id}/edit
     */
    public function update(User $user, Role $role): bool
    {
        if ($role->tenant_id !== null && $user->tenant_id !== $role->tenant_id) {
            return false;
        }

        return $user->hasPermission('Role', 'update');
    }

    /**
     * Slet en rolle fra systemet.
     */
    public function delete(User $user, Role $role): bool
    {
        if ($role->tenant_id !== null && $user->tenant_id !== $role->tenant_id) {
            return false;
        }

        return $user->hasPermission('Role', 'delete');
    }
}
