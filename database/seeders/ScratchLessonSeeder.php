<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class ScratchLessonSeeder extends Seeder
{
    public function run(): void
    {
        // Find or create a course for Scratch lessons
        $course = Course::where('title', 'LIKE', '%Scratch%')->first();
        
        if (!$course) {
            echo "No Scratch course found. Please create a course first.\n";
            return;
        }

        // Find or create a module
        $module = $course->modules()->first();
        
        if (!$module) {
            $module = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Getting Started with Scratch',
                'description' => 'Learn the basics of Scratch programming',
                'order_index' => 1,
            ]);
        }

        // Create a visual Scratch lesson
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Make Your Sprite Move',
            'slug' => 'make-your-sprite-move',
            'lesson_type' => 'interactive',
            'difficulty_level' => 'beginner',
            'duration_minutes' => 30,
            'summary' => 'Learn how to make your sprite move around the stage using motion blocks',
            'content' => '<p>In this lesson, you\'ll learn how to control sprite movement in Scratch!</p>',
            'objectives' => "• Understand motion blocks\n• Make sprites move in different directions\n• Use coordinates to position sprites\n• Create smooth animations",
            'order_index' => 1,
            'is_published' => true,
            'is_active' => true,
            'approval_status' => 'approved',
            
            // Visual component data
            'scratch_project_id' => '1234567890', // Example Scratch project ID
            
            'lesson_steps' => [
                [
                    'title' => 'Add a Sprite',
                    'description' => 'Click on the "Choose a Sprite" button and select your favorite character.',
                    'image' => null,
                    'code' => null,
                ],
                [
                    'title' => 'Drag the Move Block',
                    'description' => 'Find the blue "move 10 steps" block in the Motion category and drag it to the coding area.',
                    'image' => null,
                    'code' => null,
                ],
                [
                    'title' => 'Click the Block',
                    'description' => 'Click on the move block to see your sprite move! Try changing the number to make it move further.',
                    'image' => null,
                    'code' => null,
                ],
                [
                    'title' => 'Add a Turn Block',
                    'description' => 'Now add a "turn 15 degrees" block below the move block. Click to see your sprite turn!',
                    'image' => null,
                    'code' => null,
                ],
            ],
            
            'scratch_blocks' => [
                [
                    'category' => 'motion',
                    'text' => 'move (10) steps',
                ],
                [
                    'category' => 'motion',
                    'text' => 'turn ↻ (15) degrees',
                ],
                [
                    'category' => 'motion',
                    'text' => 'go to x: (0) y: (0)',
                ],
                [
                    'category' => 'events',
                    'text' => 'when 🏴 clicked',
                ],
                [
                    'category' => 'control',
                    'text' => 'repeat (10)',
                ],
            ],
        ]);

        echo "Created Scratch lesson: {$lesson->title} (ID: {$lesson->id})\n";
        echo "View it at: /lessons/{$lesson->id}/view\n";
    }
}
