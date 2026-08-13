<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected const CONTAINER_KEY = 'school-settings.current';

    public function currentAcademicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'current_academic_session_id');
    }

    public function currentTerm(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'current_term_id');
    }

    /**
     * Memoized per-request (bound to the container, not a static property),
     * so it never leaks stale data across HTTP requests or test cases.
     */
    public static function current(): self
    {
        if (app()->bound(self::CONTAINER_KEY)) {
            return app(self::CONTAINER_KEY);
        }

        $settings = static::firstOrCreate(['id' => 1], ['school_name' => 'Prime Foundation Academy']);

        if ($settings->wasRecentlyCreated) {
            $settings->refresh();
        }

        app()->instance(self::CONTAINER_KEY, $settings);

        return $settings;
    }

    protected static function booted(): void
    {
        static::saved(function (self $settings) {
            app()->instance(self::CONTAINER_KEY, $settings);
        });
    }
}
