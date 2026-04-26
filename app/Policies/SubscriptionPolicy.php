<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Se en liste over alle abonnementer.
     * Eksempel: /admin/subscriptions
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Subscription', 'viewAny');
    }

    /**
     * Se detaljer for et specifikt abonnement.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        if ($user->tenant_id !== $subscription->tenant_id && $user->id !== $subscription->user_id) {
            return false;
        }

        return $user->hasPermission('Subscription', 'view') || $user->id === $subscription->user_id;
    }

    /**
     * Opret et nyt abonnement manuelt.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('Subscription', 'create');
    }

    /**
     * Rediger et eksisterende abonnement.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        if ($user->tenant_id !== $subscription->tenant_id) {
            return false;
        }

        return $user->hasPermission('Subscription', 'update');
    }

    /**
     * Annuller eller slet et abonnement.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        if ($user->tenant_id !== $subscription->tenant_id) {
            return false;
        }

        return $user->hasPermission('Subscription', 'delete');
    }
}
