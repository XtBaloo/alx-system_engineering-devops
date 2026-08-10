<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-teachers');
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->can('manage-teachers') || $user->teacher?->id === $teacher->id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-teachers');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->can('manage-teachers');
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can('manage-teachers');
    }
}
