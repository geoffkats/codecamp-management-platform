<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assessment;

echo "Recent Assessments Status Check\n";
echo str_repeat("=", 60) . "\n\n";

$assessments = Assessment::latest()->take(5)->get(['id', 'title', 'approval_status', 'approved_at', 'approved_by']);

foreach ($assessments as $a) {
    echo "ID: {$a->id} - {$a->title}\n";
    echo "  Approval Status: {$a->approval_status}\n";
    echo "  Approved At: " . ($a->approved_at ? $a->approved_at->format('Y-m-d H:i:s') : 'Not approved') . "\n";
    echo "  Approved By: " . ($a->approved_by ? $a->approved_by : 'Unknown') . "\n\n";
}
