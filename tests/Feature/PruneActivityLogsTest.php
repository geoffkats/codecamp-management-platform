<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

it('deletes activity logs older than 30 days and keeps newer ones', function () {
    $stale = ActivityLog::create([
        'action' => 'update',
        'model_type' => 'Lesson',
        'model_id' => 1,
        'model_name' => 'Old lesson',
        'old_values' => ['body' => 'before'],
        'new_values' => ['body' => 'after'],
    ]);
    $stale->forceFill(['created_at' => now()->subDays(31)])->save();

    $recent = ActivityLog::create([
        'action' => 'update',
        'model_type' => 'Lesson',
        'model_id' => 2,
        'model_name' => 'Recent lesson',
        'old_values' => ['body' => 'before'],
        'new_values' => ['body' => 'after'],
    ]);

    $this->artisan('activity-logs:prune')->assertSuccessful();

    expect(DB::table('activity_logs')->where('id', $stale->id)->exists())->toBeFalse()
        ->and(DB::table('activity_logs')->where('id', $recent->id)->exists())->toBeTrue();
});
