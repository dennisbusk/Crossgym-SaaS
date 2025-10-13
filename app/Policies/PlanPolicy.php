<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
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
        return $user->hasPermission('Plan', 'viewAny');
    }

    public function view(User $user, Plan $plan): bool
    {
        return $user->hasPermission('Plan', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Plan', 'create');
    }

    public function update(User $user, Plan $plan): bool
    {
        return $user->hasPermission('Plan', 'update');
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $user->hasPermission('Plan', 'delete');
    }
}
