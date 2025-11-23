<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lessons = Lesson::where('is_published', true)->with('course')->get();

        if ($lessons->isEmpty()) {
            $this->command->warn('No published lessons found. Please run CourseSeeder first.');
            return;
        }

        $this->command->info('Creating comprehensive assessments for all lessons...');

        foreach ($lessons as $lesson) {
            $course = $lesson->course;
            $module = $lesson->module;

            // Create quiz assessment for most lessons
            $this->createQuizAssessment($lesson);

            // Create assignments for interactive and assignment-type lessons
            if (in_array($lesson->lesson_type, ['interactive', 'assignment'])) {
                $this->createAssignmentAssessment($lesson);
            }

            // Create pre-project test at the start of modules
            if ($module && $lesson->order_index == 0) {
                $this->createPreProjectTest($lesson);
            }

            // Create post-project test at the end of modules
            if ($module) {
                $moduleLessons = Lesson::where('module_id', $module->id)
                    ->orderBy('order_index', 'desc')
                    ->first();
                if ($lesson->id === $moduleLessons->id) {
                    $this->createPostProjectTest($lesson);
                }
            }

            // Create unit survey for some lessons
            if (rand(1, 3) === 1) {
                $this->createUnitSurvey($lesson);
            }

            // Create rubric assessment for advanced lessons
            if (in_array($course->difficulty_level, ['Advanced', 'Intermediate'])) {
                if (rand(1, 4) === 1) {
                    $this->createRubricAssessment($lesson);
                }
            }
        }

        // Create peer review assessments for group projects
        $this->createPeerReviewAssessments();

        // Create self-assessment for reflection lessons
        $this->createSelfAssessments();

        $this->command->info('✅ Assessments created successfully!');
    }

    /**
     * Create quiz assessment with diverse question types
     */
    private function createQuizAssessment(Lesson $lesson): void
    {
        $assessment = Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $lesson->title . ' - Quiz',
                'assessment_type' => 'quiz',
            ],
            [
                'description' => 'Test your understanding of ' . $lesson->title . '. This quiz includes multiple question types to evaluate your knowledge.',
                'max_attempts' => 3,
                'time_limit_minutes' => 30,
                'passing_score' => 70,
                'xp_reward' => 50,
                'is_required' => true,
                'show_results_immediately' => true,
                'is_locked' => false,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        if (!$assessment->wasRecentlyCreated && Question::where('assessment_id', $assessment->id)->exists()) {
            return;
        }

        $questions = $this->generateQuizQuestions($lesson);
        
        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order' => $index,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            foreach ($questionData['options'] ?? [] as $optIndex => $optionData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $optIndex,
                ]);
            }
        }
    }

    /**
     * Generate diverse quiz questions based on lesson
     */
    private function generateQuizQuestions(Lesson $lesson): array
    {
        $title = $lesson->title;
        $courseTitle = $lesson->course->title;
        $questions = [];

        // Check if this is Web Development 1 course and generate specific questions
        if ($courseTitle === 'Web Development 1') {
            return $this->generateWebDev1Questions($lesson);
        }

        // Multiple Choice Question 1
        $questions[] = [
            'question_text' => 'What is the primary purpose of ' . $title . '?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'explanation' => 'This concept is fundamental to understanding the lesson topic.',
            'options' => [
                ['option_text' => $this->getCorrectAnswer($title, $courseTitle), 'is_correct' => true],
                ['option_text' => $this->getIncorrectAnswer($title, $courseTitle), 'is_correct' => false],
                ['option_text' => $this->getIncorrectAnswer($title, $courseTitle), 'is_correct' => false],
                ['option_text' => $this->getIncorrectAnswer($title, $courseTitle), 'is_correct' => false],
            ]
        ];

        // True/False Question
        $questions[] = [
            'question_text' => 'True or False: ' . $this->getTrueFalseStatement($title, $courseTitle),
            'question_type' => 'true_false',
            'points' => 5,
            'explanation' => 'Review the lesson content to verify this statement.',
            'options' => [
                ['option_text' => 'True', 'is_correct' => true],
                ['option_text' => 'False', 'is_correct' => false],
            ]
        ];

        // Fill in the Blank
        $questions[] = [
            'question_text' => 'Complete the sentence: ' . $this->getFillBlankQuestion($title, $courseTitle),
            'question_type' => 'fill_blank',
            'points' => 10,
            'explanation' => 'The correct answer should match the key concept from the lesson.',
            'options' => [
                ['option_text' => $this->getFillBlankAnswer($title, $courseTitle), 'is_correct' => true],
                ['option_text' => $this->getFillBlankAnswer($title, $courseTitle, true), 'is_correct' => false],
            ]
        ];

        // Short Answer
        $questions[] = [
            'question_text' => 'Explain briefly: What are the key benefits of learning ' . $title . '?',
            'question_type' => 'short_answer',
            'points' => 15,
            'explanation' => 'Your answer should mention at least 2-3 key benefits discussed in the lesson.',
            'options' => []
        ];

        // Multiple Choice with Multiple Correct Answers
        $questions[] = [
            'question_text' => 'Which of the following are important concepts in ' . $title . '? (Select all that apply)',
            'question_type' => 'multiple_choice',
            'points' => 15,
            'explanation' => 'Review the lesson content to identify all relevant concepts.',
            'options' => [
                ['option_text' => $this->getConcept($title, $courseTitle), 'is_correct' => true],
                ['option_text' => $this->getConcept($title, $courseTitle), 'is_correct' => true],
                ['option_text' => $this->getIncorrectAnswer($title, $courseTitle), 'is_correct' => false],
                ['option_text' => $this->getConcept($title, $courseTitle), 'is_correct' => true],
            ]
        ];

        // Matching Question (if applicable)
        if (strpos(strtolower($title), 'introduction') === false) {
            $questions[] = [
                'question_text' => 'Match the following terms with their definitions in the context of ' . $title . ':',
                'question_type' => 'matching',
                'points' => 12,
                'explanation' => 'Carefully review the lesson to match each term with its correct definition.',
                'options' => [
                    ['option_text' => 'Term A → Definition A', 'is_correct' => true],
                    ['option_text' => 'Term B → Definition B', 'is_correct' => true],
                    ['option_text' => 'Term C → Definition C', 'is_correct' => true],
                    ['option_text' => 'Term D → Definition D', 'is_correct' => true],
                ]
            ];
        }

        // Ordering Question
        $questions[] = [
            'question_text' => 'Arrange the following steps in the correct order for ' . $title . ':',
            'question_type' => 'ordering',
            'points' => 10,
            'explanation' => 'Follow the logical sequence presented in the lesson.',
            'options' => [
                ['option_text' => 'Step 1: Initial setup', 'is_correct' => true],
                ['option_text' => 'Step 2: Configuration', 'is_correct' => true],
                ['option_text' => 'Step 3: Implementation', 'is_correct' => true],
                ['option_text' => 'Step 4: Testing', 'is_correct' => true],
            ]
        ];

        // Essay Question
        $questions[] = [
            'question_text' => 'In your own words, explain the main concepts covered in ' . $title . ' and how they relate to ' . $courseTitle . '.',
            'question_type' => 'essay',
            'points' => 20,
            'explanation' => 'Provide a comprehensive answer covering all main points from the lesson.',
            'options' => []
        ];

        return $questions;
    }

    /**
     * Helper methods to generate question content
     */
    private function getCorrectAnswer(string $title, string $courseTitle): string
    {
        $answers = [
            'To provide a comprehensive understanding of the topic',
            'To master the fundamental concepts and practical applications',
            'To develop practical skills through hands-on learning',
            'To understand the core principles and their real-world usage',
        ];
        return $answers[array_rand($answers)];
    }

    private function getIncorrectAnswer(string $title, string $courseTitle): string
    {
        $answers = [
            'To memorize facts without understanding',
            'To skip the basics and jump to advanced topics',
            'To learn only theoretical concepts',
            'To complete without practice',
        ];
        return $answers[array_rand($answers)];
    }

    private function getTrueFalseStatement(string $title, string $courseTitle): string
    {
        $statements = [
            'This topic requires prior knowledge in related areas.',
            'Practice is essential for mastering this concept.',
            'This lesson builds upon previous lessons in the course.',
            'Understanding this topic is crucial for advanced topics.',
        ];
        return $statements[array_rand($statements)];
    }

    private function getFillBlankQuestion(string $title, string $courseTitle): string
    {
        $templates = [
            'The main goal of ' . $title . ' is to teach you about _____ .',
            'When working with ' . $title . ', you need to understand _____ .',
            'The key concept in ' . $title . ' involves _____ .',
        ];
        return $templates[array_rand($templates)];
    }

    private function getFillBlankAnswer(string $title, string $courseTitle, bool $wrong = false): string
    {
        if ($wrong) {
            $answers = ['Basic concepts', 'Simple techniques', 'Fundamental principles'];
        } else {
            $answers = ['Core principles', 'Key techniques', 'Essential concepts', 'Fundamental methods'];
        }
        return $answers[array_rand($answers)];
    }

    private function getConcept(string $title, string $courseTitle): string
    {
        $concepts = [
            'Understanding the fundamentals',
            'Practical application',
            'Best practices',
            'Common patterns',
            'Key principles',
            'Implementation strategies',
        ];
        return $concepts[array_rand($concepts)];
    }

    /**
     * Create assignment assessment
     */
    private function createAssignmentAssessment(Lesson $lesson): void
    {
        Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $lesson->title . ' - Practical Assignment',
                'assessment_type' => 'assignment',
            ],
            [
                'description' => 'Complete this practical assignment based on ' . $lesson->title . '. Apply what you\'ve learned to create a real project.',
                'max_attempts' => 2,
                'passing_score' => 60,
                'xp_reward' => 100,
                'is_required' => true,
                'assignment_data' => [
                    'instructions' => 'Create a project that demonstrates your understanding of ' . $lesson->title . '. Follow the guidelines provided in the lesson and ensure your work is original and well-documented.',
                    'submission_format' => 'file',
                    'file_types' => ['pdf', 'doc', 'docx', 'zip'],
                    'max_file_size' => 10,
                    'rubric' => [
                        'Functionality' => 40,
                        'Code Quality' => 30,
                        'Documentation' => 20,
                        'Creativity' => 10,
                    ],
                ],
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    /**
     * Create pre-project test
     */
    private function createPreProjectTest(Lesson $lesson): void
    {
        $assessment = Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => 'Pre-Project Test: ' . $lesson->module->title ?? 'Module Assessment',
                'assessment_type' => 'pre_project_test',
            ],
            [
                'description' => 'This assessment evaluates your readiness before starting the project in this module. Make sure you understand the prerequisite concepts.',
                'max_attempts' => 2,
                'time_limit_minutes' => 45,
                'passing_score' => 75,
                'xp_reward' => 75,
                'is_required' => true,
                'show_results_immediately' => true,
                'project_test_data' => [
                    'test_type' => 'pre_assessment',
                    'focus_areas' => ['Foundational concepts', 'Prerequisite knowledge', 'Readiness check'],
                ],
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Add questions to pre-project test
        if ($assessment->wasRecentlyCreated) {
            $this->addPrePostTestQuestions($assessment, $lesson, true);
        }
    }

    /**
     * Create post-project test
     */
    private function createPostProjectTest(Lesson $lesson): void
    {
        $assessment = Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => 'Post-Project Test: ' . $lesson->module->title ?? 'Module Assessment',
                'assessment_type' => 'post_project_test',
            ],
            [
                'description' => 'This comprehensive test evaluates your understanding after completing the module project. It covers all concepts learned throughout the module.',
                'max_attempts' => 2,
                'time_limit_minutes' => 60,
                'passing_score' => 75,
                'xp_reward' => 100,
                'is_required' => true,
                'show_results_immediately' => true,
                'project_test_data' => [
                    'test_type' => 'post_assessment',
                    'focus_areas' => ['Module concepts', 'Project integration', 'Advanced understanding'],
                ],
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Add questions to post-project test
        if ($assessment->wasRecentlyCreated) {
            $this->addPrePostTestQuestions($assessment, $lesson, false);
        }
    }

    /**
     * Add questions to pre/post project tests
     */
    private function addPrePostTestQuestions(Assessment $assessment, Lesson $lesson, bool $isPreTest): void
    {
        $questionText = $isPreTest 
            ? 'Before starting this module, you should understand:'
            : 'After completing this module, you should be able to:';

        $questions = [
            [
                'question_text' => $questionText . ' What are the fundamental concepts required?',
                'question_type' => 'multiple_choice',
                'points' => 15,
                'explanation' => 'Review the module objectives and prerequisite materials.',
                'options' => [
                    ['option_text' => 'Core foundational concepts', 'is_correct' => true],
                    ['option_text' => 'Advanced techniques only', 'is_correct' => false],
                    ['option_text' => 'Optional supplementary material', 'is_correct' => false],
                    ['option_text' => 'Previous module completion', 'is_correct' => false],
                ]
            ],
            [
                'question_text' => 'Explain how this module builds upon previous knowledge.',
                'question_type' => 'essay',
                'points' => 25,
                'explanation' => 'Provide a detailed explanation connecting this module to previous concepts.',
                'options' => []
            ],
            [
                'question_text' => 'What practical skills will you develop in this module?',
                'question_type' => 'short_answer',
                'points' => 20,
                'explanation' => 'List at least 3 practical skills you will gain.',
                'options' => []
            ],
        ];

        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order' => $index,
                'explanation' => $questionData['explanation'],
            ]);

            foreach ($questionData['options'] ?? [] as $optIndex => $optionData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $optIndex,
                ]);
            }
        }
    }

    /**
     * Create unit survey
     */
    private function createUnitSurvey(Lesson $lesson): void
    {
        Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $lesson->title . ' - Learning Survey',
                'assessment_type' => 'unit_survey',
            ],
            [
                'description' => 'Help us improve by sharing your feedback on this lesson. Your responses help us enhance the learning experience.',
                'max_attempts' => 1,
                'passing_score' => 0, // Surveys don't have passing scores
                'xp_reward' => 10,
                'is_required' => false,
                'show_results_immediately' => false,
                'survey_data' => [
                    'questions' => [
                        [
                            'question' => 'How would you rate the clarity of this lesson?',
                            'type' => 'rating',
                            'scale' => 5,
                        ],
                        [
                            'question' => 'Was the lesson content at an appropriate difficulty level?',
                            'type' => 'choice',
                            'options' => ['Too easy', 'Just right', 'Too difficult'],
                        ],
                        [
                            'question' => 'What did you find most helpful?',
                            'type' => 'text',
                        ],
                        [
                            'question' => 'Suggestions for improvement:',
                            'type' => 'text',
                        ],
                    ],
                ],
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    /**
     * Create rubric assessment
     */
    private function createRubricAssessment(Lesson $lesson): void
    {
        Assessment::firstOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $lesson->title . ' - Rubric Assessment',
                'assessment_type' => 'rubric_assessment',
            ],
            [
                'description' => 'This assessment uses a detailed rubric to evaluate your work on ' . $lesson->title . '.',
                'max_attempts' => 1,
                'passing_score' => 70,
                'xp_reward' => 120,
                'is_required' => true,
                'show_results_immediately' => false,
                'rubric_criteria' => [
                    [
                        'criterion' => 'Understanding of Concepts',
                        'description' => 'Demonstrates clear understanding of key concepts',
                        'points' => 30,
                        'levels' => [
                            ['level' => 'Excellent', 'points' => 30, 'description' => 'Complete understanding with excellent examples'],
                            ['level' => 'Good', 'points' => 20, 'description' => 'Good understanding with some examples'],
                            ['level' => 'Satisfactory', 'points' => 10, 'description' => 'Basic understanding demonstrated'],
                            ['level' => 'Needs Improvement', 'points' => 0, 'description' => 'Limited understanding shown'],
                        ],
                    ],
                    [
                        'criterion' => 'Application of Skills',
                        'description' => 'Effectively applies learned skills',
                        'points' => 30,
                        'levels' => [
                            ['level' => 'Excellent', 'points' => 30, 'description' => 'Skillful application with innovation'],
                            ['level' => 'Good', 'points' => 20, 'description' => 'Competent application'],
                            ['level' => 'Satisfactory', 'points' => 10, 'description' => 'Basic application'],
                            ['level' => 'Needs Improvement', 'points' => 0, 'description' => 'Limited application'],
                        ],
                    ],
                    [
                        'criterion' => 'Quality of Work',
                        'description' => 'Overall quality and completeness',
                        'points' => 25,
                        'levels' => [
                            ['level' => 'Excellent', 'points' => 25, 'description' => 'High quality, polished work'],
                            ['level' => 'Good', 'points' => 15, 'description' => 'Good quality work'],
                            ['level' => 'Satisfactory', 'points' => 8, 'description' => 'Acceptable quality'],
                            ['level' => 'Needs Improvement', 'points' => 0, 'description' => 'Poor quality'],
                        ],
                    ],
                    [
                        'criterion' => 'Documentation',
                        'description' => 'Clear documentation and explanation',
                        'points' => 15,
                        'levels' => [
                            ['level' => 'Excellent', 'points' => 15, 'description' => 'Comprehensive documentation'],
                            ['level' => 'Good', 'points' => 10, 'description' => 'Good documentation'],
                            ['level' => 'Satisfactory', 'points' => 5, 'description' => 'Basic documentation'],
                            ['level' => 'Needs Improvement', 'points' => 0, 'description' => 'Insufficient documentation'],
                        ],
                    ],
                ],
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }

    /**
     * Create peer review assessments
     */
    private function createPeerReviewAssessments(): void
    {
        $groupProjectLessons = Lesson::where('lesson_type', 'assignment')
            ->where('is_published', true)
            ->take(10)
            ->get();

        foreach ($groupProjectLessons as $lesson) {
            Assessment::firstOrCreate(
                [
                    'course_id' => $lesson->course_id,
                    'lesson_id' => $lesson->id,
                    'title' => $lesson->title . ' - Peer Review',
                    'assessment_type' => 'peer_review',
                ],
                [
                    'description' => 'Review your peers\' work on ' . $lesson->title . '. Provide constructive feedback to help them improve.',
                    'max_attempts' => 1,
                    'passing_score' => 0,
                    'xp_reward' => 50,
                    'is_required' => false,
                    'show_results_immediately' => true,
                    'peer_review_data' => [
                        'review_items' => [
                            [
                                'item' => 'Quality of Work',
                                'type' => 'rating',
                                'scale' => 5,
                            ],
                            [
                                'item' => 'Creativity',
                                'type' => 'rating',
                                'scale' => 5,
                            ],
                            [
                                'item' => 'Technical Correctness',
                                'type' => 'rating',
                                'scale' => 5,
                            ],
                            [
                                'item' => 'What did you like?',
                                'type' => 'text',
                            ],
                            [
                                'item' => 'Suggestions for improvement',
                                'type' => 'text',
                            ],
                        ],
                        'min_reviews_required' => 2,
                    ],
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]
            );
        }
    }

    /**
     * Create self-assessment assessments
     */
    private function createSelfAssessments(): void
    {
        $reflectionLessons = Lesson::whereIn('lesson_type', ['text', 'video'])
            ->where('is_published', true)
            ->take(15)
            ->get();

        foreach ($reflectionLessons as $lesson) {
            Assessment::firstOrCreate(
                [
                    'course_id' => $lesson->course_id,
                    'lesson_id' => $lesson->id,
                    'title' => $lesson->title . ' - Self-Assessment',
                    'assessment_type' => 'self_assessment',
                ],
                [
                    'description' => 'Reflect on your learning in ' . $lesson->title . '. Assess your own understanding and identify areas for improvement.',
                    'max_attempts' => 1,
                    'passing_score' => 0,
                    'xp_reward' => 30,
                    'is_required' => false,
                    'show_results_immediately' => false,
                    'self_assessment_data' => [
                        'reflection_questions' => [
                            [
                                'question' => 'How well do you understand the concepts covered in this lesson?',
                                'type' => 'rating',
                                'scale' => 5,
                            ],
                            [
                                'question' => 'What was the most challenging part?',
                                'type' => 'text',
                            ],
                            [
                                'question' => 'What concepts do you need to review?',
                                'type' => 'text',
                            ],
                            [
                                'question' => 'How confident are you in applying these concepts?',
                                'type' => 'rating',
                                'scale' => 5,
                            ],
                            [
                                'question' => 'What learning strategies worked best for you?',
                                'type' => 'text',
                            ],
                        ],
                    ],
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]
            );
        }
    }

    /**
     * Generate Web Development 1 specific questions based on lesson title
     */
    private function generateWebDev1Questions(Lesson $lesson): array
    {
        $title = strtolower($lesson->title);
        $questions = [];

        // HTML Questions
        if (strpos($title, 'html') !== false || strpos($title, 'introduction to html') !== false) {
            $questions[] = [
                'question_text' => 'What does HTML stand for?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'HTML stands for HyperText Markup Language, which is the standard markup language for creating web pages.',
                'options' => [
                    ['option_text' => 'HyperText Markup Language', 'is_correct' => true],
                    ['option_text' => 'High Tech Modern Language', 'is_correct' => false],
                    ['option_text' => 'Home Tool Markup Language', 'is_correct' => false],
                    ['option_text' => 'Hyperlink and Text Markup Language', 'is_correct' => false],
                ]
            ];
            $questions[] = [
                'question_text' => 'Which HTML5 element is used to define the main content of a document?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'The <main> element represents the main content of the body of a document.',
                'options' => [
                    ['option_text' => '<main>', 'is_correct' => true],
                    ['option_text' => '<content>', 'is_correct' => false],
                    ['option_text' => '<body>', 'is_correct' => false],
                    ['option_text' => '<section>', 'is_correct' => false],
                ]
            ];
        }

        if (strpos($title, 'semantic') !== false || strpos($title, 'html5 semantic') !== false) {
            $questions[] = [
                'question_text' => 'Which of the following are HTML5 semantic elements? (Select all that apply)',
                'question_type' => 'multiple_choice',
                'points' => 15,
                'explanation' => 'Semantic elements clearly describe their meaning: <header>, <nav>, <article>, <section>, <aside>, and <footer>.',
                'options' => [
                    ['option_text' => '<header>', 'is_correct' => true],
                    ['option_text' => '<nav>', 'is_correct' => true],
                    ['option_text' => '<article>', 'is_correct' => true],
                    ['option_text' => '<div>', 'is_correct' => false],
                ]
            ];
        }

        if (strpos($title, 'form') !== false || strpos($title, 'input') !== false) {
            $questions[] = [
                'question_text' => 'Which input type is used for email addresses with built-in validation?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'The type="email" input provides built-in email validation in modern browsers.',
                'options' => [
                    ['option_text' => 'type="email"', 'is_correct' => true],
                    ['option_text' => 'type="text"', 'is_correct' => false],
                    ['option_text' => 'type="mail"', 'is_correct' => false],
                    ['option_text' => 'type="address"', 'is_correct' => false],
                ]
            ];
        }

        // CSS Questions
        if (strpos($title, 'css') !== false || strpos($title, 'styling') !== false) {
            $questions[] = [
                'question_text' => 'What does CSS stand for?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'CSS stands for Cascading Style Sheets, used to style HTML elements.',
                'options' => [
                    ['option_text' => 'Cascading Style Sheets', 'is_correct' => true],
                    ['option_text' => 'Computer Style Sheets', 'is_correct' => false],
                    ['option_text' => 'Creative Style System', 'is_correct' => false],
                    ['option_text' => 'Colorful Style Sheets', 'is_correct' => false],
                ]
            ];
        }

        if (strpos($title, 'box model') !== false || strpos($title, 'margin') !== false || strpos($title, 'padding') !== false) {
            $questions[] = [
                'question_text' => 'In the CSS box model, what is the correct order from outside to inside?',
                'question_type' => 'ordering',
                'points' => 15,
                'explanation' => 'The box model order is: margin (outside) → border → padding → content (inside).',
                'options' => [
                    ['option_text' => 'Margin', 'is_correct' => true],
                    ['option_text' => 'Border', 'is_correct' => true],
                    ['option_text' => 'Padding', 'is_correct' => true],
                    ['option_text' => 'Content', 'is_correct' => true],
                ]
            ];
        }

        if (strpos($title, 'flexbox') !== false) {
            $questions[] = [
                'question_text' => 'Which CSS property is used to create a flexbox container?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'display: flex; creates a flex container and enables flexbox layout.',
                'options' => [
                    ['option_text' => 'display: flex;', 'is_correct' => true],
                    ['option_text' => 'display: grid;', 'is_correct' => false],
                    ['option_text' => 'display: block;', 'is_correct' => false],
                    ['option_text' => 'flex: 1;', 'is_correct' => false],
                ]
            ];
        }

        if (strpos($title, 'grid') !== false && strpos($title, 'bootstrap') === false) {
            $questions[] = [
                'question_text' => 'Which property is used to define grid columns in CSS Grid?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'grid-template-columns defines the number and size of columns in a grid.',
                'options' => [
                    ['option_text' => 'grid-template-columns', 'is_correct' => true],
                    ['option_text' => 'grid-columns', 'is_correct' => false],
                    ['option_text' => 'columns', 'is_correct' => false],
                    ['option_text' => 'grid-col', 'is_correct' => false],
                ]
            ];
        }

        if (strpos($title, 'responsive') !== false || strpos($title, 'media query') !== false) {
            $questions[] = [
                'question_text' => 'What is the mobile-first approach in responsive design?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'Mobile-first means designing for mobile devices first, then adding styles for larger screens using min-width media queries.',
                'options' => [
                    ['option_text' => 'Designing for mobile devices first, then enhancing for larger screens', 'is_correct' => true],
                    ['option_text' => 'Designing only for mobile devices', 'is_correct' => false],
                    ['option_text' => 'Designing for desktop first', 'is_correct' => false],
                    ['option_text' => 'Using only mobile-friendly fonts', 'is_correct' => false],
                ]
            ];
        }

        // Bootstrap Questions
        if (strpos($title, 'bootstrap') !== false) {
            $questions[] = [
                'question_text' => 'What is Bootstrap?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'Bootstrap is a free and open-source CSS framework for developing responsive, mobile-first websites.',
                'options' => [
                    ['option_text' => 'A CSS framework for building responsive websites', 'is_correct' => true],
                    ['option_text' => 'A JavaScript library', 'is_correct' => false],
                    ['option_text' => 'A database system', 'is_correct' => false],
                    ['option_text' => 'A programming language', 'is_correct' => false],
                ]
            ];
            $questions[] = [
                'question_text' => 'Which Bootstrap class creates a responsive container?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'The .container class creates a responsive fixed-width container.',
                'options' => [
                    ['option_text' => '.container', 'is_correct' => true],
                    ['option_text' => '.wrapper', 'is_correct' => false],
                    ['option_text' => '.box', 'is_correct' => false],
                    ['option_text' => '.responsive', 'is_correct' => false],
                ]
            ];
            $questions[] = [
                'question_text' => 'True or False: Bootstrap uses a 12-column grid system.',
                'question_type' => 'true_false',
                'points' => 5,
                'explanation' => 'Bootstrap uses a 12-column responsive grid system that can be divided into different column sizes.',
                'options' => [
                    ['option_text' => 'True', 'is_correct' => true],
                    ['option_text' => 'False', 'is_correct' => false],
                ]
            ];
        }

        // Project-based questions
        if (strpos($title, 'project') !== false) {
            $questions[] = [
                'question_text' => 'What are the key elements of a well-structured HTML page? (Select all that apply)',
                'question_type' => 'multiple_choice',
                'points' => 15,
                'explanation' => 'A well-structured page includes semantic HTML, proper headings hierarchy, organized sections, and accessibility features.',
                'options' => [
                    ['option_text' => 'Semantic HTML5 elements', 'is_correct' => true],
                    ['option_text' => 'Proper heading hierarchy (h1, h2, h3)', 'is_correct' => true],
                    ['option_text' => 'Organized sections', 'is_correct' => true],
                    ['option_text' => 'Inline styles only', 'is_correct' => false],
                ]
            ];
        }

        // If no specific questions matched, add general questions
        if (empty($questions)) {
            $questions[] = [
                'question_text' => 'What is the main topic covered in this lesson?',
                'question_type' => 'short_answer',
                'points' => 15,
                'explanation' => 'Review the lesson title and content to identify the main topic.',
                'options' => []
            ];
            $questions[] = [
                'question_text' => 'True or False: This lesson is important for building web development skills.',
                'question_type' => 'true_false',
                'points' => 5,
                'explanation' => 'All lessons in Web Development 1 are important for building foundational skills.',
                'options' => [
                    ['option_text' => 'True', 'is_correct' => true],
                    ['option_text' => 'False', 'is_correct' => false],
                ]
            ];
        }

        // Always add a practical application question
        $questions[] = [
            'question_text' => 'How would you apply the concepts from this lesson in a real-world project?',
            'question_type' => 'essay',
            'points' => 20,
            'explanation' => 'Think about practical applications and provide specific examples of how you would use these concepts.',
            'options' => []
        ];

        return $questions;
    }
}
