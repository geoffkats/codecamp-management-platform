<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'icdl_number')) {
                $table->string('icdl_number')->nullable()->after('student_id');
            }

            if (!Schema::hasColumn('student_profiles', 'exam_readiness_status')) {
                $table->string('exam_readiness_status', 32)->default('not_ready')->after('program_type');
            }

            if (!Schema::hasColumn('student_profiles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('program_type');
            }
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_exam_readiness_check CHECK (exam_readiness_status IN ('not_ready','student_requested','teacher_approved','needs_practice','exam_completed'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_exam_readiness_check');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_exam_readiness_check');
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'icdl_number')) {
                $table->dropColumn('icdl_number');
            }

            if (Schema::hasColumn('student_profiles', 'exam_readiness_status')) {
                $table->dropColumn('exam_readiness_status');
            }

            if (Schema::hasColumn('student_profiles', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
