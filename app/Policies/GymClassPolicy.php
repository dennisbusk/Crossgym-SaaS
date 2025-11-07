<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GymClass;
use App\Models\User;

class GymClassPolicy
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

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('GymClass', 'viewAny');
    }

    public function view(User $user, GymClass $class): bool
    {
        return $user->hasPermission('GymClass', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('GymClass', 'create');
    }

    public function update(User $user, GymClass $class): bool
    {
        return $user->hasPermission('GymClass', 'update');
    }

    public function delete(User $user, GymClass $class): bool
    {
        return $user->hasPermission('GymClass', 'delete');
    }
}
