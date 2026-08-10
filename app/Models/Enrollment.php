<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'academic_session_id', 'school_class_id', 'class_arm_id',
        'status', 'promoted_from_enrollment_id', 'promoted_by', 'promoted_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'promoted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function classArm(): BelongsTo
    {
        return $this->belongsTo(ClassArm::class);
    }

    public function promotedFrom(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'promoted_from_enrollment_id');
    }

    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
