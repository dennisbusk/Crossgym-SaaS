<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Role;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if ($user->role_id) {
            $role = Role::find($user->role_id);
            if ($role) {
                $user->syncPermissionsFromRole($role);
            }
        }
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('role_id') && $user->role_id) {
            $role = Role::find($user->role_id);
            if ($role) {
                $user->syncPermissionsFromRole($role);
            }
        }
    }
}
