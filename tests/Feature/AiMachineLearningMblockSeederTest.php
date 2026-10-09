<?php

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Support\RichContent;
use Database\Seeders\AiMachineLearningMblockSeeder;

it('seeds the AI course with bank quizzes, upload assignments and a pooled final exam, and re-runs cleanly', function () {
    userWithRole('admin');

    $this->seed(AiMachineLearningMblockSeeder::class);
    $this->seed(AiMachineLearningMblockSeeder::class);

    $course = Course::where('slug', 'ai-machine-learning-with-mblock')->firstOrFail();
    expect($course->modules()->count())->toBe(6)
        ->and($course->lessons()->count())->toBe(10);

    $bank = Question::where('settings->bank_key', 'like', 'aiml-%');
    expect((clone $bank)->count())->toBe(135)
        ->and((clone $bank)->whereDoesntHave('placements', fn ($q) => $q->where('course_id', $course->id))->count())->toBe(0);

    $quizzes = Assessment::where('course_id', $course->id)->where('title', 'like', 'Lesson % Quiz')->get();
    expect($quizzes)->toHaveCount(9);
    $quizzes->each(fn (Assessment $quiz) => expect($quiz->questions()->count())->toBe(15));

    $exam = Assessment::where('course_id', $course->id)->where('title', 'like', 'Final Exam%')->firstOrFail();
    expect($exam->questions()->count())->toBe(135)
        ->and($exam->questions_per_attempt)->toBe(40)
        ->and($exam->lesson_id)->toBe($course->lessons()->orderByDesc('order')->value('id'));

    $assignments = Assessment::where('course_id', $course->id)->where('assessment_type', 'assignment')->get();
    expect($assignments)->toHaveCount(10);
    $upload = Question::where('assessment_id', $assignments->first()->id)->where('question_type', 'file_upload')->firstOrFail();
    expect($upload->settings['allowed_types'])->toContain('mblock');

    $html = RichContent::render($course->lessons()->orderBy('order')->value('content'));
    expect($html)->not->toContain('&lt;div')->not->toContain('&lt;table');
});
