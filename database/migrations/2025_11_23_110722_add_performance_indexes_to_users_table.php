<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('is_active');
            $table->index('created_at');
            $table->index(['is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_active', 'created_at']);
        });
    }
};
