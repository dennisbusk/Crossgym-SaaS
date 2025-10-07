<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GymClass;
use App\Models\User;

class GymClassPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Admins can do anything
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null; // any authenticated tenant user can list
    }

    public function view(User $user, GymClass $class): bool
    {
        return $user->tenant_id === $class->tenant_id;
    }

    public function create(User $user): bool
    {
        // Trainers and Admin (handled in before) can create
        return $user->role && $user->role->name === 'Trainer';
    }

    public function update(User $user, GymClass $class): bool
    {
        // Only the trainer who created the class (or Admin via before)
        return $user->id === $class->trainer_id;
    }

    public function delete(User $user, GymClass $class): bool
    {
        // Only the trainer who created the class (or Admin via before)
        return $user->id === $class->trainer_id;
    }
}
