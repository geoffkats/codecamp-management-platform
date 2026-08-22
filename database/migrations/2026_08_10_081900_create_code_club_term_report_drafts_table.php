<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_club_term_report_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_club_id')->constrained('code_clubs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('term_key', 32);
            $table->string('term_label')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('summary')->nullable();
            $table->string('overall_label', 64)->nullable();
            $table->text('instructor_comment')->nullable();
            $table->json('track_notes')->nullable();
            $table->json('behavior')->nullable();
            $table->json('achievements')->nullable();
            $table->json('improvements')->nullable();
            $table->json('goals')->nullable();
            $table->timestamps();

            $table->unique(['code_club_id', 'student_id', 'term_key'], 'cc_term_drafts_unique');
            $table->index(['code_club_id', 'term_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_club_term_report_drafts');
    }
};
