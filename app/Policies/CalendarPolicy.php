<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class CalendarPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Se kalenderoversigten over alle hold.
     * Eksempel: /admin/calendar
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Calendar', 'viewAny');
    }
}
