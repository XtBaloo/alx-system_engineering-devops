<?php

namespace Database\Seeders;

use App\Models\GradingScale;
use Illuminate\Database\Seeder;

class GradingScaleSeeder extends Seeder
{
    public function run(): void
    {
        $scales = [
            ['min_score' => 70, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'grade_point' => 5, 'order' => 1],
            ['min_score' => 60, 'max_score' => 69, 'grade' => 'B', 'remark' => 'Very Good', 'grade_point' => 4, 'order' => 2],
            ['min_score' => 50, 'max_score' => 59, 'grade' => 'C', 'remark' => 'Good', 'grade_point' => 3, 'order' => 3],
            ['min_score' => 45, 'max_score' => 49, 'grade' => 'D', 'remark' => 'Fair', 'grade_point' => 2, 'order' => 4],
            ['min_score' => 40, 'max_score' => 44, 'grade' => 'E', 'remark' => 'Pass', 'grade_point' => 1, 'order' => 5],
            ['min_score' => 0, 'max_score' => 39, 'grade' => 'F', 'remark' => 'Fail', 'grade_point' => 0, 'order' => 6],
        ];

        foreach ($scales as $scale) {
            GradingScale::updateOrCreate(['grade' => $scale['grade']], $scale);
        }
    }
}
