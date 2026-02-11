<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking for duplicate enrollment points...\n\n";

// Find duplicate enrollment points
$duplicates = DB::select("
    SELECT user_id, course_id, type, COUNT(*) as count, SUM(points_earned) as total_points,
           GROUP_CONCAT(id ORDER BY id) as ids
    FROM user_progress
    WHERE type = 'course_enrolled'
    GROUP BY user_id, course_id, type
    HAVING COUNT(*) > 1
    ORDER BY count DESC, user_id
");

if (empty($duplicates)) {
    echo "✅ No duplicate enrollment points found!\n";
} else {
    echo "⚠️ Found " . count($duplicates) . " users with duplicate enrollment points:\n\n";
    
    foreach ($duplicates as $dup) {
        $user = DB::table('users')->find($dup->user_id);
        $course = DB::table('courses')->find($dup->course_id);
        
        echo "User #{$dup->user_id} ({$user->name}): {$dup->count}x enrollment in course #{$dup->course_id} ({$course->title})\n";
        echo "  - Total points from duplicates: {$dup->total_points} XP (should be 50)\n";
        echo "  - Extra points awarded: " . ($dup->total_points - 50) . " XP\n\n";
    }
}

echo "\n" . str_repeat("-", 80) . "\n\n";
echo "Checking for duplicate lesson completion points...\n\n";

// Find duplicate lesson completion points
$lessonDuplicates = DB::select("
    SELECT user_id, lesson_id, type, COUNT(*) as count, SUM(points_earned) as total_points
    FROM user_progress
    WHERE type = 'lesson_completed'
    GROUP BY user_id, lesson_id, type
    HAVING COUNT(*) > 1
    ORDER BY count DESC, user_id
    LIMIT 20
");

if (empty($lessonDuplicates)) {
    echo "✅ No duplicate lesson completion points found!\n";
} else {
    echo "⚠️ Found " . count($lessonDuplicates) . " cases of duplicate lesson completion points (showing first 20):\n\n";
    
    foreach ($lessonDuplicates as $dup) {
        $user = DB::table('users')->find($dup->user_id);
        $lesson = DB::table('lessons')->find($dup->lesson_id);
        
        echo "User #{$dup->user_id} ({$user->name}): {$dup->count}x completion of lesson #{$dup->lesson_id} ({$lesson->title})\n";
        echo "  - Total points from duplicates: {$dup->total_points} XP\n";
        echo "  - Extra points awarded: " . ($dup->total_points - ($dup->total_points / $dup->count)) . " XP\n\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n\n";

$totalEnrollmentDuplicates = count($duplicates);
$totalLessonDuplicates = DB::select("
    SELECT COUNT(*) as count
    FROM (
        SELECT user_id, lesson_id, type
        FROM user_progress
        WHERE type = 'lesson_completed'
        GROUP BY user_id, lesson_id, type
        HAVING COUNT(*) > 1
    ) as subquery
")[0]->count ?? 0;

echo "Enrollment duplicates: {$totalEnrollmentDuplicates}\n";
echo "Lesson completion duplicates: {$totalLessonDuplicates}\n\n";

if ($totalEnrollmentDuplicates > 0 || $totalLessonDuplicates > 0) {
    echo "⚠️ ACTION REQUIRED:\n";
    echo "Run the cleanup script to remove duplicates before applying migration.\n";
    echo "Script: php clean-duplicate-points.php\n";
}
