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
            $table->foreignId('course_id')
                ->nullable()
                ->after('course_module_id')
                ->constrained('courses')
                ->nullOnDelete();

            $table->index(['student_profile_id', 'course_id']);
        });

        DB::table('internal_test_marks')
            ->whereNull('course_id')
            ->whereNotNull('course_module_id')
            ->update([
                'course_id' => DB::raw('(SELECT course_id FROM course_modules WHERE course_modules.id = internal_test_marks.course_module_id)')
            ]);
    }

    public function down(): void
    {
        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->dropIndex(['student_profile_id', 'course_id']);
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
