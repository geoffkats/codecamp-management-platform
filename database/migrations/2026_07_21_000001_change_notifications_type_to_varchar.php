<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change from ENUM to VARCHAR so adding new notification types never needs a migration.
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'info'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'info',
            'success',
            'warning',
            'error',
            'achievement',
            'reminder',
            'system',
            'lesson_approval',
            'lesson_approved',
            'lesson_rejected',
            'course_approval',
            'course_approved',
            'course_rejected',
            'assignment_graded',
            'icdl_exam_rejected'
        ) NOT NULL DEFAULT 'info'");
    }
};
