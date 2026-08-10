<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\ClassArm;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndPeopleSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::where('is_active', true)->first();

        // --- Core accounts -------------------------------------------------
        $superAdmin = User::updateOrCreate(['email' => 'superadmin@primefoundationacademy.example'], [
            'name' => 'Chinedu Eze',
            'password' => Hash::make('SuperAdmin@2026'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->syncRoles(['super-admin']);

        $administrator = User::updateOrCreate(['email' => 'admin@primefoundationacademy.example'], [
            'name' => 'Funke Bello',
            'password' => Hash::make('Administrator@2026'),
            'email_verified_at' => now(),
        ]);
        $administrator->syncRoles(['administrator']);

        // --- Teachers --------------------------------------------------------
        $jss1 = SchoolClass::where('name', 'JSS 1')->first();
        $jss1a = ClassArm::where('school_class_id', $jss1->id)->where('name', 'A')->first();
        $jss1b = ClassArm::where('school_class_id', $jss1->id)->where('name', 'B')->first();

        $teacherDefs = [
            ['name' => 'Mrs. Ngozi Adichie', 'first_name' => 'Ngozi', 'last_name' => 'Adichie', 'email' => 'teacher@primefoundationacademy.example', 'password' => 'Teacher@2026', 'class_teacher_of' => $jss1a, 'qualification' => 'B.Ed English'],
            ['name' => 'Mr. Tunde Bakare', 'first_name' => 'Tunde', 'last_name' => 'Bakare', 'email' => 'tunde.bakare@primefoundationacademy.example', 'password' => 'Teacher@2026', 'class_teacher_of' => $jss1b, 'qualification' => 'B.Sc Mathematics'],
            ['name' => 'Miss Amaka Obi', 'first_name' => 'Amaka', 'last_name' => 'Obi', 'email' => 'amaka.obi@primefoundationacademy.example', 'password' => 'Teacher@2026', 'class_teacher_of' => null, 'qualification' => 'B.Sc Biology'],
        ];

        $teachers = collect();

        foreach ($teacherDefs as $i => $def) {
            $user = User::updateOrCreate(['email' => $def['email']], [
                'name' => $def['name'],
                'password' => Hash::make($def['password']),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['teacher']);

            $teacher = Teacher::updateOrCreate(['user_id' => $user->id], [
                'teacher_id' => 'PFA/TCH/26/'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'first_name' => $def['first_name'],
                'last_name' => $def['last_name'],
                'gender' => $i === 1 ? 'male' : 'female',
                'date_of_birth' => now()->subYears(30 + $i)->format('Y-m-d'),
                'phone' => '080100000'.$i,
                'email' => $def['email'],
                'qualification' => $def['qualification'],
                'employment_date' => now()->subYears(3)->format('Y-m-d'),
                'status' => 'active',
            ]);

            if ($def['class_teacher_of']) {
                $def['class_teacher_of']->update(['class_teacher_id' => $teacher->id]);
            }

            $teachers->push($teacher);
        }

        // Assign teachers to subjects/classes for the active session
        $subjects = \App\Models\Subject::whereIn('code', ['ENG', 'MTH', 'BSC'])->get()->keyBy('code');

        TeacherAssignment::firstOrCreate([
            'teacher_id' => $teachers[0]->id, 'class_arm_id' => $jss1a->id,
            'subject_id' => $subjects['ENG']->id, 'academic_session_id' => $session->id,
        ]);
        TeacherAssignment::firstOrCreate([
            'teacher_id' => $teachers[1]->id, 'class_arm_id' => $jss1a->id,
            'subject_id' => $subjects['MTH']->id, 'academic_session_id' => $session->id,
        ]);
        TeacherAssignment::firstOrCreate([
            'teacher_id' => $teachers[2]->id, 'class_arm_id' => $jss1a->id,
            'subject_id' => $subjects['BSC']->id, 'academic_session_id' => $session->id,
        ]);

        // --- Guardians -------------------------------------------------------
        $parentUser = User::updateOrCreate(['email' => 'parent@primefoundationacademy.example'], [
            'name' => 'Mr. Emeka Nwosu',
            'password' => Hash::make('Parent@2026'),
            'email_verified_at' => now(),
        ]);
        $parentUser->syncRoles(['parent']);

        $guardian = Guardian::updateOrCreate(['user_id' => $parentUser->id], [
            'first_name' => 'Emeka',
            'last_name' => 'Nwosu',
            'phone' => '08023456789',
            'email' => 'parent@primefoundationacademy.example',
            'address' => '22 Admiralty Way, Lekki, Lagos',
            'occupation' => 'Civil Engineer',
        ]);

        $otherGuardian = Guardian::updateOrCreate(['first_name' => 'Blessing', 'last_name' => 'Nwosu'], [
            'phone' => '08034567890',
            'email' => 'blessing.nwosu@example.com',
            'occupation' => 'Nurse',
        ]);

        // --- Students ----------------------------------------------------------
        $studentUser = User::updateOrCreate(['email' => 'student@primefoundationacademy.example'], [
            'name' => 'David Nwosu',
            'password' => Hash::make('Student@2026'),
            'email_verified_at' => now(),
        ]);
        $studentUser->syncRoles(['student']);

        $demoStudent = Student::updateOrCreate(['admission_number' => 'PFA/26/0001'], [
            'user_id' => $studentUser->id,
            'first_name' => 'David',
            'last_name' => 'Nwosu',
            'gender' => 'male',
            'date_of_birth' => '2013-05-14',
            'nationality' => 'Nigerian',
            'state_of_origin' => 'Imo',
            'lga' => 'Owerri Municipal',
            'address' => '22 Admiralty Way, Lekki, Lagos',
            'phone' => '08023456789',
            'email' => 'student@primefoundationacademy.example',
            'admission_date' => now()->subYears(1)->format('Y-m-d'),
            'current_class_arm_id' => $jss1a->id,
            'status' => 'active',
        ]);

        $demoStudent->guardians()->syncWithoutDetaching([
            $guardian->id => ['relationship' => 'Father', 'is_primary' => true],
            $otherGuardian->id => ['relationship' => 'Mother', 'is_primary' => false],
        ]);

        Enrollment::firstOrCreate(
            ['student_id' => $demoStudent->id, 'academic_session_id' => $session->id],
            ['school_class_id' => $jss1->id, 'class_arm_id' => $jss1a->id, 'status' => 'active']
        );

        // Additional sample students to populate class lists / reports
        $firstNames = ['Chioma', 'Ibrahim', 'Grace', 'Ayodele', 'Fatima', 'Peter', 'Ijeoma', 'Musa'];
        $lastNames = ['Okafor', 'Suleiman', 'Adeyemi', 'Balogun', 'Yusuf', 'Umeh', 'Chukwu', 'Danjuma'];

        foreach ($firstNames as $i => $first) {
            $admissionNumber = 'PFA/26/'.str_pad((string) ($i + 2), 4, '0', STR_PAD_LEFT);

            $student = Student::updateOrCreate(['admission_number' => $admissionNumber], [
                'first_name' => $first,
                'last_name' => $lastNames[$i],
                'gender' => $i % 2 === 0 ? 'female' : 'male',
                'date_of_birth' => now()->subYears(12)->subDays($i * 10)->format('Y-m-d'),
                'nationality' => 'Nigerian',
                'admission_date' => now()->subMonths(6)->format('Y-m-d'),
                'current_class_arm_id' => $jss1a->id,
                'status' => 'active',
            ]);

            $g = Guardian::updateOrCreate(['first_name' => $first.' Sr', 'last_name' => $lastNames[$i]], [
                'phone' => '0803300'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'occupation' => 'Trader',
            ]);

            $student->guardians()->syncWithoutDetaching([$g->id => ['relationship' => 'Parent', 'is_primary' => true]]);

            Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'academic_session_id' => $session->id],
                ['school_class_id' => $jss1->id, 'class_arm_id' => $jss1a->id, 'status' => 'active']
            );
        }
    }
}
