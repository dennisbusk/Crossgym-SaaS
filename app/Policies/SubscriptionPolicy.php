<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
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
        return $user->hasPermission('Subscription', 'viewAny');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('Subscription', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Subscription', 'create');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('Subscription', 'update');
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('Subscription', 'delete');
    }
}
