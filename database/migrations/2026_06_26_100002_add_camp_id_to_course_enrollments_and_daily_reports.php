<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // camp_id column already exists on course_enrollments (partial migration), just add the FK
        Schema::table('course_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('course_enrollments', 'camp_id')) {
                $table->unsignedBigInteger('camp_id')->nullable()->after('course_id');
            }
            $table->foreign('camp_id')->references('id')->on('code_camps')->nullOnDelete();
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('camp_id')->nullable()->after('course_id');
            $table->foreign('camp_id')->references('id')->on('code_camps')->nullOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropForeign(['camp_id']);
            $table->dropColumn('camp_id');
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['camp_id']);
            $table->dropColumn('camp_id');
        });
    }
};
