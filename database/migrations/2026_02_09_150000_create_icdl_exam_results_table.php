<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icdl_exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('course_module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('exam_session');
            $table->decimal('score', 5, 2);
            $table->string('result');
            $table->string('status')->default('pending_review');
            $table->boolean('is_locked')->default(true);
            $table->date('exam_date');
            $table->text('teacher_comment')->nullable();
            $table->foreignId('entered_by_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('unlock_reason')->nullable();
            $table->foreignId('unlocked_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_profile_id', 'course_module_id']);
            $table->index(['status', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icdl_exam_results');
    }
};
