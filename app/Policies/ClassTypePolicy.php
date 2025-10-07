<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassType;
use App\Models\User;

class ClassTypePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Admins can do anything
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, ClassType $classType): bool
    {
        return $user->tenant_id === $classType->tenant_id;
    }

    public function create(User $user): bool
    {
        // Trainers can manage class types for their tenant
        return $user->role && in_array($user->role->name, ['Trainer'], true);
    }

    public function update(User $user, ClassType $classType): bool
    {
        return $user->tenant_id === $classType->tenant_id
            && ($user->role && in_array($user->role->name, ['Trainer'], true));
    }

    public function delete(User $user, ClassType $classType): bool
    {
        return $user->tenant_id === $classType->tenant_id
            && ($user->role && in_array($user->role->name, ['Trainer'], true));
    }
}
