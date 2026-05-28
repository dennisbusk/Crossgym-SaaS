<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutLog;

class WorkoutLogPolicy
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
        return true;
    }

    public function view(User $user, WorkoutLog $workoutLog): bool
    {
        if ($user->tenant_id !== $workoutLog->tenant_id && $user->id !== $workoutLog->user_id) {
            return false;
        }

        return $user->id === $workoutLog->user_id || $user->hasPermission('WorkoutLog', 'viewAny');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkoutLog $workoutLog): bool
    {
        return $user->id === $workoutLog->user_id;
    }

    public function delete(User $user, WorkoutLog $workoutLog): bool
    {
        return $user->id === $workoutLog->user_id;
    }
}
