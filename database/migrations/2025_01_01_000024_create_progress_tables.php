<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_progress')) {
            Schema::create('user_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->nullable()->constrained()->onDelete('cascade');
                $table->enum('type', [
                    'course_enrolled',
                    'lesson_started',
                    'lesson_completed',
                    'quiz_started',
                    'quiz_completed'
                ]);
                $table->json('metadata')->nullable();
                $table->integer('points_earned')->default(0);
                $table->decimal('score', 5, 2)->nullable();
                $table->integer('time_spent')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'course_id']);
            });
        }

        if (!Schema::hasTable('lesson_progress')) {
            Schema::create('lesson_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->integer('time_spent')->default(0);
                $table->json('progress_data')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'lesson_id']);
            });
        }

        if (!Schema::hasTable('student_lesson_progress')) {
            Schema::create('student_lesson_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
                $table->foreignId('activity_id')->nullable()->constrained('lesson_activities')->onDelete('cascade');
                $table->enum('status', [
                    'not_started',
                    'in_progress',
                    'keep_working',
                    'needs_review',
                    'completed'
                ])->default('not_started');
                $table->integer('progress_percentage')->default(0);
                $table->integer('time_spent_minutes')->default(0);
                $table->integer('attempts')->default(0);
                $table->json('completion_data')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'lesson_id', 'activity_id']);
            });
        }

        if (!Schema::hasTable('video_progress')) {
            Schema::create('video_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
                $table->string('video_url');
                $table->integer('duration_seconds');
                $table->integer('watched_seconds')->default(0);
                $table->decimal('progress_percentage', 5, 2)->default(0.00);
                $table->integer('last_position_seconds')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('last_watched_at')->nullable();
                $table->integer('watch_count')->default(0);
                $table->timestamps();
                
                $table->unique(['user_id', 'lesson_id', 'video_url']);
            });
        }

        if (!Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
                $table->integer('attempt_number');
                $table->decimal('score', 5, 2)->default(0.00);
                $table->boolean('is_passed')->default(false);
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->json('answers')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'quiz_id', 'attempt_number']);
            });
        }

        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->morphs('gradeable');
                $table->decimal('score', 5, 2);
                $table->decimal('max_score', 5, 2);
                $table->decimal('percentage', 5, 2);
                $table->string('letter_grade')->nullable();
                $table->text('feedback')->nullable();
                $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('graded_at')->nullable();
                $table->boolean('is_final')->default(false);
                $table->timestamps();
                
                $table->index(['user_id', 'course_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('video_progress');
        Schema::dropIfExists('student_lesson_progress');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('user_progress');
    }
};
