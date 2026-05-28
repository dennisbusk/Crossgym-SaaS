<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Achievement', 'viewAny');
    }

    public function view(User $user, Achievement $achievement): bool
    {
        return $user->hasPermission('Achievement', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Achievement', 'create');
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return $user->hasPermission('Achievement', 'update');
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->hasPermission('Achievement', 'delete');
    }
}
