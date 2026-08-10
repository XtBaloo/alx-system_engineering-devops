<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'JSS '.fake()->unique()->numberBetween(1, 3),
            'level' => 'junior_secondary',
            'order' => fake()->numberBetween(1, 20),
        ];
    }
}
