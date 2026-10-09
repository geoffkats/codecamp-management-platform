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

class AiMachineLearningMblockSeeder extends Seeder
{
    private const QUIZ_SIZE = 15;

    private const EXAM_QUESTIONS_PER_ATTEMPT = 40;

    private const BASE_TAGS = ['ai-ml-mblock', 'machine-learning'];

    /**
     * AI & Machine Learning with mBlock — 6 modules, 10 lessons, a 135-question bank,
     * a 15-question quiz and an evidence-upload assignment per lesson, and a final exam
     * drawing from the whole bank.
     *
     * Safe to re-run: php artisan db:seed --class=AiMachineLearningMblockSeeder
     */
    public function run(): void
    {
        $instructor = $this->resolveInstructor();

        if (! $instructor) {
            $this->command->error('No users found. Create at least one teacher or admin before seeding this course.');

            return;
        }

        $data = require database_path('seeders/data/ai-ml-mblock/lessons.php');
        $bank = require database_path('seeders/data/ai-ml-mblock/questions.php');
        $writer = app(QuestionWriter::class);

        $course = $this->seedCourse($instructor, $data['course']);
        $this->command->info("Using course: {$course->title} (ID {$course->id})");

        $globalOrder = 1;
        $allQuestionIds = [];
        $examLesson = null;

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
                $lesson = $this->seedLesson($course, $module, $lessonIndex, $globalOrder, $lessonData, $instructor);

                if (isset($bank[$globalOrder])) {
                    $quiz = $this->seedQuiz($lesson, $globalOrder);
                    $ids = $this->seedBankQuestions($writer, $bank[$globalOrder], $globalOrder, $course, $module, $instructor);
                    $quiz->pinQuestions($ids);
                    $allQuestionIds = array_merge($allQuestionIds, $ids);
                }

                $this->seedAssignment($lesson, $lessonData['assignment'], $data['assignment_defaults']);

                if (! empty($lessonData['final_exam'])) {
                    $examLesson = $lesson;
                }

                $this->command->info("Seeded lesson {$globalOrder}: {$lesson->title}");
                $globalOrder++;
            }
        }

        if ($examLesson) {
            $exam = $this->seedFinalExam($examLesson);
            $exam->pinQuestions($allQuestionIds);
            $this->command->info('Final exam pool: '.count($allQuestionIds).' questions, '.self::EXAM_QUESTIONS_PER_ATTEMPT.' per attempt.');
        }

        $this->command->info('AI & Machine Learning with mBlock is ready.');
    }

    private function seedCourse(User $instructor, array $payload): Course
    {
        $existing = Course::query()
            ->where('slug', $payload['slug'])
            ->orWhere('title', $payload['title'])
            ->first();

        $payload += [
            'price' => 0.00,
            'is_published' => true,
            'enrollment_type' => 'invite_only',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $instructor->id,
        ];

        if ($existing) {
            $existing->fill($payload);
            if (! $existing->instructor_id) {
                $existing->instructor_id = $instructor->id;
            }
            $existing->save();

            return $existing;
        }

        $payload['instructor_id'] = $instructor->id;

        return Course::create($payload);
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
                'difficulty_level' => $lessonData['difficulty'],
                'duration_minutes' => $lessonData['minutes'],
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

    private function seedQuiz(Lesson $lesson, int $number): Assessment
    {
        return Assessment::updateOrCreate(
            ['course_id' => $lesson->course_id, 'lesson_id' => $lesson->id, 'assessment_type' => 'quiz'],
            [
                'title' => "Lesson {$number} Quiz",
                'description' => '<p>'.self::QUIZ_SIZE.' questions from the course question bank. You need 70% to pass and can try 3 times.</p>',
                'max_attempts' => 3,
                'time_limit_minutes' => 20,
                'passing_score' => 70,
                'xp_reward' => 50,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => true,
                'allow_review' => true,
                'is_randomized' => true,
                'shuffle_options' => true,
                'is_locked' => false,
                'questions_per_attempt' => null,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    private function seedFinalExam(Lesson $lesson): Assessment
    {
        return Assessment::updateOrCreate(
            ['course_id' => $lesson->course_id, 'lesson_id' => $lesson->id, 'assessment_type' => 'quiz'],
            [
                'title' => 'Final Exam: AI & Machine Learning with mBlock',
                'description' => '<p>The final exam draws <strong>'.self::EXAM_QUESTIONS_PER_ATTEMPT.' random questions</strong> from the whole course question bank, so every attempt is different. You have 60 minutes and 2 attempts. Pass mark: 70%.</p>',
                'max_attempts' => 2,
                'time_limit_minutes' => 60,
                'passing_score' => 70,
                'xp_reward' => 200,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => false,
                'allow_review' => true,
                'is_randomized' => true,
                'shuffle_options' => true,
                'is_locked' => false,
                'questions_per_attempt' => self::EXAM_QUESTIONS_PER_ATTEMPT,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    /**
     * @return array<int, int> question ids in quiz order
     */
    private function seedBankQuestions(QuestionWriter $writer, array $rows, int $lessonNumber, Course $course, CourseModule $module, User $instructor): array
    {
        $ids = [];

        foreach (array_slice($rows, 0, self::QUIZ_SIZE) as $index => $row) {
            [$kind, $difficulty, $text, $answer, $explanation] = $row;
            $key = sprintf('aiml-L%d-Q%02d', $lessonNumber, $index + 1);

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
                array_merge(self::BASE_TAGS, ['lesson-'.$lessonNumber]),
                [['course_id' => $course->id, 'course_module_id' => $module->id]],
            );

            $ids[] = $question->id;
        }

        return $ids;
    }

    /**
     * Options are shuffled with a seed derived from the question key so the order is stable
     * across re-runs, and existing option ids are reused by text so stored answers stay valid.
     *
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
        $fileTypes = $brief['file_types'];

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
                    'file_types' => $fileTypes,
                    'max_file_size' => 20,
                    'max_points' => 100,
                    'allow_text' => true,
                    'allow_files' => true,
                    'rubric' => [
                        'Model is trained and works (evidence shown)' => 35,
                        'Program uses the model correctly' => 30,
                        'Testing notes and explanation' => 20,
                        'Creativity and presentation' => 15,
                    ],
                ],
            ]
        );

        $this->upsertAssignmentQuestion($assessment, 'short_answer', [
            'question_text' => $brief['prompt_question'],
            'points' => 20,
            'order' => 1,
            'explanation' => 'Your teacher reads this while grading your upload.',
            'settings' => ['min_words' => 0, 'max_words' => 500],
        ]);

        $this->upsertAssignmentQuestion($assessment, 'file_upload', [
            'question_text' => $brief['upload_question'],
            'points' => 80,
            'order' => 2,
            'explanation' => 'Upload your .mblock project and screenshots or photos as evidence of your work.',
            'settings' => [
                'allowed_types' => implode(',', $fileTypes),
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

    private function resolveInstructor(): ?User
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->orderBy('id')->first()
            ?? User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['teacher', 'ict_teacher']);
            })->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
    }
}
