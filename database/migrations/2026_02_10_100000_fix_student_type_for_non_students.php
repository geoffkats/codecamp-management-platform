<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove default value from student_type - it should only be set for students
        Schema::table('users', function (Blueprint $table) {
            // Change column to nullable without default
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE users MODIFY student_type VARCHAR(20) NULL');
            }
        });

        // Set student_type to NULL for all users who are NOT students
        // Only keep student_type for users with 'student' role
        if (Schema::hasTable('user_roles') && Schema::hasTable('roles')) {
            DB::statement("
                UPDATE users 
                SET student_type = NULL 
                WHERE id NOT IN (
                    SELECT DISTINCT user_roles.user_id 
                    FROM user_roles 
                    INNER JOIN roles ON user_roles.role_id = roles.id 
                    WHERE roles.name = 'student'
                )
            ");
        }

        // For users who ARE students but have NULL student_type, default to 'codecamp'
        if (Schema::hasTable('user_roles') && Schema::hasTable('roles')) {
            DB::statement("
                UPDATE users 
                SET student_type = 'codecamp' 
                WHERE student_type IS NULL 
                AND id IN (
                    SELECT DISTINCT user_roles.user_id 
                    FROM user_roles 
                    INNER JOIN roles ON user_roles.role_id = roles.id 
                    WHERE roles.name = 'student'
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore default value
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE users MODIFY student_type VARCHAR(20) DEFAULT "codecamp"');
            }
        });
    }
};
