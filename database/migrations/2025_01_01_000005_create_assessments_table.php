<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('assessment_type');
            $table->text('description')->nullable();
            $table->unsignedInteger('max_attempts')->default(1);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('passing_score')->default(70);
            $table->unsignedInteger('xp_reward')->default(50);
            $table->json('questions')->nullable();
            $table->json('rubric_criteria')->nullable();
            $table->json('assignment_data')->nullable();
            $table->json('project_test_data')->nullable();
            $table->json('survey_data')->nullable();
            $table->json('peer_review_data')->nullable();
            $table->json('self_assessment_data')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('show_results_immediately')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'draft'])->default('draft');
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('course_id');
            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};

