<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            // For MariaDB/MySQL, update the ENUM values directly
            DB::statement("ALTER TABLE student_profiles MODIFY payment_status ENUM('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_payment_status_check');
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IN ('not_submitted','pending','verified','rejected'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE student_profiles MODIFY payment_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_payment_status_check');
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IN ('pending','verified','rejected'))");
        }
    }
};
