<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change from rigid enum to flexible varchar so we can add any challenge type
        Schema::table('daily_challenges', function (Blueprint $table) {
            $table->string('type', 50)->default('lesson_completion')->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_challenges', function (Blueprint $table) {
            $table->enum('type', [
                'lesson_completion',
                'quiz_score',
                'study_time',
                'course_progress',
                'forum_participation',
                'assignment_submission',
            ])->default('lesson_completion')->change();
        });
    }
};
