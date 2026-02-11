<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $driver = DB::getDriverName();
        $constraint = 'student_profiles_student_category_check';

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement(
                "ALTER TABLE student_profiles ADD CONSTRAINT {$constraint} CHECK (student_category IN ('codecamp','school_club','ict_school'))"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $driver = DB::getDriverName();
        $constraint = 'student_profiles_student_category_check';

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE student_profiles DROP CHECK {$constraint}");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE student_profiles DROP CONSTRAINT {$constraint}");
        }
    }
};
