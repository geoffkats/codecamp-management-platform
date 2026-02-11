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
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_type', 20)->default('codecamp')->after('email');
            $table->string('student_id', 50)->nullable()->after('student_type');
            $table->index('student_type');
            $table->unique('student_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(191) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(191) NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->dropIndex(['student_type']);
            $table->dropColumn(['student_type', 'student_id']);
        });
    }
};
