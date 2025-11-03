<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::with('modules')->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found. Please run CourseSeeder first.');
            return;
        }

        foreach ($courses as $course) {
            $modules = $course->modules;
            
            if ($modules->isEmpty()) {
                continue;
            }

            foreach ($modules as $module) {
                $lessonsPerModule = rand(3, 6);
                
                // Check if module already has lessons
                if (Lesson::where('module_id', $module->id)->exists()) {
                    continue;
                }

                for ($i = 0; $i < $lessonsPerModule; $i++) {
                    $lessonTypes = ['text', 'video', 'interactive', 'quiz'];
                    $lessonType = $lessonTypes[array_rand($lessonTypes)];
                    
                    $lessonTitle = $this->generateLessonTitle($course->title, $module->title, $i + 1);
                    $lessonSlug = Str::slug($lessonTitle);
                    
                    $lesson = Lesson::firstOrCreate(
                        [
                            'course_id' => $course->id,
                            'slug' => $lessonSlug,
                        ],
                        [
                            'module_id' => $module->id,
                            'title' => $lessonTitle,
                            'content' => $this->generateLessonContent($lessonType),
                            'summary' => 'Learn essential concepts and practical applications in this comprehensive lesson.',
                            'lesson_type' => $lessonType,
                            'difficulty_level' => ['beginner', 'intermediate', 'advanced'][rand(0, 2)],
                            'duration_minutes' => rand(15, 60),
                            'order_index' => $i + 1,
                            'is_published' => $course->is_published,
                            'is_free_preview' => $i === 0, // First lesson is free preview
                            'is_locked' => false,
                            'is_active' => true,
                            'approval_status' => $course->approval_status,
                            'objectives' => $this->generateObjectives(),
                            'implementation_guidance' => 'Follow the step-by-step instructions and practice exercises included in this lesson.',
                        ]
                    );
                }
            }
        }
    }

    private function generateLessonTitle(string $courseTitle, string $moduleTitle, int $index): string
    {
        $topics = [
            'Introduction to',
            'Understanding',
            'Mastering',
            'Exploring',
            'Implementing',
            'Advanced',
            'Practical',
            'Essential',
        ];

        $concepts = [
            'Fundamentals',
            'Core Concepts',
            'Best Practices',
            'Techniques',
            'Strategies',
            'Principles',
            'Methods',
            'Tools',
        ];

        return $topics[array_rand($topics)] . ' ' . $concepts[array_rand($concepts)] . ' - Part ' . $index;
    }

    private function generateLessonContent(string $type): string
    {
        $baseContent = '<h2>Welcome to this Lesson</h2><p>This lesson covers important concepts and practical applications.</p>';

        switch ($type) {
            case 'video':
                return $baseContent . '<div class="video-wrapper"><p>Video content will be displayed here. Watch carefully and take notes.</p></div>';
            case 'quiz':
                return $baseContent . '<p>This lesson includes interactive quiz questions to test your understanding.</p>';
            case 'interactive':
                return $baseContent . '<p>Engage with interactive exercises and hands-on practice in this lesson.</p>';
            default:
                return $baseContent . '<h3>Key Concepts</h3><ul><li>Important concept 1</li><li>Important concept 2</li><li>Important concept 3</li></ul><h3>Practice Exercise</h3><p>Complete the exercises provided to reinforce your learning.</p>';
        }
    }

    private function generateObjectives(): string
    {
        $objectives = [
            'Understand key concepts',
            'Apply learned techniques',
            'Practice with real-world examples',
            'Evaluate your progress',
        ];

        return implode("\n", array_map(fn($obj) => "• {$obj}", $objectives));
    }
}

