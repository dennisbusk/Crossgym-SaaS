<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class SuperAdminPolicy
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
        // Typically only superadmins should have this permission
        return $user->hasPermission('SuperAdmin', 'viewAny');
    }

    public function viewDashboard(User $user): bool
    {
        return $user->hasPermission('SuperAdmin', 'viewDashboard');
    }

    public function viewSettings(User $user): bool
    {
        return $user->hasPermission('SuperAdmin', 'viewSettings');
    }
}
