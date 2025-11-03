<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('assessment_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->string('question_type');
            $table->integer('points')->default(10);
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable();
            $table->string('media_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_alt_text')->nullable();
            $table->enum('image_position', ['top', 'bottom', 'left', 'right'])->default('top');
            $table->string('media_type')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index('quiz_id');
            $table->index('assessment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

