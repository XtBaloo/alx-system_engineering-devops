<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('manage-users') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('manage-users') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('manage-users');
    }

    public function manageRoles(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }
}
