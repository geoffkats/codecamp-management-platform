<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('time_limit')->nullable();
            $table->integer('max_attempts')->default(3);
            $table->decimal('passing_score', 5, 2)->default(70.00);
            $table->boolean('is_randomized')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('show_correct_answers')->default(true);
            $table->boolean('allow_review')->default(true);
            $table->timestamps();
            
            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

