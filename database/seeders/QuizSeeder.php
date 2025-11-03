<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lessons = Lesson::where('is_published', true)->get();

        if ($lessons->isEmpty()) {
            $this->command->warn('No published lessons found. Please run CourseSeeder first.');
            return;
        }

        $quizCount = 0;

        foreach ($lessons->take(10) as $lesson) {
            // Skip if quiz already exists for this lesson
            if (Quiz::where('lesson_id', $lesson->id)->exists()) {
                continue;
            }

            $quiz = Quiz::create([
                'lesson_id' => $lesson->id,
                'title' => $lesson->title . ' - Quiz',
                'description' => 'Test your understanding of the concepts covered in this lesson.',
                'instructions' => 'Read each question carefully and select the best answer. You have ' . rand(15, 45) . ' minutes to complete this quiz.',
                'time_limit' => rand(15, 45), // minutes
                'max_attempts' => rand(2, 5),
                'passing_score' => rand(60, 80),
                'is_randomized' => rand(0, 1) === 1,
                'is_published' => true,
                'show_correct_answers' => true,
                'allow_review' => true,
            ]);

            // Create questions for the quiz
            $numberOfQuestions = rand(5, 10);
            
            for ($i = 0; $i < $numberOfQuestions; $i++) {
                $questionType = ['multiple_choice', 'true_false', 'short_answer'][rand(0, 2)];
                
                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $this->generateQuestionText($lesson->title, $i + 1),
                    'question_type' => $questionType,
                    'points' => rand(5, 20),
                    'order' => $i,
                    'explanation' => 'This is the correct answer based on the lesson content.',
                ]);

                // Create options for multiple choice and true/false questions
                if ($questionType === 'multiple_choice') {
                    $options = [
                        ['option_text' => 'Correct Answer', 'is_correct' => true],
                        ['option_text' => 'Incorrect Option 1', 'is_correct' => false],
                        ['option_text' => 'Incorrect Option 2', 'is_correct' => false],
                        ['option_text' => 'Incorrect Option 3', 'is_correct' => false],
                    ];
                    
                    // Shuffle options
                    shuffle($options);
                    
                    foreach ($options as $optIndex => $optionData) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'order' => $optIndex,
                        ]);
                    }
                } elseif ($questionType === 'true_false') {
                    $isTrue = rand(0, 1) === 1;
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'True',
                        'is_correct' => $isTrue,
                        'order' => 0,
                    ]);
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'False',
                        'is_correct' => !$isTrue,
                        'order' => 1,
                    ]);
                }
            }

            $quizCount++;
        }

        $this->command->info("Created {$quizCount} quizzes with questions.");
    }

    private function generateQuestionText(string $lessonTitle, int $number): string
    {
        $questionTemplates = [
            "What is the main concept discussed in {$lessonTitle}?",
            "Which of the following best describes the topic covered in {$lessonTitle}?",
            "What is the primary purpose of {$lessonTitle}?",
            "Which statement is most accurate about {$lessonTitle}?",
            "What key principle is demonstrated in {$lessonTitle}?",
            "What is the relationship between the concepts in {$lessonTitle}?",
            "Which technique is most commonly used in {$lessonTitle}?",
            "What would be the expected outcome when applying {$lessonTitle}?",
        ];

        return $questionTemplates[array_rand($questionTemplates)];
    }
}

