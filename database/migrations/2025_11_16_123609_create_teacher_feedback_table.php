<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('category', ['teaching_quality', 'communication', 'support', 'professionalism', 'general'])->default('general');
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->text('feedback');
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['teacher_id', 'status']);
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_feedback');
    }
};
