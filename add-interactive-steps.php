<?php

/**
 * Add interactive step-by-step instructions to any lesson
 * Works for Scratch, Python, Web Development, etc.
 * 
 * Usage: php add-interactive-steps.php <lesson_id> <type>
 * Types: scratch, python, web, general
 * Example: php add-interactive-steps.php 44 web
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lesson;

if ($argc < 2) {
    echo "Usage: php add-interactive-steps.php <lesson_id> [type]\n";
    echo "Types: scratch, python, web, general (default: general)\n";
    echo "Example: php add-interactive-steps.php 44 web\n";
    exit(1);
}

$lessonId = $argv[1];
$type = $argv[2] ?? 'general';

$lesson = Lesson::find($lessonId);

if (!$lesson) {
    echo "Lesson with ID {$lessonId} not found!\n";
    exit(1);
}

echo "Found lesson: {$lesson->title}\n";
echo "Adding {$type} interactive steps...\n\n";

// Define steps based on lesson type
$stepsTemplates = [
    'scratch' => [
        ['title' => 'Open Scratch', 'description' => 'Go to scratch.mit.edu and click "Create" to start a new project.'],
        ['title' => 'Add Your Sprite', 'description' => 'Click "Choose a Sprite" and select a character for your project.'],
        ['title' => 'Add Code Blocks', 'description' => 'Drag blocks from the block palette to the coding area.'],
        ['title' => 'Test Your Project', 'description' => 'Click the green flag to run your code and see it in action!'],
    ],
    'python' => [
        ['title' => 'Open Your Editor', 'description' => 'Open your Python IDE or text editor (VS Code, PyCharm, etc.).'],
        ['title' => 'Create a New File', 'description' => 'Create a new Python file with a .py extension.'],
        ['title' => 'Write Your Code', 'description' => 'Type the Python code following the examples provided.'],
        ['title' => 'Run Your Program', 'description' => 'Save the file and run it using python filename.py in the terminal.'],
    ],
    'web' => [
        ['title' => 'Create HTML File', 'description' => 'Create a new file called index.html in your project folder.'],
        ['title' => 'Add HTML Structure', 'description' => 'Start with the basic HTML5 template structure.'],
        ['title' => 'Add CSS Styling', 'description' => 'Create a styles.css file and link it to your HTML.'],
        ['title' => 'Test in Browser', 'description' => 'Open your HTML file in a web browser to see the results.'],
    ],
    'general' => [
        ['title' => 'Read the Instructions', 'description' => 'Carefully read through all the lesson materials and examples.'],
        ['title' => 'Follow Along', 'description' => 'Try the examples yourself as you go through the lesson.'],
        ['title' => 'Practice', 'description' => 'Complete the practice exercises to reinforce what you learned.'],
        ['title' => 'Review', 'description' => 'Go back and review any concepts that were challenging.'],
    ],
];

$steps = $stepsTemplates[$type] ?? $stepsTemplates['general'];

// Add code blocks based on type
$codeBlocks = [];
if ($type === 'scratch') {
    $codeBlocks = [
        ['category' => 'events', 'text' => 'when 🏴 clicked'],
        ['category' => 'motion', 'text' => 'move (10) steps'],
        ['category' => 'looks', 'text' => 'say [Hello!] for (2) seconds'],
        ['category' => 'control', 'text' => 'repeat (10)'],
    ];
} elseif ($type === 'python') {
    $codeBlocks = [
        ['category' => 'operators', 'text' => 'print("Hello, World!")'],
        ['category' => 'variables', 'text' => 'name = "Student"'],
        ['category' => 'control', 'text' => 'if condition:'],
        ['category' => 'control', 'text' => 'for i in range(10):'],
    ];
} elseif ($type === 'web') {
    $codeBlocks = [
        ['category' => 'looks', 'text' => '<h1>My Website</h1>'],
        ['category' => 'looks', 'text' => '<p>This is a paragraph</p>'],
        ['category' => 'operators', 'text' => 'color: blue;'],
        ['category' => 'operators', 'text' => 'font-size: 20px;'],
    ];
}

// Update the lesson
$lesson->update([
    'lesson_type' => 'interactive',
    'lesson_steps' => $steps,
    'scratch_blocks' => !empty($codeBlocks) ? $codeBlocks : null,
]);

echo "\n✅ Successfully added interactive components!\n\n";
echo "Lesson Details:\n";
echo "- ID: {$lesson->id}\n";
echo "- Title: {$lesson->title}\n";
echo "- Type: {$lesson->lesson_type}\n";
echo "- Steps: " . count($lesson->lesson_steps) . "\n";
if ($lesson->scratch_blocks) {
    echo "- Code Examples: " . count($lesson->scratch_blocks) . "\n";
}
echo "\n🎯 View the lesson at: /lessons/{$lesson->id}/view\n";
echo "\nThe visual step-by-step instructions will appear below the main lesson content!\n";
