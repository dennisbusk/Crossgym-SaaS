<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
//    public function before(User $user, string $ability): ?bool
//    {
//        // When impersonating, do not grant superadmin bypass
//        if (method_exists($user, 'isImpersonated') && $user->isImpersonated()) {
//            return null;
//        }
//        if ($user->role && $user->role->slug === 'superadmin') {
//            return true;
//        }
//
//        return null;
//    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Role', 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('Role', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('Role', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('Role', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('Role', 'delete');
    }
}
