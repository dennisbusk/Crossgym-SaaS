<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StripeWebhookLog;
use App\Models\User;

class StripeWebhookLogPolicy
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
        return $user->hasPermission('StripeWebhookLog', 'viewAny');
    }

    public function view(User $user, StripeWebhookLog $stripeWebhookLog): bool
    {
        return $user->hasPermission('StripeWebhookLog', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('StripeWebhookLog', 'create');
    }

    public function update(User $user, StripeWebhookLog $stripeWebhookLog): bool
    {
        return $user->hasPermission('StripeWebhookLog', 'update');
    }

    public function delete(User $user, StripeWebhookLog $stripeWebhookLog): bool
    {
        return $user->hasPermission('StripeWebhookLog', 'delete');
    }
}
