<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lesson;
use App\Models\Course;

$courseId = $argv[1] ?? 4;

$course = Course::find($courseId);

if (!$course) {
    echo "Course {$courseId} not found!\n";
    exit(1);
}

echo "Course: {$course->title}\n";
echo "=" . str_repeat("=", strlen($course->title) + 8) . "\n\n";

$lessons = Lesson::where('course_id', $courseId)->get();

if ($lessons->isEmpty()) {
    echo "No lessons found in this course.\n";
    exit(0);
}

echo "Found {$lessons->count()} lesson(s):\n\n";

foreach ($lessons as $lesson) {
    echo "ID: {$lesson->id}\n";
    echo "Title: {$lesson->title}\n";
    echo "Type: {$lesson->lesson_type}\n";
    echo "View at: /lessons/{$lesson->id}/view\n";
    echo str_repeat("-", 50) . "\n";
}

echo "\nTo add visual components to a lesson, run:\n";
echo "php add-visual-to-lesson.php <lesson_id>\n";
