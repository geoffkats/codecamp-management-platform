<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add soft deletes to lessons table
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to assessments table
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to quizzes table
        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to assignments table
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
