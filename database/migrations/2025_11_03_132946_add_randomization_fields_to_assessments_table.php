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
        Schema::table('assessments', function (Blueprint $table) {
            $table->boolean('is_randomized')->default(false)->after('show_results_immediately');
            $table->boolean('shuffle_options')->default(false)->after('is_randomized');
            $table->boolean('show_correct_answers')->default(true)->after('shuffle_options');
            $table->boolean('allow_review')->default(true)->after('show_correct_answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['is_randomized', 'shuffle_options', 'show_correct_answers', 'allow_review']);
        });
    }
};
