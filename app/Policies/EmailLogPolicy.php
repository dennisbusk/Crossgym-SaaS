<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailLog;
use App\Models\User;

class EmailLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('EmailLog', 'viewAny');
    }

    public function view(User $user, EmailLog $emailLog): bool
    {
        return $user->hasPermission('EmailLog', 'view');
    }

    public function delete(User $user, EmailLog $emailLog): bool
    {
        return $user->hasPermission('EmailLog', 'delete');
    }
}
