<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Services\Assessments\QuestionWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebDevelopment1Seeder extends Seeder
{
    private const QUIZ_SIZE = 10;

    private const FINAL_QUIZ_SIZE = 15;

    private const BASE_TAGS = ['web-development-1', 'web1'];

    /**
     * Rewrites Web Development 1 as a 3-week camp: 15 lessons of 2 hours,
     * a 10-question bank quiz each day, and a project assignment to upload.
     * Day 15's quiz draws 15 questions from the whole bank.
     *
     * Safe to re-run: php artisan db:seed --class=WebDevelopment1Seeder
     */
    public function run(): void
    {
        $course = Course::query()->where('slug', 'web-development-1')->orWhere('title', 'Web Development 1')->first();

        if (! $course) {
            $this->command->error('Web Development 1 was not found. Create that course before seeding these lessons.');

            return;
        }

        $instructor = User::query()->find($course->instructor_id) ?? $this->resolveInstructor();

        if (! $instructor) {
            $this->command->error('The course has no instructor and no user exists to own the new questions.');

            return;
        }

        $data = require database_path('seeders/data/web1/lessons.php');
        $bank = require database_path('seeders/data/web1/questions.php');
        $writer = app(QuestionWriter::class);

        $course->fill($data['course']);
        $course->save();
        $this->command->info("Using course: {$course->title} (ID {$course->id})");

        $globalOrder = 1;
        $allQuestionIds = [];
        $keptSlugs = [];
        $finalLesson = null;

        foreach ($data['modules'] as $moduleIndex => $moduleData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleData['title']],
                [
                    'description' => $moduleData['description'],
                    'overview' => $moduleData['overview'],
                    'order_index' => $moduleIndex + 1,
                    'estimated_duration_hours' => $moduleData['hours'],
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $instructor->id,
                ]
            );

            foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                $lesson = Lesson::withoutEvents(fn () => $this->seedLesson($course, $module, $lessonIndex, $globalOrder, $lessonData, $instructor));
                $keptSlugs[] = $lesson->slug;

                if (isset($bank[$globalOrder])) {
                    $quiz = $this->seedQuiz($lesson, $globalOrder, $globalOrder === 15);
                    $ids = $this->seedBankQuestions($writer, $bank[$globalOrder], $globalOrder, $course, $module, $instructor);
                    $quiz->pinQuestions($ids);
                    $allQuestionIds = array_merge($allQuestionIds, $ids);
                }

                $this->seedAssignment($lesson, $lessonData['assignment'], $data['assignment_defaults']);

                if ($globalOrder === 15) {
                    $finalLesson = $lesson;
                }

                $this->command->info("Seeded day {$globalOrder}: {$lesson->title}");
                $globalOrder++;
            }
        }

        if ($finalLesson) {
            $final = Assessment::query()
                ->where('course_id', $finalLesson->course_id)
                ->where('lesson_id', $finalLesson->id)
                ->where('assessment_type', 'quiz')
                ->first();
            $final?->pinQuestions($allQuestionIds);
            $this->command->info('Day 15 quiz pool: '.count($allQuestionIds).' questions, '.self::FINAL_QUIZ_SIZE.' per attempt.');
        }

        $hidden = Lesson::withoutEvents(function () use ($course, $keptSlugs) {
            return Lesson::withTrashed()
                ->where('course_id', $course->id)
                ->whereNotIn('slug', $keptSlugs)
                ->update(['is_published' => false, 'is_active' => false]);
        });

        $this->command->info("Hid {$hidden} older Web 1 lesson(s) that are not part of the 15-day camp.");
        $this->command->info('Web Development 1 camp notes are ready.');
    }

    private function seedLesson(Course $course, CourseModule $module, int $lessonIndex, int $globalOrder, array $lessonData, User $instructor): Lesson
    {
        return Lesson::updateOrCreate(
            ['course_id' => $course->id, 'slug' => Str::slug($lessonData['title'])],
            [
                'module_id' => $module->id,
                'title' => $lessonData['title'],
                'content' => $lessonData['content'],
                'summary' => $lessonData['summary'],
                'question_of_day' => $lessonData['question_of_day'],
                'objectives' => $lessonData['objectives'],
                'implementation_guidance' => $lessonData['guidance'],
                'lesson_type' => 'text',
                'difficulty_level' => 'beginner',
                'duration_minutes' => 120,
                'order' => $globalOrder,
                'order_index' => $lessonIndex + 1,
                'is_published' => true,
                'is_free_preview' => $globalOrder === 1,
                'is_locked' => false,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $instructor->id,
            ]
        );
    }

    private function seedQuiz(Lesson $lesson, int $day, bool $final): Assessment
    {
        return Assessment::updateOrCreate(
            ['course_id' => $lesson->course_id, 'lesson_id' => $lesson->id, 'assessment_type' => 'quiz'],
            [
                'title' => $final ? 'Day 15 review quiz: Web Development 1' : "Day {$day} quiz",
                'description' => $final
                    ? '<p>'.self::FINAL_QUIZ_SIZE.' questions drawn from the whole Web 1 bank, so the review mixes HTML, CSS, and Bootstrap. Pass mark: 70%. You can try twice.</p>'
                    : '<p>'.self::QUIZ_SIZE.' questions from today’s lesson. Pass mark: 70%. You can try 3 times.</p>',
                'max_attempts' => $final ? 2 : 3,
                'time_limit_minutes' => $final ? 25 : 15,
                'passing_score' => 70,
                'xp_reward' => $final ? 100 : 40,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => true,
                'allow_review' => true,
                'is_randomized' => true,
                'shuffle_options' => true,
                'is_locked' => false,
                'questions_per_attempt' => $final ? self::FINAL_QUIZ_SIZE : null,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    /**
     * @return array<int, int>
     */
    private function seedBankQuestions(QuestionWriter $writer, array $rows, int $day, Course $course, CourseModule $module, User $instructor): array
    {
        $ids = [];

        foreach (array_slice($rows, 0, self::QUIZ_SIZE) as $index => $row) {
            [$kind, $difficulty, $text, $answer, $explanation] = $row;
            $key = sprintf('web1-L%d-Q%02d', $day, $index + 1);

            $existing = Question::withTrashed()->where('settings->bank_key', $key)->first();
            if ($existing?->trashed()) {
                $existing->restore();
            }

            $type = match ($kind) {
                'tf' => 'true_false',
                'ms' => 'multiple_select',
                default => 'multiple_choice',
            };

            $question = $writer->save(
                $existing,
                [
                    'question_text' => $text,
                    'question_type' => $type,
                    'points' => 10,
                    'difficulty' => $difficulty,
                    'status' => 'active',
                    'explanation' => $explanation,
                    'order' => $index + 1,
                    'settings' => ['bank_key' => $key],
                    'created_by' => $existing?->created_by ?? $instructor->id,
                ],
                $this->optionsFor($kind, $answer, $key, $existing),
                array_merge(self::BASE_TAGS, ['day-'.$day, $this->weekTag($day)]),
                [['course_id' => $course->id, 'course_module_id' => $module->id]],
            );

            $ids[] = $question->id;
        }

        return $ids;
    }

    /**
     * @return array<int, array{id: int|null, option_text: string, is_correct: bool}>
     */
    private function optionsFor(string $kind, mixed $answer, string $key, ?Question $existing): array
    {
        if ($kind === 'tf') {
            $options = [
                ['option_text' => 'True', 'is_correct' => $answer === true],
                ['option_text' => 'False', 'is_correct' => $answer === false],
            ];
        } else {
            $options = array_map(fn (string $option) => [
                'option_text' => ltrim($option, '*'),
                'is_correct' => str_starts_with($option, '*'),
            ], $answer);

            mt_srand(crc32($key));
            shuffle($options);
            mt_srand();
        }

        $existingIds = $existing
            ? $existing->options()->pluck('id', 'option_text')->all()
            : [];

        return array_map(fn (array $option) => $option + ['id' => $existingIds[$option['option_text']] ?? null], $options);
    }

    private function seedAssignment(Lesson $lesson, array $brief, array $defaults): void
    {
        $brief += $defaults;

        $assessment = Assessment::updateOrCreate(
            ['course_id' => $lesson->course_id, 'lesson_id' => $lesson->id, 'assessment_type' => 'assignment'],
            [
                'title' => $brief['title'],
                'description' => $brief['description'],
                'max_attempts' => 3,
                'time_limit_minutes' => null,
                'passing_score' => 60,
                'xp_reward' => $brief['xp'],
                'is_required' => true,
                'show_results_immediately' => false,
                'show_correct_answers' => false,
                'allow_review' => true,
                'is_randomized' => false,
                'shuffle_options' => false,
                'is_locked' => false,
                'questions_per_attempt' => null,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'assignment_data' => [
                    'instructions' => $brief['instructions'],
                    'submission_format' => 'file',
                    'file_types' => $brief['file_types'],
                    'max_file_size' => 20,
                    'max_points' => 100,
                    'allow_text' => true,
                    'allow_files' => true,
                    'rubric' => [
                        'The page matches today’s brief' => 40,
                        'HTML is valid and uses the tags from the lesson' => 25,
                        'Files open for someone else (paths, zip, images)' => 20,
                        'Short explanation of what you built' => 15,
                    ],
                ],
            ]
        );

        $this->upsertAssignmentQuestion($assessment, 'short_answer', [
            'question_text' => $brief['prompt_question'],
            'points' => 20,
            'order' => 1,
            'explanation' => 'Your teacher reads this while marking the upload.',
            'settings' => ['min_words' => 0, 'max_words' => 400],
        ]);

        $this->upsertAssignmentQuestion($assessment, 'file_upload', [
            'question_text' => $brief['upload_question'],
            'points' => 80,
            'order' => 2,
            'explanation' => 'Upload the HTML, CSS, images, or a zip of the project.',
            'settings' => [
                'allowed_types' => implode(',', $brief['file_types']),
                'max_size' => 20,
                'max_files' => 3,
            ],
        ]);
    }

    private function upsertAssignmentQuestion(Assessment $assessment, string $type, array $data): void
    {
        $data += ['assessment_id' => $assessment->id, 'question_type' => $type];

        $question = Question::query()
            ->where('assessment_id', $assessment->id)
            ->where('question_type', $type)
            ->first();

        $question ? $question->update($data) : Question::create($data);
    }

    private function weekTag(int $day): string
    {
        return match (true) {
            $day <= 5 => 'html',
            $day <= 10 => 'css',
            default => 'bootstrap',
        };
    }

    private function resolveInstructor(): ?User
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
    }
}
