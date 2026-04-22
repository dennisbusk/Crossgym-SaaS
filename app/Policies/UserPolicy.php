<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User as AuthUser;

class UserPolicy
{
    /**
     * Se en liste over alle brugere i systemet.
     * Eksempel: /admin/users
     */
    public function viewAny(AuthUser $user): bool
    {
        return $user->hasPermission('User', 'viewAny');
    }

    /**
     * Se detaljer for en specifik bruger.
     * Eksempel: /admin/users/{id}
     */
    public function view(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'view');
    }

    /**
     * Opret en ny bruger i systemet.
     * Eksempel: /admin/users/create
     */
    public function create(AuthUser $user): bool
    {
        return $user->hasPermission('User', 'create');
    }

    /**
     * Rediger oplysninger for en eksisterende bruger.
     * Eksempel: /admin/users/{id}/edit
     */
    public function update(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'update');
    }

    /**
     * Slet en bruger fra systemet.
     */
    public function delete(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'delete');
    }

    /**
     * Log ind som en anden bruger for at fejlsøge eller hjælpe.
     */
    public function impersonate(AuthUser $user, User $model): bool
    {
        return $user->hasPermission('User', 'impersonate');
    }
}
