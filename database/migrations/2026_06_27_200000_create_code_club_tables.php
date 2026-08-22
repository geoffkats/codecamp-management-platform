<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('day_of_week', 20)->nullable();
            $table->time('session_start')->nullable();
            $table->time('session_end')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->unsignedInteger('max_capacity')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index('status');
        });

        Schema::create('code_club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_club_id')->constrained('code_clubs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('dropped_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['code_club_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('code_club_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_club_id')->constrained('code_clubs')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['facilitator', 'assistant'])->default('facilitator');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['code_club_id', 'instructor_id']);
            $table->index(['instructor_id', 'status']);
        });

        Schema::create('club_session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_club_id')->constrained('code_clubs')->cascadeOnDelete();
            $table->foreignId('facilitator_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->text('summary')->nullable();
            $table->text('challenges')->nullable();
            $table->text('topics_covered')->nullable();
            $table->unsignedSmallInteger('attendance_count')->default(0);
            $table->unsignedSmallInteger('enrolled_count')->default(0);
            $table->boolean('follow_up_required')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['code_club_id', 'session_date']);
            $table->index(['facilitator_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_session_reports');
        Schema::dropIfExists('code_club_instructors');
        Schema::dropIfExists('code_club_memberships');
        Schema::dropIfExists('code_clubs');
    }
};
