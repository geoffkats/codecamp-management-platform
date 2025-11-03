<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuizAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run UserSeeder first.');
            return;
        }

        $quizzes = Quiz::where('is_published', true)->get();

        if ($quizzes->isEmpty()) {
            $this->command->warn('No published quizzes found. Please run QuizSeeder first.');
            return;
        }

        $attemptCount = 0;

        foreach ($students as $student) {
            // Each student attempts 3-6 random quizzes
            $studentQuizzes = $quizzes->random(rand(3, min(6, $quizzes->count())));

            foreach ($studentQuizzes as $quiz) {
                // Create 1-3 attempts per quiz (some may be incomplete)
                $numberOfAttempts = rand(1, min(3, $quiz->max_attempts));

                for ($attemptNum = 1; $attemptNum <= $numberOfAttempts; $attemptNum++) {
                    $score = rand(50, 100);
                    $isPassed = $score >= $quiz->passing_score;
                    $isCompleted = rand(0, 10) > 2; // 80% completion rate

                    $startedAt = now()->subDays(rand(1, 30))->subHours(rand(0, 23));
                    $completedAt = $isCompleted ? $startedAt->copy()->addMinutes(rand(5, $quiz->time_limit ?? 45)) : null;

                    QuizAttempt::create([
                        'user_id' => $student->id,
                        'quiz_id' => $quiz->id,
                        'attempt_number' => $attemptNum,
                        'started_at' => $startedAt,
                        'completed_at' => $completedAt,
                        'score' => $isCompleted ? $score : 0.00,
                        'is_passed' => $isCompleted && $isPassed,
                        'answers' => $isCompleted ? $this->generateAnswers($quiz) : null,
                        'created_at' => $startedAt,
                        'updated_at' => $completedAt ?? $startedAt,
                    ]);

                    $attemptCount++;
                }
            }
        }

        $this->command->info("Created {$attemptCount} quiz attempts.");
    }

    private function generateAnswers($quiz): array
    {
        $answers = [];
        $questions = $quiz->questions()->with('options')->get();

        foreach ($questions as $question) {
            if ($question->question_type === 'multiple_choice' || $question->question_type === 'true_false') {
                $correctOptions = $question->options()->where('is_correct', true)->get();
                if ($correctOptions->isNotEmpty()) {
                    // 70% chance of selecting correct answer
                    if (rand(0, 100) < 70) {
                        $answers[$question->id] = $correctOptions->random()->id;
                    } else {
                        $wrongOptions = $question->options()->where('is_correct', false)->get();
                        if ($wrongOptions->isNotEmpty()) {
                            $answers[$question->id] = $wrongOptions->random()->id;
                        }
                    }
                }
            } else {
                $answers[$question->id] = 'Sample answer text';
            }
        }

        return $answers;
    }
}

