<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points');
            $table->integer('rank');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('last_updated');
            $table->timestamps();
            
            $table->index(['type', 'course_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};

