<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('name', 'codecamp_trainer')->first();

        if (!$role) {
            DB::table('roles')->insert([
                'name' => 'codecamp_trainer',
                'display_name' => 'CodeCamp Trainer',
                'description' => 'CodeCamp instructors who teach and manage CodeCamp students',
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

    public function down(): void
    {
        DB::table('roles')->where('name', 'codecamp_trainer')->delete();
    }
};
