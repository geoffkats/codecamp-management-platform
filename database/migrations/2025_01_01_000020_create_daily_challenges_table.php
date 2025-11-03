<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', [
                'lesson_completion',
                'quiz_score',
                'study_time',
                'course_progress',
                'forum_participation',
                'assignment_submission'
            ]);
            $table->json('requirements')->nullable();
            $table->integer('reward_points')->default(100);
            $table->date('date');
            $table->boolean('is_active')->default(true);
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('category', 50)->nullable();
            $table->timestamps();
            
            $table->index(['date', 'is_active']);
        });

        Schema::create('daily_challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('daily_challenges')->onDelete('cascade');
            $table->timestamp('attempted_at');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->integer('points_earned')->default(0);
            $table->json('details')->nullable();
            $table->json('progress_data')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_challenge_attempts');
        Schema::dropIfExists('daily_challenges');
    }
};

