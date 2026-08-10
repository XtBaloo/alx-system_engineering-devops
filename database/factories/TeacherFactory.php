<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => 'PFA/TCH/'.fake()->unique()->numberBetween(1000, 9999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'employment_date' => now()->subYears(2)->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
