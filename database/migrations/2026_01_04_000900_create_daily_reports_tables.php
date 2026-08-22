<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('report_date');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('instructor_id');
            $table->string('status')->default('submitted');
            $table->text('summary')->nullable();
            $table->text('challenges')->nullable();
            $table->text('issues')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['report_date', 'instructor_id', 'course_id'], 'daily_reports_unique_day_course_instructor');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('daily_report_attendance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('daily_report_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status', 20); // present, absent, late
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('tagged_by')->nullable();
            $table->timestamps();

            $table->foreign('daily_report_id')->references('id')->on('daily_reports')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tagged_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('daily_report_mentions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('daily_report_id');
            $table->string('mentionable_type');
            $table->unsignedBigInteger('mentionable_id');
            $table->string('role')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('daily_report_id')->references('id')->on('daily_reports')->onDelete('cascade');
        });

        Schema::create('daily_report_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('daily_report_id');
            $table->string('path');
            $table->string('name')->nullable();
            $table->string('type', 255)->nullable();
            $table->timestamps();

            $table->foreign('daily_report_id')->references('id')->on('daily_reports')->onDelete('cascade');
        });

        Schema::create('daily_report_issues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('daily_report_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('normal'); // normal, high, critical
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('status', 20)->default('open'); // open, in_progress, resolved
            $table->timestamps();

            $table->foreign('daily_report_id')->references('id')->on('daily_reports')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_issues');
        Schema::dropIfExists('daily_report_attachments');
        Schema::dropIfExists('daily_report_mentions');
        Schema::dropIfExists('daily_report_attendance');
        Schema::dropIfExists('daily_reports');
    }
};
