<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_reports', 'pedagogical_approaches')) {
                $table->json('pedagogical_approaches')->nullable()->after('issues');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'pedagogical_approaches')) {
                $table->dropColumn('pedagogical_approaches');
            }
        });
    }
};
