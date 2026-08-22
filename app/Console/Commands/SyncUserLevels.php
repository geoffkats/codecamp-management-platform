<?php

namespace App\Console\Commands;

use App\Services\PointsService;
use Illuminate\Console\Command;

class SyncUserLevels extends Command
{
    protected $signature = 'points:sync-levels';

    protected $description = 'Recalculate every user level and rank title from total XP';

    public function handle(PointsService $pointsService): int
    {
        $this->info('Syncing user levels from total XP...');

        $count = $pointsService->syncAllLevels();

        $this->info("Synced {$count} user point records.");

        return self::SUCCESS;
    }
}
