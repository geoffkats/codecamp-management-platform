<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['ict_teacher', 'codecamp_trainer']);
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['role', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
