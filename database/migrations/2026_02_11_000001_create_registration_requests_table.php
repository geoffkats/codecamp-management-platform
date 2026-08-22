<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('organization_name')->nullable();
            $table->string('role_title')->nullable();
            $table->string('program_interest')->nullable();
            $table->string('course_interest')->nullable();
            $table->string('school_level')->nullable();
            $table->unsignedInteger('students_count')->nullable();
            $table->string('national_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('preferred_schedule')->nullable();
            $table->date('preferred_exam_date')->nullable();
            $table->json('icdl_modules')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};
