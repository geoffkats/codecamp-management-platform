<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->enum('activity_type', [
                'exploration',
                'skill_building',
                'practice',
                'assessment',
                'challenge',
                'warm_up',
                'wrap_up',
                'group_work',
                'individual_work',
                'discussion',
                'presentation',
                'coding',
                'debugging',
                'research'
            ])->default('practice');
            $table->enum('level_type', [
                'concept',
                'activity',
                'assessment',
                'survey',
                'video',
                'text',
                'map',
                'unplugged',
                'online',
                'choice_level'
            ])->default('activity');
            $table->enum('level_status', [
                'not_started',
                'in_progress',
                'keep_working',
                'needs_review',
                'completed'
            ])->default('not_started');
            $table->integer('expected_duration_minutes')->default(30);
            $table->integer('order_index')->default(0);
            $table->json('level_details')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['lesson_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_activities');
    }
};

