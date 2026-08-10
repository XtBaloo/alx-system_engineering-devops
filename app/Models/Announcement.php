<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'message', 'author_id', 'target', 'school_class_id',
        'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForAudience($query, string $role, ?int $classArmSchoolClassId = null)
    {
        return $query->where(function ($q) use ($role, $classArmSchoolClassId) {
            $q->where('target', 'everyone')
                ->orWhere('target', $role);

            if ($classArmSchoolClassId) {
                $q->orWhere(function ($q2) use ($classArmSchoolClassId) {
                    $q2->where('target', 'class')->where('school_class_id', $classArmSchoolClassId);
                });
            }
        });
    }
}
