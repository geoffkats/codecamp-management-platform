<?php

namespace App\Console\Commands;

use App\Models\AssessmentAttempt;
use App\Services\Assessments\LegacyQuestionSetBackfiller;
use Illuminate\Console\Command;

class SnapshotAssessmentAttempts extends Command
{
    protected $signature = 'assessments:snapshot-attempts
        {--dry-run : Report what would be snapshotted and flagged without writing}
        {--attempt=* : Only these attempt ids}
        {--assessment= : Only attempts of this assessment}
        {--chunk=200 : Attempts processed per batch}';

    protected $description = 'Freeze question sets for attempts started before snapshots existed, flagging any that cannot be reconstructed exactly';

    public function handle(LegacyQuestionSetBackfiller $backfiller): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = AssessmentAttempt::query()
            ->whereNull('question_set_generated_at')
            ->when($this->option('attempt'), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->when($this->option('assessment'), fn ($q, $id) => $q->where('assessment_id', $id));

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No attempts need a question-set snapshot.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[dry run] ' : '')."Processing {$total} attempt(s)...");

        $processed = 0;
        $flagged = [];
        $reasonCounts = [];

        $query->orderBy('id')->chunkById(max(1, (int) $this->option('chunk')), function ($attempts) use ($backfiller, $dryRun, &$processed, &$flagged, &$reasonCounts) {
            foreach ($attempts as $attempt) {
                $reasons = $dryRun
                    ? $backfiller->plan($attempt)['reasons']
                    : ($backfiller->backfill($attempt)->question_set_meta['review_reasons'] ?? []);

                $processed++;
                if ($reasons !== []) {
                    $flagged[] = [$attempt->id, $attempt->assessment_id, $attempt->status, implode(', ', $reasons)];
                    foreach ($reasons as $reason) {
                        $reasonCounts[$reason] = ($reasonCounts[$reason] ?? 0) + 1;
                    }
                }
            }
        });

        $this->info(($dryRun ? 'Would snapshot' : 'Snapshotted')." {$processed} attempt(s); ".count($flagged).' flagged for migration review.');

        if ($reasonCounts !== []) {
            $this->table(['Reason', 'Attempts'], collect($reasonCounts)->map(fn ($count, $reason) => [$reason, $count])->values()->all());
            $this->table(['Attempt', 'Assessment', 'Status', 'Reasons'], array_slice($flagged, 0, 100));
            if (count($flagged) > 100) {
                $this->line('…and '.(count($flagged) - 100).' more.');
            }
        }

        return self::SUCCESS;
    }
}
