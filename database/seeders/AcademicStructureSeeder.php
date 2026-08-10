<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $previousSession = AcademicSession::updateOrCreate(['name' => '2025/2026'], [
            'start_date' => '2025-09-08',
            'end_date' => '2026-07-17',
            'is_active' => false,
        ]);

        $currentSession = AcademicSession::updateOrCreate(['name' => '2026/2027'], [
            'start_date' => '2026-09-07',
            'end_date' => '2027-07-16',
            'is_active' => true,
        ]);

        foreach ([$previousSession, $currentSession] as $session) {
            $this->createTerms($session);
        }

        $currentTerm = Term::where('academic_session_id', $currentSession->id)->where('name', 'First Term')->first();
        $currentTerm->update(['is_active' => true]);

        $classes = [
            ['name' => 'Nursery 1', 'level' => 'nursery', 'order' => 1],
            ['name' => 'Nursery 2', 'level' => 'nursery', 'order' => 2],
            ['name' => 'Nursery 3', 'level' => 'nursery', 'order' => 3],
            ['name' => 'Primary 1', 'level' => 'primary', 'order' => 4],
            ['name' => 'Primary 2', 'level' => 'primary', 'order' => 5],
            ['name' => 'Primary 3', 'level' => 'primary', 'order' => 6],
            ['name' => 'Primary 4', 'level' => 'primary', 'order' => 7],
            ['name' => 'Primary 5', 'level' => 'primary', 'order' => 8],
            ['name' => 'Primary 6', 'level' => 'primary', 'order' => 9],
            ['name' => 'JSS 1', 'level' => 'junior_secondary', 'order' => 10],
            ['name' => 'JSS 2', 'level' => 'junior_secondary', 'order' => 11],
            ['name' => 'JSS 3', 'level' => 'junior_secondary', 'order' => 12],
            ['name' => 'SS 1', 'level' => 'senior_secondary', 'order' => 13],
            ['name' => 'SS 2', 'level' => 'senior_secondary', 'order' => 14],
            ['name' => 'SS 3', 'level' => 'senior_secondary', 'order' => 15],
        ];

        $schoolClasses = collect();

        foreach ($classes as $c) {
            $schoolClasses->push(SchoolClass::updateOrCreate(['name' => $c['name']], $c));
        }

        foreach ($schoolClasses as $class) {
            ClassArm::firstOrCreate(['school_class_id' => $class->id, 'name' => 'A']);

            if (in_array($class->level, ['junior_secondary', 'senior_secondary'])) {
                ClassArm::firstOrCreate(['school_class_id' => $class->id, 'name' => 'B']);
            }
        }

        $subjects = [
            ['name' => 'English Language', 'code' => 'ENG', 'category' => 'Core', 'is_compulsory' => true],
            ['name' => 'Mathematics', 'code' => 'MTH', 'category' => 'Core', 'is_compulsory' => true],
            ['name' => 'Basic Science', 'code' => 'BSC', 'category' => 'Science', 'is_compulsory' => true],
            ['name' => 'Social Studies', 'code' => 'SOS', 'category' => 'Humanities', 'is_compulsory' => true],
            ['name' => 'Civic Education', 'code' => 'CIV', 'category' => 'Humanities', 'is_compulsory' => true],
            ['name' => 'Physical and Health Education', 'code' => 'PHE', 'category' => 'Sports', 'is_compulsory' => false],
            ['name' => 'Computer Studies', 'code' => 'COM', 'category' => 'ICT', 'is_compulsory' => true],
            ['name' => 'Agricultural Science', 'code' => 'AGR', 'category' => 'Science', 'is_compulsory' => false],
            ['name' => 'Physics', 'code' => 'PHY', 'category' => 'Science', 'is_compulsory' => false],
            ['name' => 'Chemistry', 'code' => 'CHM', 'category' => 'Science', 'is_compulsory' => false],
            ['name' => 'Biology', 'code' => 'BIO', 'category' => 'Science', 'is_compulsory' => false],
            ['name' => 'Economics', 'code' => 'ECO', 'category' => 'Arts', 'is_compulsory' => false],
        ];

        $subjectModels = collect();

        foreach ($subjects as $s) {
            $subjectModels->push(Subject::updateOrCreate(['code' => $s['code']], $s + ['status' => 'active']));
        }

        $coreSubjectIds = $subjectModels->whereIn('code', ['ENG', 'MTH', 'BSC', 'SOS', 'CIV', 'COM'])->pluck('id');
        $seniorSubjectIds = $subjectModels->whereIn('code', ['ENG', 'MTH', 'PHY', 'CHM', 'BIO', 'ECO', 'COM'])->pluck('id');

        foreach ($schoolClasses as $class) {
            if ($class->level === 'senior_secondary') {
                $class->subjects()->syncWithoutDetaching($seniorSubjectIds);
            } else {
                $class->subjects()->syncWithoutDetaching($coreSubjectIds);
            }
        }

        SchoolSetting::current()->update([
            'current_academic_session_id' => $currentSession->id,
            'current_term_id' => $currentTerm->id,
        ]);
    }

    protected function createTerms(AcademicSession $session): void
    {
        $year = (int) explode('/', $session->name)[0];

        $terms = [
            ['name' => 'First Term', 'start_date' => "{$year}-09-08", 'end_date' => "{$year}-12-12"],
            ['name' => 'Second Term', 'start_date' => ($year + 1).'-01-05', 'end_date' => ($year + 1).'-04-01'],
            ['name' => 'Third Term', 'start_date' => ($year + 1).'-04-20', 'end_date' => ($year + 1).'-07-17'],
        ];

        foreach ($terms as $t) {
            Term::updateOrCreate(
                ['academic_session_id' => $session->id, 'name' => $t['name']],
                $t + ['status' => 'open']
            );
        }
    }
}
