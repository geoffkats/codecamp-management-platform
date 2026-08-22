<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained('code_camps')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrolled_by')->constrained('users');
            $table->foreignId('previous_camp_id')->nullable()->constrained('code_camps')->nullOnDelete();
            $table->enum('status', ['active', 'completed', 'transferred', 'dropped'])->default('active');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['camp_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_enrollments');
    }
};
