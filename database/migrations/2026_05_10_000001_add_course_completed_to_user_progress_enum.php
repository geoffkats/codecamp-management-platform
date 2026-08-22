<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL 8.0.16+, modify the enum column
        if (Schema::hasTable('user_progress')) {
            try {
                // Add course_completed to the enum list
                DB::statement("ALTER TABLE user_progress MODIFY COLUMN type ENUM(
                    'course_enrolled',
                    'lesson_started',
                    'lesson_completed',
                    'quiz_started',
                    'quiz_completed',
                    'course_completed'
                )");
            } catch (\Exception $e) {
                // Log error but don't fail - the column might already have the value
                \Log::warning('Could not modify user_progress type enum', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_progress')) {
            try {
                DB::statement("ALTER TABLE user_progress MODIFY COLUMN type ENUM(
                    'course_enrolled',
                    'lesson_started',
                    'lesson_completed',
                    'quiz_started',
                    'quiz_completed'
                )");
            } catch (\Exception $e) {
                \Log::warning('Could not revert user_progress type enum', ['error' => $e->getMessage()]);
            }
        }
    }
};
