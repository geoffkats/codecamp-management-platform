<?php

use App\Services\Assessments\QuestionBankBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('difficulty', 10)->default('medium')->after('points');
            $table->string('status', 12)->default('active')->after('difficulty');
            $table->unsignedInteger('version')->default(1)->after('status');
            $table->foreignId('created_by')->nullable()->after('settings')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index('status');
            $table->index('difficulty');
        });

        // A bank question must outlive the assessment it was first written for.
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['assessment_id']);
            $table->foreign('assessment_id')->references('id')->on('assessments')->nullOnDelete();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 80)->unique();
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['question_id', 'tag_id']);
        });

        Schema::create('question_placements', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_module_id')->nullable()->constrained('course_modules')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['question_id', 'course_id', 'course_module_id'], 'qp_question_course_module_unique');
        });

        Schema::create('assessment_question', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['assessment_id', 'question_id']);
            $table->index(['assessment_id', 'position']);
        });

        QuestionBankBackfill::run();
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_question');
        Schema::dropIfExists('question_placements');
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('tags');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['assessment_id']);
            $table->foreign('assessment_id')->references('id')->on('assessments')->cascadeOnDelete();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['difficulty']);
            $table->dropColumn(['difficulty', 'status', 'version', 'created_by', 'updated_by', 'deleted_at']);
        });
    }
};
