<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('created_at');
            $table->index('subject_tag');
            $table->index(['course_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['is_pinned', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['subject_tag']);
            $table->dropIndex(['course_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['is_pinned', 'created_at']);
        });
    }
};
