<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_progress')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM user_progress'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        foreach (['unique_user_course_type', 'user_progress_user_id_course_id_type_unique'] as $index) {
            if (in_array($index, $indexes, true)) {
                DB::statement("ALTER TABLE user_progress DROP INDEX `{$index}`");
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: restoring the unique would block one lesson XP per course again.
    }
};
