<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicSessionFactory extends Factory
{
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2020, 2035);

        return [
            'name' => "{$year}/".($year + 1),
            'start_date' => "{$year}-09-01",
            'end_date' => ($year + 1).'-07-31',
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true])
            ->afterCreating(function (\App\Models\AcademicSession $session) {
                \App\Models\SchoolSetting::current()->update(['current_academic_session_id' => $session->id]);
            });
    }
}
