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

    /**
     * Se en liste over alle medlemskabstyper (planer).
     * Eksempel: /admin/plans
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Plan', 'viewAny');
    }

    /**
     * Se detaljer for en specifik medlemskabstype.
     */
    public function view(User $user, Plan $plan): bool
    {
        if ($plan->tenant_id !== null && $user->tenant_id !== $plan->tenant_id) {
            return false;
        }

        return $user->hasPermission('Plan', 'view');
    }

    /**
     * Opret en ny medlemskabstype.
     * Eksempel: /admin/plans/create
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('Plan', 'create');
    }

    /**
     * Rediger en eksisterende medlemskabstype.
     * Eksempel: /admin/plans/{id}/edit
     */
    public function update(User $user, Plan $plan): bool
    {
        if ($plan->tenant_id !== null && $user->tenant_id !== $plan->tenant_id) {
            return false;
        }

        return $user->hasPermission('Plan', 'update');
    }

    /**
     * Slet en medlemskabstype, hvis der ikke er aktive abonnementer på den.
     */
    public function delete(User $user, Plan $plan): bool
    {
        if ($plan->tenant_id !== null && $user->tenant_id !== $plan->tenant_id) {
            return false;
        }

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
