<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('code_club_term_report_drafts', function (Blueprint $table) {
            $table->json('metrics_overrides')->nullable()->after('goals');
        });
    }

    public function down(): void
    {
        Schema::table('code_club_term_report_drafts', function (Blueprint $table) {
            $table->dropColumn('metrics_overrides');
        });
    }
};
