<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GymClass;
use App\Models\User;

class GymClassPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Se en liste over alle hold.
     * Eksempel: /admin/classes
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('GymClass', 'viewAny');
    }

    /**
     * Se detaljer for et specifikt hold.
     * Eksempel: /admin/classes/{id}
     */
    public function view(User $user, GymClass $class): bool
    {
        if ($user->tenant_id !== $class->tenant_id) {
            return false;
        }

        if ($user->hasPermission('GymClass', 'view')) {
            return true;
        }

        return $user->tenant_id && $user->tenant_id === $class->tenant_id;
    }

    /**
     * Tillad brugeren at booke sig på et hold.
     */
    public function book(User $user, GymClass $class): bool
    {
        return $this->view($user, $class);
    }

    /**
     * Opret et nyt hold.
     * Eksempel: /admin/classes/create
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('GymClass', 'create');
    }

    /**
     * Rediger oplysninger for et eksisterende hold.
     * Eksempel: /admin/classes/{id}/edit
     */
    public function update(User $user, GymClass $class): bool
    {
        if ($user->tenant_id !== $class->tenant_id) {
            return false;
        }

        if (! $user->hasPermission('GymClass', 'update')) {
            return false;
        }

        if ($user->role && in_array($user->role->slug, ['administrator', 'admin'])) {
            return true;
        }

        if ($user->role && $user->role->slug === 'trainer') {
            return $class->trainer_id === $user->id;
        }

        return false;
    }

    /**
     * Slet et hold fra systemet.
     */
    public function delete(User $user, GymClass $class): bool
    {
        if ($user->tenant_id !== $class->tenant_id) {
            return false;
        }

        if (! $user->hasPermission('GymClass', 'delete')) {
            return false;
        }

        if ($user->role && in_array($user->role->slug, ['administrator', 'admin'])) {
            return true;
        }

        if ($user->role && $user->role->slug === 'trainer') {
            return $class->trainer_id === $user->id;
        }

        return false;
    }
}
