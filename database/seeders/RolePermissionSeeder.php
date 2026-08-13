<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage-users',
        'manage-roles',
        'manage-settings',
        'manage-academic-structure',
        'manage-teachers',
        'manage-students',
        'manage-guardians',
        'take-attendance',
        'view-attendance',
        'manage-assessment-config',
        'enter-scores',
        'review-results',
        'approve-results',
        'publish-results',
        'view-results',
        'manage-fees',
        'manage-payments',
        'manage-announcements',
        'manage-documents',
        'view-reports',
        'view-audit-logs',
        'view-own-data',
        'view-timetable',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        $administrator = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions([
            'manage-settings',
            'manage-academic-structure',
            'manage-teachers',
            'manage-students',
            'manage-guardians',
            'take-attendance',
            'view-attendance',
            'manage-assessment-config',
            'enter-scores',
            'review-results',
            'approve-results',
            'publish-results',
            'view-results',
            'manage-fees',
            'manage-payments',
            'manage-announcements',
            'manage-documents',
            'view-reports',
            'view-audit-logs',
            'manage-users',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->syncPermissions([
            'take-attendance',
            'view-attendance',
            'enter-scores',
            'review-results',
            'view-results',
            'manage-announcements',
            'view-timetable',
        ]);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'view-own-data',
        ]);

        $parent = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $parent->syncPermissions([
            'view-own-data',
        ]);
    }
}
