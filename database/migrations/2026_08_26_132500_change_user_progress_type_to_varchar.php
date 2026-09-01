<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stop ENUM truncation for admin_award / future progress types.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_progress')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM user_progress LIKE 'type'"))->first();
        $type = strtolower((string) ($column->Type ?? ''));

        if ($type === '') {
            return;
        }

        // Prefer varchar so new award types do not require another ENUM alter.
        if (str_starts_with($type, 'enum') || ! str_contains($type, 'varchar')) {
            DB::statement("ALTER TABLE user_progress MODIFY COLUMN type VARCHAR(50) NOT NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_progress')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE user_progress MODIFY COLUMN type ENUM(
            'course_enrolled',
            'lesson_started',
            'lesson_completed',
            'quiz_started',
            'quiz_completed',
            'course_completed',
            'admin_award'
        ) NOT NULL");
    }
};
