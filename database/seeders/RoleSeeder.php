<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'student',
                'display_name' => 'Student',
                'description' => 'Learners who enroll in courses and complete educational activities',
                'permissions' => [
                    'view_courses',
                    'enroll_courses',
                    'take_quizzes',
                    'view_progress',
                    'view_badges',
                ],
            ],
            [
                'name' => 'teacher',
                'display_name' => 'Teacher',
                'description' => 'Instructors who create and manage educational content',
                'permissions' => [
                    'view_courses',
                    'create_courses',
                    'edit_courses',
                    'delete_courses',
                    'create_lessons',
                    'edit_lessons',
                    'delete_lessons',
                    'create_quizzes',
                    'edit_quizzes',
                    'delete_quizzes',
                    'view_student_progress',
                    'grade_assignments',
                    'add_student',
                    'edit_student',
                    'view_student',
                    'assign_assessment',
                    'view_reports',
                    'grade_assessment',
                ],
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administrators with full access',
                'permissions' => [
                    'view_courses',
                    'create_courses',
                    'edit_courses',
                    'delete_courses',
                    'manage_users',
                    'manage_roles',
                    'view_analytics',
                    'system_settings',
                    'manage_badges',
                    'view_all_progress',
                ],
            ],
            [
                'name' => 'supervisor',
                'display_name' => 'Supervisor',
                'description' => 'Content supervisors who review and approve educational materials',
                'permissions' => [
                    'view_courses',
                    'edit_courses',
                    'view_analytics',
                    'approve_content',
                    'reject_content',
                    'manage_enrollments',
                    'view_all_progress',
                    'view_student_progress',
                ],
            ],
            [
                'name' => 'operations_manager',
                'display_name' => 'Operations Manager',
                'description' => 'Responsible for operational compliance, attendance, and inventory',
                'permissions' => [
                    'record_student_attendance',
                    'verify_uniform_payment',
                    'record_instructor_attendance',
                    'manage_inventory',
                    'view_reports',
                    'export_data',
                    'view_student_gadgets',
                    'view_student',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}

