<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
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
        return $user->hasPermission('Payment', 'viewAny');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->tenant_id !== $payment->tenant_id && $user->id !== $payment->user_id) {
            return false;
        }

        return $user->hasPermission('Payment', 'view') || $user->id === $payment->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Payment', 'create');
    }

    public function update(User $user, Payment $payment): bool
    {
        if ($user->tenant_id !== $payment->tenant_id) {
            return false;
        }

        return $user->hasPermission('Payment', 'update');
    }

    public function delete(User $user, Payment $payment): bool
    {
        if ($user->tenant_id !== $payment->tenant_id) {
            return false;
        }

        return $user->hasPermission('Payment', 'delete');
    }
}
