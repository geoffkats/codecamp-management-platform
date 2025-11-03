<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('game_type', ['math-quiz', 'word-puzzle', 'memory-game', 'typing-test']);
            $table->json('game_data');
            $table->json('user_answers');
            $table->integer('score')->default(0);
            $table->integer('xp_earned')->default(0);
            $table->integer('play_time_seconds')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0.00);
            $table->integer('wpm')->default(0);
            $table->integer('words_typed')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'game_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_records');
    }
};

