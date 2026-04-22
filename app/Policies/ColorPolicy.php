<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Color;
use App\Models\User;

class ColorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('Color', 'viewAny');
    }

    public function view(User $user, Color $color): bool
    {
        return $user->hasPermission('Color', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Color', 'create');
    }

    public function update(User $user, Color $color): bool
    {
        return $user->hasPermission('Color', 'update');
    }

    public function delete(User $user, Color $color): bool
    {
        return $user->hasPermission('Color', 'delete');
    }
}
