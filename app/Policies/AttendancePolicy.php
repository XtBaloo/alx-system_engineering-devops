<?php

namespace App\Policies;

use App\Models\ClassArm;
use App\Models\User;

class AttendancePolicy
{
    public function take(User $user, ClassArm $classArm): bool
    {
        if (! $user->can('take-attendance')) {
            return false;
        }

        if ($user->can('manage-students')) {
            return true;
        }

        $teacherId = $user->teacher?->id;

        return $teacherId && (
            $classArm->class_teacher_id === $teacherId
            || $classArm->teacherAssignments()->where('teacher_id', $teacherId)->exists()
        );
    }

    public function viewStudentAttendance(User $user, \App\Models\Student $student): bool
    {
        if ($user->can('view-attendance')) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $student->id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            return $user->guardianProfile?->students()->where('students.id', $student->id)->exists() ?? false;
        }

        return false;
    }
}
