<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('scratch_project_id')->nullable()->after('video_url');
            $table->json('lesson_steps')->nullable()->after('scratch_project_id');
            $table->json('scratch_blocks')->nullable()->after('lesson_steps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['scratch_project_id', 'lesson_steps', 'scratch_blocks']);
        });
    }
};
