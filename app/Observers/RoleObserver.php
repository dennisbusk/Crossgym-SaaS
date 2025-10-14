<?php

namespace App\Observers;

use App\Models\Role;
use Str;

class RoleObserver {

    public function creating( Role $role ): void {
        $role->slug = $role->slug ?? Str::slug($role->name);
    }
}
