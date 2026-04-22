<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AICoachSettings;
use App\Models\User;

class AICoachSettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('AICoachSettings', 'viewAny');
    }

    public function view(User $user, AICoachSettings $settings): bool
    {
        return $user->hasPermission('AICoachSettings', 'view') && $user->tenant_id === $settings->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('AICoachSettings', 'create');
    }

    public function update(User $user, AICoachSettings $settings): bool
    {
        return $user->hasPermission('AICoachSettings', 'update') && $user->tenant_id === $settings->tenant_id;
    }

    public function delete(User $user, AICoachSettings $settings): bool
    {
        return $user->hasPermission('AICoachSettings', 'delete') && $user->tenant_id === $settings->tenant_id;
    }
}
