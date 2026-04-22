<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class CalendarPolicy
{
    /**
     * Se kalenderoversigten over alle hold.
     * Eksempel: /admin/calendar
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Calendar', 'viewAny');
    }
}
