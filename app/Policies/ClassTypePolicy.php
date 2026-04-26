<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassType;
use App\Models\User;

class ClassTypePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Se en liste over alle holdtyper.
     * Eksempel: /admin/class-types
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('ClassType', 'viewAny');
    }

    /**
     * Se detaljer for en specifik holdtype.
     */
    public function view(User $user, ClassType $classType): bool
    {
        if ($user->tenant_id !== $classType->tenant_id) {
            return false;
        }

        return $user->hasPermission('ClassType', 'view');
    }

    /**
     * Opret en ny holdtype.
     * Eksempel: /admin/class-types/create
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('ClassType', 'create');
    }

    /**
     * Rediger en eksisterende holdtype.
     * Eksempel: /admin/class-types/{id}/edit
     */
    public function update(User $user, ClassType $classType): bool
    {
        if ($user->tenant_id !== $classType->tenant_id) {
            return false;
        }

        return $user->hasPermission('ClassType', 'update');
    }

    /**
     * Slet en holdtype fra systemet.
     */
    public function delete(User $user, ClassType $classType): bool
    {
        if ($user->tenant_id !== $classType->tenant_id) {
            return false;
        }

        return $user->hasPermission('ClassType', 'delete');
    }
}
