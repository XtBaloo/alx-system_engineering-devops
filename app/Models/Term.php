<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_session_id', 'name', 'start_date', 'end_date',
        'is_active', 'status', 'closed_at', 'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function current(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
