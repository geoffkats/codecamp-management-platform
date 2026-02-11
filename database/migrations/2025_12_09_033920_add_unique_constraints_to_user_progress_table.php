<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean duplicates first (lesson_completed only - that's what awards points)
        DB::statement("
            DELETE p1 FROM user_progress p1
            INNER JOIN user_progress p2
            WHERE p1.id > p2.id
                AND p1.user_id = p2.user_id
                AND p1.type = p2.type
                AND ((p1.course_id = p2.course_id) OR (p1.course_id IS NULL AND p2.course_id IS NULL))
                AND ((p1.lesson_id = p2.lesson_id) OR (p1.lesson_id IS NULL AND p2.lesson_id IS NULL))
                AND p1.type IN ('course_enrolled', 'lesson_completed', 'course_completed')
        ");

        Schema::table('user_progress', function (Blueprint $table) {
            // Prevent duplicate enrollment points: one per user per course
            $table->unique(['user_id', 'course_id', 'type'], 'unique_user_course_type');
            
            // Prevent duplicate lesson points: one per user per lesson per type
            $table->unique(['user_id', 'lesson_id', 'type'], 'unique_user_lesson_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropUnique('unique_user_course_type');
            $table->dropUnique('unique_user_lesson_type');
        });
    }
};
