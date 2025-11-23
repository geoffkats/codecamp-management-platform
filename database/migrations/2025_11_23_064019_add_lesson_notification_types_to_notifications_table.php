<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to add new notification types
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
            'course_rejected'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'info', 
            'success', 
            'warning', 
            'error', 
            'achievement', 
            'reminder', 
            'system'
        ) NOT NULL");
    }
};
