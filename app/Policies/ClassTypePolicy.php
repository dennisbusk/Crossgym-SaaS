<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassType;
use App\Models\User;

class ClassTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('ClassType', 'viewAny');
    }

    public function view(User $user, ClassType $classType): bool
    {
        return $user->hasPermission('ClassType', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('ClassType', 'create');
    }

    public function update(User $user, ClassType $classType): bool
    {
        return $user->hasPermission('ClassType', 'update');
    }

    public function delete(User $user, ClassType $classType): bool
    {
        return $user->hasPermission('ClassType', 'delete');
    }
}
