<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
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
        // Require permission
        if (! $user->hasPermission('Plan', 'delete')) {
            return false;
        }

        // Business rule: a plan can only be deleted if there are no active/trialing subscriptions on it
        return ! $plan->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();
    }
}
