<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_number' => 'PFA/'.fake()->unique()->numberBetween(10000, 99999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'nationality' => 'Nigerian',
            'admission_date' => now()->subMonths(6)->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
