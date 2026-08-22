<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_program_type_check');
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_program_type_check CHECK (program_type IN ('ict','codecamp','codeclub'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_program_type_check');
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_program_type_check CHECK (program_type IN ('ict','codecamp'))");
        }
    }
};
