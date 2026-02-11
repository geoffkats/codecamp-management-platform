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
            if (!Schema::hasColumn('student_profiles', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('student_profiles', 'program_type')) {
                $table->enum('program_type', ['ict', 'codecamp'])->default('codecamp')->after('school_id');
            }
        });

        if (Schema::hasTable('schools') && Schema::hasColumn('student_profiles', 'school_id')) {
            $canAddForeignKey = true;

            if (DB::getDriverName() === 'mysql') {
                $schoolStatus = DB::selectOne("SHOW TABLE STATUS LIKE 'schools'");
                $studentStatus = DB::selectOne("SHOW TABLE STATUS LIKE 'student_profiles'");
                $schoolEngine = strtolower($schoolStatus->Engine ?? '');
                $studentEngine = strtolower($studentStatus->Engine ?? '');

                if ($schoolEngine !== 'innodb' || $studentEngine !== 'innodb') {
                    $canAddForeignKey = false;
                }
            }

            if ($canAddForeignKey) {
                try {
                    Schema::table('student_profiles', function (Blueprint $table) {
                        $table->foreign('school_id')
                            ->references('id')
                            ->on('schools')
                            ->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // Skip FK creation if it fails (e.g., engine mismatch or table missing).
                }
            }
        }

        if (Schema::hasColumn('student_profiles', 'student_category')) {
            DB::table('student_profiles')
                ->where('student_category', 'ict_school')
                ->update(['program_type' => 'ict']);
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_program_type_check CHECK (program_type IN ('ict','codecamp'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_program_type_check');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_program_type_check');
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'program_type')) {
                $table->dropColumn('program_type');
            }

            if (Schema::hasColumn('student_profiles', 'school_id')) {
                try {
                    $table->dropForeign(['school_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK does not exist.
                }
                $table->dropColumn('school_id');
            }
        });
    }
};
