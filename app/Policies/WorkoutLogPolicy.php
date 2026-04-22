<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\WorkoutLog;
use App\Models\User;

class WorkoutLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkoutLog $workoutLog): bool
    {
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
