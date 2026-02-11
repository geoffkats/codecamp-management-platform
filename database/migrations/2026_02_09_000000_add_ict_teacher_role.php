<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = DB::table('roles')->where('name', 'ict_teacher')->first();

        if (!$role) {
            DB::table('roles')->insert([
                'name' => 'ict_teacher',
                'display_name' => 'ICT Teacher',
                'description' => 'ICT instructors who teach and manage educational content',
                'permissions' => json_encode([
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
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'ict_teacher')->delete();
    }
};
