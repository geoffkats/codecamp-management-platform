<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_progress')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $column = collect(DB::select("SHOW COLUMNS FROM user_progress LIKE 'type'"))->first();
            $type = strtolower((string) ($column->Type ?? ''));

            if (str_starts_with($type, 'enum') && ! str_contains($type, 'admin_award')) {
                DB::statement("ALTER TABLE user_progress MODIFY COLUMN type ENUM(
                    'course_enrolled',
                    'lesson_started',
                    'lesson_completed',
                    'quiz_started',
                    'quiz_completed',
                    'course_completed',
                    'admin_award'
                )");
            }
        }

        $indexes = ['unique_user_course_type', 'user_progress_user_id_course_id_type_unique'];

        foreach ($indexes as $index) {
            try {
                Schema::table('user_progress', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index);
                });
                break;
            } catch (\Throwable) {
                // Index name can differ between environments.
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_progress')) {
            return;
        }

        Schema::table('user_progress', function (Blueprint $table) {
            $table->unique(['user_id', 'course_id', 'type'], 'unique_user_course_type');
        });
    }
};
