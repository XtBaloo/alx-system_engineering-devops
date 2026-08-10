<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class StudentFee extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'student_id', 'fee_structure_id', 'academic_session_id', 'term_id',
        'amount_due', 'amount_paid', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->amount_due - (float) $this->amount_paid, 2);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }
}
