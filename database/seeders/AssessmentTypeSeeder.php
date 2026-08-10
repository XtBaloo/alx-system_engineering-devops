<?php

namespace Database\Seeders;

use App\Models\AssessmentType;
use Illuminate\Database\Seeder;

class AssessmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'CA 1', 'code' => 'CA1', 'max_score' => 10, 'order' => 1],
            ['name' => 'CA 2', 'code' => 'CA2', 'max_score' => 10, 'order' => 2],
            ['name' => 'Assignment', 'code' => 'ASG', 'max_score' => 10, 'order' => 3],
            ['name' => 'Test', 'code' => 'TEST', 'max_score' => 10, 'order' => 4],
        ];

        foreach ($types as $type) {
            AssessmentType::updateOrCreate(['code' => $type['code']], $type + ['is_active' => true]);
        }
    }
}
