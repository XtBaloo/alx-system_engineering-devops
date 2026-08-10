<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Student extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'admission_number', 'first_name', 'middle_name', 'last_name',
        'gender', 'date_of_birth', 'photo_path', 'nationality', 'state_of_origin',
        'lga', 'address', 'phone', 'email', 'admission_date', 'previous_school',
        'current_class_arm_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentClassArm(): BelongsTo
    {
        return $this->belongsTo(ClassArm::class, 'current_class_arm_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('relationship', 'is_primary')
            ->withTimestamps();
    }

    public function primaryGuardian(): ?Guardian
    {
        return $this->guardians()->wherePivot('is_primary', true)->first()
            ?? $this->guardians()->first();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects')
            ->withPivot('academic_session_id')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function assessmentScores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function examinationScores(): HasMany
    {
        return $this->hasMany(ExaminationScore::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}", ' ');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
