<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist
        $hasColumns = Schema::hasColumns('student_attendances', ['course_id', 'recorded_by']);
        
        if (!$hasColumns) {
            // First, drop the foreign key that depends on the unique index
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->dropForeign(['student_profile_id']);
            });
            
            // Now drop the unique constraint
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->dropUnique(['student_profile_id', 'attendance_date']);
            });
            
            // Add new columns
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->foreignId('course_id')->nullable()->after('student_profile_id')->constrained()->onDelete('set null');
                $table->foreignId('recorded_by')->nullable()->after('notes')->constrained('users')->onDelete('cascade');
            });
            
            // Recreate the foreign key and add new unique constraint
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->foreign('student_profile_id')->references('id')->on('student_profiles')->onDelete('cascade');
                $table->unique(['student_profile_id', 'attendance_date', 'course_id'], 'student_attendance_unique');
            });
        } else {
            // Columns exist, just ensure constraints are correct
            try {
                Schema::table('student_attendances', function (Blueprint $table) {
                    $table->dropUnique(['student_profile_id', 'attendance_date']);
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            
            try {
                Schema::table('student_attendances', function (Blueprint $table) {
                    $table->unique(['student_profile_id', 'attendance_date', 'course_id'], 'student_attendance_unique');
                });
            } catch (\Exception $e) {
                // Unique constraint might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropUnique('student_attendance_unique');
            $table->dropForeign(['student_profile_id']);
        });
        
        // Drop new foreign keys and columns
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['course_id', 'recorded_by']);
        });
        
        // Restore original unique constraint and foreign key
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->unique(['student_profile_id', 'attendance_date']);
            $table->foreign('student_profile_id')->references('id')->on('student_profiles')->onDelete('cascade');
        });
    }
};
