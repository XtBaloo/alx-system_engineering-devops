<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-students') || $user->hasRole('teacher');
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->can('manage-students')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            $teacherId = $user->teacher?->id;

            return $teacherId && $student->currentClassArm
                ?->teacherAssignments()
                ->where('teacher_id', $teacherId)
                ->exists();
        }

        if ($user->hasRole('student')) {
            return $user->student?->id === $student->id;
        }

        if ($user->hasRole('parent')) {
            return $user->guardianProfile?->students()->where('students.id', $student->id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-students');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->can('manage-students');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('manage-students');
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->can('manage-students');
    }

    public function promote(User $user, Student $student): bool
    {
        return $user->can('manage-students');
    }
}
