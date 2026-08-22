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
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // First, update any 'not_submitted' values to NULL
            DB::statement("UPDATE student_profiles SET payment_status = NULL WHERE payment_status = 'not_submitted'");
            
            // Get all constraints on this table and drop the payment_status check constraint
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'payment_status'");
            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE student_profiles DROP CONSTRAINT {$constraint->CONSTRAINT_NAME}");
                } catch (\Exception $e) {
                    // Continue if constraint doesn't exist
                }
            }
            
            // Also try to drop by name directly
            try {
                DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_payment_status_check');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }
            
            // Modify column to be nullable with VARCHAR instead of ENUM for flexibility
            DB::statement('ALTER TABLE student_profiles MODIFY payment_status VARCHAR(24) NULL');
            
            // Only re-add constraint if it doesn't already exist
            $existingConstraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'student_profiles' AND CONSTRAINT_NAME = 'student_profiles_payment_status_check'");
            if (empty($existingConstraints)) {
                DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IS NULL OR payment_status IN ('pending','verified','rejected'))");
            }
        } elseif ($driver === 'pgsql') {
            // First, update any 'not_submitted' values to NULL
            DB::statement("UPDATE student_profiles SET payment_status = NULL WHERE payment_status = 'not_submitted'");
            
            // Drop existing constraint
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_payment_status_check');
            
            // Alter column to be nullable
            DB::statement('ALTER TABLE student_profiles ALTER COLUMN payment_status DROP NOT NULL');
            
            // Re-add constraint that allows NULL
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IS NULL OR payment_status IN ('pending','verified','rejected'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Update NULL values back to 'not_submitted' if the old constraint allowed it
            // For now, we'll use 'pending' as a safe default
            DB::statement("UPDATE student_profiles SET payment_status = 'pending' WHERE payment_status IS NULL");
            
            try {
                DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_payment_status_check');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }
            
            // Restore NOT NULL with VARCHAR
            DB::statement("ALTER TABLE student_profiles MODIFY payment_status VARCHAR(24) NOT NULL DEFAULT 'pending'");
            
            // Restore original constraint
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IN ('pending','verified','rejected'))");
        } elseif ($driver === 'pgsql') {
            // Update NULL values back to 'pending'
            DB::statement("UPDATE student_profiles SET payment_status = 'pending' WHERE payment_status IS NULL");
            
            // Drop constraint
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_payment_status_check');
            
            // Restore NOT NULL with default
            DB::statement("ALTER TABLE student_profiles ALTER COLUMN payment_status SET NOT NULL");
            DB::statement("ALTER TABLE student_profiles ALTER COLUMN payment_status SET DEFAULT 'pending'");
            
            // Restore original constraint  
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IN ('pending','verified','rejected'))");
        }
    }
};
