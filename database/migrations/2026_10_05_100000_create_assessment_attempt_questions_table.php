<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_attempt_questions', function (Blueprint $table) {
            // Foreign keys and row locks during generation require InnoDB even where the server default is MyISAM.
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            // Nullable so deleting a bank question never deletes what a student actually answered.
            $table->foreignId('question_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('question_version')->nullable();
            $table->unsignedSmallInteger('position');
            $table->string('question_type', 50);
            $table->decimal('points', 8, 2)->default(0);
            $table->json('snapshot');
            $table->json('presentation')->nullable();
            $table->json('source')->nullable();
            $table->decimal('earned_points', 8, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('needs_manual')->default(false);
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_attempt_id', 'position'], 'aaq_attempt_position_unique');
            $table->unique(['assessment_attempt_id', 'question_id'], 'aaq_attempt_question_unique');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->timestamp('question_set_generated_at')->nullable()->after('answers');
            $table->unsignedSmallInteger('question_set_version')->nullable()->after('question_set_generated_at');
            $table->string('question_set_source', 20)->nullable()->after('question_set_version');
            $table->json('question_set_meta')->nullable()->after('question_set_source');
            $table->boolean('question_set_review_required')->default(false)->after('question_set_meta');
            // null = legacy row of unknown unit (see AssessmentAttempt::scoreAsPoints); new rows store 'points'.
            $table->string('score_unit', 16)->nullable()->after('score');

            $table->index('question_set_review_required');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropIndex(['question_set_review_required']);
            $table->dropColumn([
                'question_set_generated_at',
                'question_set_version',
                'question_set_source',
                'question_set_meta',
                'question_set_review_required',
                'score_unit',
            ]);
        });

        Schema::dropIfExists('assessment_attempt_questions');
    }
};
