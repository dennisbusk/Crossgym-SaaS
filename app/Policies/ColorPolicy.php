<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Color;
use App\Models\User;

class ColorPolicy
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
        return $user->hasPermission('Color', 'viewAny');
    }

    public function view(User $user, Color $color): bool
    {
        if ($color->tenant_id !== null && $user->tenant_id !== $color->tenant_id) {
            return false;
        }

        return $user->hasPermission('Color', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Color', 'create');
    }

    public function update(User $user, Color $color): bool
    {
        if ($color->tenant_id !== null && $user->tenant_id !== $color->tenant_id) {
            return false;
        }

        return $user->hasPermission('Color', 'update');
    }

    public function delete(User $user, Color $color): bool
    {
        if ($color->tenant_id !== null && $user->tenant_id !== $color->tenant_id) {
            return false;
        }

        return $user->hasPermission('Color', 'delete');
    }
}
