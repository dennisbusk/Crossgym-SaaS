<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    /**
     * Se en liste over alle øvelser i biblioteket.
     * Eksempel: /admin/exercises
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tilføj en ny øvelse til biblioteket.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Rediger en eksisterende øvelse.
     */
    public function update(User $user, Exercise $exercise): bool
    {
        return $user->hasPermission('Exercise', 'update');
    }

    /**
     * Slet en øvelse fra biblioteket.
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->hasPermission('Exercise', 'delete');
    }
}
