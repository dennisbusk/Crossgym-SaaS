<?php

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;

class EmailTemplatePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role && $user->role->slug === 'superadmin') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('EmailTemplate', 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmailTemplate $emailTemplate): bool
    {
        if ($emailTemplate->tenant_id !== null && $user->tenant_id !== $emailTemplate->tenant_id) {
            return false;
        }

        return $user->hasPermission('EmailTemplate', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('EmailTemplate', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmailTemplate $emailTemplate): bool
    {
        if ($emailTemplate->tenant_id !== null && $user->tenant_id !== $emailTemplate->tenant_id) {
            return false;
        }

        return $user->hasPermission('EmailTemplate', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmailTemplate $emailTemplate): bool
    {
        if ($emailTemplate->tenant_id !== null && $user->tenant_id !== $emailTemplate->tenant_id) {
            return false;
        }

        return $user->hasPermission('EmailTemplate', 'delete');
    }
}
