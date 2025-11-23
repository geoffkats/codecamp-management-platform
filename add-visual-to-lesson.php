<?php

/**
 * Quick script to add visual components to an existing lesson
 * 
 * Usage: php add-visual-to-lesson.php <lesson_id>
 * Example: php add-visual-to-lesson.php 4
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lesson;

if ($argc < 2) {
    echo "Usage: php add-visual-to-lesson.php <lesson_id>\n";
    echo "Example: php add-visual-to-lesson.php 4\n";
    exit(1);
}

$lessonId = $argv[1];

$lesson = Lesson::find($lessonId);

if (!$lesson) {
    echo "Lesson with ID {$lessonId} not found!\n";
    exit(1);
}

echo "Found lesson: {$lesson->title}\n";
echo "Current lesson type: {$lesson->lesson_type}\n\n";

// Update the lesson with visual components
$lesson->update([
    'lesson_type' => 'interactive',
    'scratch_project_id' => '1234567890', // Replace with real Scratch project ID
    'lesson_steps' => [
        [
            'title' => 'Step 1: Open Scratch',
            'description' => 'Go to scratch.mit.edu and click "Create" to start a new project.',
            'image' => null,
            'code' => null,
        ],
        [
            'title' => 'Step 2: Add Motion Blocks',
            'description' => 'Click on the Motion category (blue) and drag the "move 10 steps" block to the coding area.',
            'image' => null,
            'code' => null,
        ],
        [
            'title' => 'Step 3: Add an Event',
            'description' => 'Click on Events (yellow) and add the "when flag clicked" block on top of your move block.',
            'image' => null,
            'code' => null,
        ],
        [
            'title' => 'Step 4: Test Your Code',
            'description' => 'Click the green flag to see your sprite move! Try changing the number to make it move further.',
            'image' => null,
            'code' => null,
        ],
    ],
    'scratch_blocks' => [
        [
            'category' => 'events',
            'text' => 'when 🏴 clicked',
        ],
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
            'category' => 'control',
            'text' => 'repeat (10)',
        ],
        [
            'category' => 'looks',
            'text' => 'say [Hello!] for (2) seconds',
        ],
    ],
]);

echo "\n✅ Successfully added visual components to lesson!\n\n";
echo "Lesson Details:\n";
echo "- ID: {$lesson->id}\n";
echo "- Title: {$lesson->title}\n";
echo "- Type: {$lesson->lesson_type}\n";
echo "- Steps: " . count($lesson->lesson_steps) . "\n";
echo "- Blocks: " . count($lesson->scratch_blocks) . "\n";
echo "\nView the lesson at: /lessons/{$lesson->id}/view\n";
