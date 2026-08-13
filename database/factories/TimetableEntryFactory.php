<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_arm_id' => ClassArm::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'academic_session_id' => AcademicSession::factory(),
            'day_of_week' => fake()->randomElement(array_keys(TimetableEntry::DAYS)),
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => fake()->randomElement(['Room 1', 'Room 2', 'Lab A']),
        ];
    }
}
