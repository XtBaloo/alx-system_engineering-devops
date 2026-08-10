<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        if ($user->can('manage-documents') || $user->can('manage-students')) {
            return true;
        }

        if ($user->hasRole('student')) {
            return $document->student_id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            return $user->guardianProfile?->students()->where('students.id', $document->student_id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-documents') || $user->can('manage-students');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('manage-documents') || $user->can('manage-students');
    }
}
