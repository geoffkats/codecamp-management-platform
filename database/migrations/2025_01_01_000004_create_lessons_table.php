<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('course_modules')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('content');
            $table->text('summary')->nullable();
            $table->integer('order')->default(0);
            $table->integer('order_index')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('video_duration')->nullable();
            $table->text('question_of_day')->nullable();
            $table->text('objectives')->nullable();
            $table->text('implementation_guidance')->nullable();
            $table->string('lesson_type')->default('text');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->boolean('has_levels')->default(false);
            $table->integer('total_levels')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('attachments')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'draft'])->default('draft');
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->unique(['course_id', 'slug']);
            $table->index(['module_id', 'order_index']);
            $table->index(['lesson_type', 'difficulty_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

