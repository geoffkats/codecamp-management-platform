<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_session_reports', function (Blueprint $table) {
            $table->text('new_techniques')->nullable()->after('topics_covered');
        });
    }

    public function down(): void
    {
        Schema::table('club_session_reports', function (Blueprint $table) {
            $table->dropColumn('new_techniques');
        });
    }
};
