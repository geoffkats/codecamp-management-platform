<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->dropForeign(['course_module_id']);
        });

        DB::statement('ALTER TABLE internal_test_marks MODIFY course_module_id BIGINT UNSIGNED NULL');

        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->foreign('course_module_id')
                ->references('id')
                ->on('course_modules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->dropForeign(['course_module_id']);
        });

        DB::statement('ALTER TABLE internal_test_marks MODIFY course_module_id BIGINT UNSIGNED NOT NULL');

        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->foreign('course_module_id')
                ->references('id')
                ->on('course_modules')
                ->cascadeOnDelete();
        });
    }
};
