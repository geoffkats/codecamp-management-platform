<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add enrollment control fields to courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('enrollment_type', ['open', 'invite_only', 'approval_required'])->default('open')->after('is_featured');
            $table->integer('max_students')->nullable()->after('enrollment_type');
        });

        // Create course invitations table for invite-only courses
        Schema::create('course_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->timestamp('invited_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        // Create enrollment requests table for approval-required courses
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('message')->nullable(); // Student's request message
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['status', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
        Schema::dropIfExists('course_invitations');
        
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['enrollment_type', 'max_students']);
        });
    }
};

