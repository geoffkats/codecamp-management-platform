<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'deleted_at')) {
                $table->softDeletes();
                $table->index('deleted_at', 'assessments_deleted_at_index');
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'deleted_at')) {
                $table->softDeletes();
                $table->index('deleted_at', 'assignments_deleted_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'deleted_at')) {
                $table->dropIndex('assessments_deleted_at_index');
                $table->dropSoftDeletes();
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'deleted_at')) {
                $table->dropIndex('assignments_deleted_at_index');
                $table->dropSoftDeletes();
            }
        });
    }
};
