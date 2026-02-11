<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assessment;4

$updated = 0;

$assessments = Assessment::where('approval_status', '!=', 'approved')->get();

if ($assessments->isEmpty()) {
    echo "No assessments require approval.\n";
    exit(0);
}

echo "Approving {$assessments->count()} assessments...\n";

foreach ($assessments as $assessment) {
    $assessment->update([
        'approval_status' => 'approved',
        'approved_at' => now(),
        'approved_by' => 1, // set to a valid admin user ID in production
    ]);
    $updated++;
    echo "✓ Approved ID {$assessment->id}: {$assessment->title}\n";
}

echo "\n✅ Done. Approved {$updated} assessments.\n";
