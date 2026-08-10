<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with roles, school configuration,
     * academic structure and sample data for local development/demo use.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SchoolSettingSeeder::class,
            GradingScaleSeeder::class,
            AssessmentTypeSeeder::class,
            AcademicStructureSeeder::class,
            UserAndPeopleSeeder::class,
            FeeSeeder::class,
            SampleAcademicDataSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
