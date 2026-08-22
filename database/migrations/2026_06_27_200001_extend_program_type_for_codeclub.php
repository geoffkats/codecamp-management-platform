<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('student_profiles', 'program_type')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE student_profiles MODIFY program_type VARCHAR(20) NOT NULL DEFAULT "codecamp"');
                try {
                    DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_program_type_check');
                } catch (\Throwable) {
                    // constraint may not exist
                }
                try {
                    DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_program_type_check CHECK (program_type IN ('ict','codecamp','codeclub'))");
                } catch (\Throwable) {
                    // ignore if unsupported
                }
            } else {
                Schema::table('student_profiles', function (Blueprint $table) {
                    $table->string('program_type', 20)->default('codecamp')->change();
                });
            }
        }

        // users.student_type stays nullable (staff have NULL); codeclub fits existing varchar(20).
        if (Schema::hasColumn('users', 'student_type')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE users MODIFY student_type VARCHAR(20) NULL');
            }
        }
    }

    public function down(): void
    {
        DB::table('student_profiles')->where('program_type', 'codeclub')->update(['program_type' => 'codecamp']);
        DB::table('users')->where('student_type', 'codeclub')->update(['student_type' => 'codecamp']);
    }
};
