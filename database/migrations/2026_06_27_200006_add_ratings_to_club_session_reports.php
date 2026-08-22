<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_session_reports', function (Blueprint $table) {
            $table->unsignedTinyInteger('teamwork_rating')->nullable()->after('new_techniques');
            $table->unsignedTinyInteger('collaboration_rating')->nullable()->after('teamwork_rating');
        });
    }

    public function down(): void
    {
        Schema::table('club_session_reports', function (Blueprint $table) {
            $table->dropColumn(['teamwork_rating', 'collaboration_rating']);
        });
    }
};
