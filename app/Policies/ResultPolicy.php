<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-results') || $user->hasAnyRole(['student', 'parent']);
    }

    public function view(User $user, Result $result): bool
    {
        if ($user->can('view-results') && $this->teachesOrManages($user, $result)) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $result->student_id === $user->student?->id && $result->status === 'published';
        }

        if ($user->hasRole('parent')) {
            return $result->status === 'published'
                && ($user->guardianProfile?->students()->where('students.id', $result->student_id)->exists() ?? false);
        }

        return false;
    }

    public function enterScores(User $user, Result $result): bool
    {
        if (! $user->can('enter-scores')) {
            return false;
        }

        return $this->teachesOrManages($user, $result);
    }

    public function submit(User $user, Result $result): bool
    {
        return $this->enterScores($user, $result);
    }

    public function review(User $user, Result $result): bool
    {
        return $user->can('review-results');
    }

    public function approve(User $user, Result $result): bool
    {
        return $user->can('approve-results');
    }

    public function publish(User $user, Result $result): bool
    {
        return $user->can('publish-results');
    }

    protected function teachesOrManages(User $user, Result $result): bool
    {
        if ($user->can('manage-students')) {
            return true;
        }

        $teacherId = $user->teacher?->id;

        if (! $teacherId) {
            return false;
        }

        return \App\Models\TeacherAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_arm_id', $result->class_arm_id)
            ->where('subject_id', $result->subject_id)
            ->where('academic_session_id', $result->academic_session_id)
            ->exists();
    }
}
