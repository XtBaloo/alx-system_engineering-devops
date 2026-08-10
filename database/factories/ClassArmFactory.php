<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassArmFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(),
            'name' => fake()->randomElement(['A', 'B', 'C']),
        ];
    }
}
