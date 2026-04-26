<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AICoachSettings;
use App\Models\User;

class AICoachSettingsPolicy
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
        return $user->hasPermission('AICoachSettings', 'viewAny');
    }

    public function view(User $user, AICoachSettings $settings): bool
    {
        if ($user->tenant_id !== $settings->tenant_id) {
            return false;
        }

        return $user->hasPermission('AICoachSettings', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('AICoachSettings', 'create');
    }

    public function update(User $user, AICoachSettings $settings): bool
    {
        if ($user->tenant_id !== $settings->tenant_id) {
            return false;
        }

        return $user->hasPermission('AICoachSettings', 'update');
    }

    public function delete(User $user, AICoachSettings $settings): bool
    {
        if ($user->tenant_id !== $settings->tenant_id) {
            return false;
        }

        return $user->hasPermission('AICoachSettings', 'delete');
    }
}
