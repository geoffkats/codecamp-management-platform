<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_challenges', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['course_id', 'date', 'is_active'], 'daily_challenges_course_date_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('daily_challenges', function (Blueprint $table) {
            $table->dropIndex('daily_challenges_course_date_active_index');
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });
    }
};
