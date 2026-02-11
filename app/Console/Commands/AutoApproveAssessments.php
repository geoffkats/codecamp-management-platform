<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class AutoApproveAssessments extends Command
{
    protected $signature = 'assessments:auto-approve {--dry-run}';
    
    protected $description = 'Auto-approve all draft assessments';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $assessments = Assessment::where('approval_status', 'draft')->get();
        
        if ($assessments->isEmpty()) {
            $this->info('No draft assessments found.');
            return 0;
        }

        $this->warn("Found {$assessments->count()} draft assessments");
        
        if ($dryRun) {
            $this->info('[DRY RUN] Would approve:');
            foreach ($assessments as $a) {
                $this->line("  - ID {$a->id}: {$a->title}");
            }
            return 0;
        }

        if (!$this->confirm("Approve all {$assessments->count()} assessments?")) {
            $this->info('Cancelled.');
            return 0;
        }

        $updated = 0;
        foreach ($assessments as $assessment) {
            $assessment->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => 1, // Change to actual admin user ID if needed
            ]);
            $updated++;
            $this->line("✓ Approved: {$assessment->title}");
        }

        $this->info("\n✅ Successfully approved {$updated} assessments!");
        return 0;
    }
}
