<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class RetentionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Retention', 'viewAny');
    }

    public function sendRecallEmail(User $user): bool
    {
        return $user->hasPermission('Retention', 'sendRecallEmail');
    }
}
