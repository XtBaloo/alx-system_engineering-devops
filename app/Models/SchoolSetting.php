<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use App\Traits\Auditable;

class SchoolSetting extends Model
{
    use Auditable;

    protected $fillable = [
        'school_name', 'motto', 'address', 'phone', 'email', 'website',
        'principal_name', 'registration_number', 'logo_path',
        'school_signature_path', 'principal_signature_path', 'currency_symbol',
        'examination_max_score', 'ranking_method', 'report_card_footer_note',
        'current_academic_session_id', 'current_term_id',
    ];

    public function currentAcademicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'current_academic_session_id');
    }

    public function currentTerm(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'current_term_id');
    }

    public static function current(): self
    {
        return Cache::rememberForever('school_settings', function () {
            return static::firstOrCreate(['id' => 1], ['school_name' => 'Prime Foundation Academy']);
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('school_settings'));
    }
}
