<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'category' => 'Core',
            'is_compulsory' => true,
            'status' => 'active',
        ];
    }
}
