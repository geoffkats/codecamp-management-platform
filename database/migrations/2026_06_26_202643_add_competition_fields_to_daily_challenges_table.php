<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_challenges', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_challenges', 'is_competition')) {
                $table->boolean('is_competition')->default(false)->after('course_id');
            }
            if (! Schema::hasColumn('daily_challenges', 'competition_ends_at')) {
                $table->timestamp('competition_ends_at')->nullable()->after('is_competition');
            }
        });

        // Add score column to attempts for competition ranking (completed_at already exists)
        Schema::table('daily_challenge_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_challenge_attempts', 'score')) {
                $table->unsignedSmallInteger('score')->nullable()->after('points_earned');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_challenges', function (Blueprint $table) {
            $table->dropColumn(['is_competition', 'competition_ends_at']);
        });
    }
};
