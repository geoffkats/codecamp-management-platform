<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-logs:prune {--days=30 : Delete logs older than this many days}';

    protected $description = 'Delete activity logs older than 30 days so the audit table cannot grow without limit';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $deleted = 0;

        do {
            $ids = DB::table('activity_logs')
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit(100)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += DB::table('activity_logs')->whereIn('id', $ids)->delete();
        } while (true);

        if ($deleted > 0 && DB::getDriverName() === 'mysql') {
            $this->reclaimSpace();
        }

        $this->info($deleted > 0
            ? "Deleted {$deleted} activity log(s) older than {$days} days."
            : "No activity logs older than {$days} days.");

        return self::SUCCESS;
    }

    /**
     * Copy the remaining rows into a new table. OPTIMIZE TABLE on this MySQL
     * build reports success without releasing the space used by deleted lesson snapshots.
     */
    private function reclaimSpace(): void
    {
        DB::statement('DROP TABLE IF EXISTS activity_logs_pruned');
        DB::statement('CREATE TABLE activity_logs_pruned LIKE activity_logs');
        DB::statement('INSERT INTO activity_logs_pruned SELECT * FROM activity_logs');
        DB::statement('DROP TABLE activity_logs');
        DB::statement('RENAME TABLE activity_logs_pruned TO activity_logs');
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
    }
}
