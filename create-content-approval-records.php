<?php

/**
 * Script to create ContentApproval records for existing lessons
 * Run this once to backfill approval records for lessons that were auto-approved
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lesson;
use App\Models\ContentApproval;
use Illuminate\Support\Facades\DB;

echo "Creating ContentApproval records for existing lessons...\n\n";

$lessons = Lesson::with('course')->get();
$created = 0;
$skipped = 0;

foreach ($lessons as $lesson) {
    // Check if ContentApproval already exists
    $exists = ContentApproval::where('approvable_type', Lesson::class)
        ->where('approvable_id', $lesson->id)
        ->exists();
    
    if ($exists) {
        $skipped++;
        continue;
    }
    
    // Determine status based on lesson approval_status
    $status = match($lesson->approval_status) {
        'approved' => 'approved',
        'pending' => 'pending',
        'rejected' => 'rejected',
        default => 'approved', // Default to approved for draft/null
    };
    
    // Create ContentApproval record
    ContentApproval::create([
        'approvable_type' => Lesson::class,
        'approvable_id' => $lesson->id,
        'status' => $status,
        'submitted_by' => $lesson->course->instructor_id ?? 1,
        'submitted_at' => $lesson->submitted_for_approval_at ?? $lesson->created_at,
        'reviewed_by' => $lesson->approved_by,
        'reviewed_at' => $lesson->approved_at,
        'notes' => 'Backfilled approval record',
    ]);
    
    $created++;
    echo "✓ Created approval record for lesson: {$lesson->title}\n";
}

echo "\n";
echo "Summary:\n";
echo "- Created: {$created} records\n";
echo "- Skipped: {$skipped} records (already exist)\n";
echo "\nDone!\n";
