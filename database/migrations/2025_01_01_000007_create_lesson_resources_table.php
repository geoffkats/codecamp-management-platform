<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('resource_type', ['teacher', 'student', 'both'])->default('both');
            $table->enum('resource_category', [
                'lesson_plan',
                'presentation',
                'worksheet',
                'video',
                'audio',
                'document',
                'image',
                'assessment',
                'rubric',
                'answer_key',
                'implementation_guide',
                'professional_development',
                'other'
            ])->default('document');
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->index(['lesson_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_resources');
    }
};

