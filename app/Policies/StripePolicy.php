<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class StripePolicy
{
    public function updateSubscription(User $user, Subscription $subscription): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('Stripe', 'updateSubscription');
    }

    public function refundPayment(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('Stripe', 'refundPayment');
    }

    protected function isAdmin(User $user): bool
    {
        $role = strtolower($user->role?->name ?? '');
        return in_array($role, ['admin', 'superadmin']);
    }
}
