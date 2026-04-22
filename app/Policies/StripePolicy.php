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
        // Do not treat user as admin while impersonating
        if (method_exists($user, 'isImpersonated') && $user->isImpersonated()) {
            return false;
        }
        $roleName = is_array($user->role?->getAttributes()['name'] ?? null)
            ? strtolower($user->role?->getTranslations('name')['en'] ?? $user->role?->getTranslations('name')['da'] ?? '')
            : strtolower((string) ($user->role?->name ?? ''));
        $roleSlug = strtolower($user->role?->slug ?? '');

        return $roleName === 'admin' || $roleSlug === 'admin';
    }
}
