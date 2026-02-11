<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'course_modules_deleted_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropIndex('course_modules_deleted_at_index');
            $table->dropSoftDeletes();
        });
    }
};
