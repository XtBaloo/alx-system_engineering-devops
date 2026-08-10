<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_session_id' => AcademicSession::factory(),
            'name' => 'First Term',
            'start_date' => now()->subMonths(2)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'is_active' => false,
            'status' => 'open',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true])
            ->afterCreating(function (\App\Models\Term $term) {
                \App\Models\SchoolSetting::current()->update([
                    'current_term_id' => $term->id,
                    'current_academic_session_id' => $term->academic_session_id,
                ]);
            });
    }
}
