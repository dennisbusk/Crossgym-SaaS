<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
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
        return $user->hasPermission('Role', 'update');
    }

    /**
     * Slet en rolle fra systemet.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('Role', 'delete');
    }
}
