<?php

declare( strict_types=1 );

namespace App\Policies;

use App\Models\User;

class SubscriptionsPolicy {

    public function manage( User $user ): bool {
        $role = $user->role?->name;

        return in_array($role, [ 'Admin', 'SuperAdmin' ]);
    }
}
