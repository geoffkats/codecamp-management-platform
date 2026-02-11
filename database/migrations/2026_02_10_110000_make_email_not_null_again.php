<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make email NOT NULL again - required for authentication
        if (DB::getDriverName() === 'mysql') {
            // First ensure no NULL emails exist
            DB::statement("
                UPDATE users 
                SET email = CONCAT('user', id, '@placeholder.local') 
                WHERE email IS NULL
            ");
            
            // Now make column NOT NULL
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(191) NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(191) NULL');
        }
    }
};
