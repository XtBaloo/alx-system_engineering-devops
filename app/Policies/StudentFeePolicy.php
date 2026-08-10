<?php

namespace App\Policies;

use App\Models\StudentFee;
use App\Models\User;

class StudentFeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-fees') || $user->hasAnyRole(['student', 'parent']);
    }

    public function view(User $user, StudentFee $studentFee): bool
    {
        if ($user->can('manage-fees')) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $studentFee->student_id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            return $user->guardianProfile?->students()->where('students.id', $studentFee->student_id)->exists() ?? false;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->can('manage-fees');
    }

    public function recordPayment(User $user): bool
    {
        return $user->can('manage-payments');
    }
}
