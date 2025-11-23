<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique(); // Auto-generated: STU-YYYYMMDD-XXXX
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('parent_guardian_name');
            $table->string('parent_guardian_contact');
            $table->string('class_grade')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('address')->nullable();
            $table->boolean('uniform_paid')->default(false);
            $table->date('uniform_payment_date')->nullable();
            $table->string('scratch_account')->nullable();
            $table->string('github_account')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Student Gadgets/Laptops
        Schema::create('student_gadgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->onDelete('cascade');
            $table->string('device_type'); // laptop, tablet, etc.
            $table->string('serial_number')->nullable();
            $table->text('specifications')->nullable();
            $table->timestamps();
        });

        // Student Attendance
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['student_profile_id', 'attendance_date', 'course_id'], 'student_attendance_unique');
        });

        // Instructor Attendance
        Schema::create('instructor_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_attendance');
        Schema::dropIfExists('student_attendance');
        Schema::dropIfExists('student_gadgets');
        Schema::dropIfExists('student_profiles');
    }
};
