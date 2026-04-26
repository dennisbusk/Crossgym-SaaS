<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailLog;
use App\Models\User;

class EmailLogPolicy
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
        return $user->hasPermission('EmailLog', 'viewAny');
    }

    public function view(User $user, EmailLog $emailLog): bool
    {
        if ($user->tenant_id !== $emailLog->tenant_id) {
            return false;
        }

        return $user->hasPermission('EmailLog', 'view');
    }

    public function delete(User $user, EmailLog $emailLog): bool
    {
        if ($user->tenant_id !== $emailLog->tenant_id) {
            return false;
        }

        return $user->hasPermission('EmailLog', 'delete');
    }
}
