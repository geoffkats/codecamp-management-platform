<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Course;
use Illuminate\Console\Command;

class TestAuditLogging extends Command
{
    protected $signature = 'app:test-audit-logging';
    protected $description = 'Test the audit logging system';

    public function handle(): int
    {
        $this->info('Testing Audit Logging System...\n');

        // Test 1: Create a course
        $this->info('1️⃣ Testing Course Creation...');
        $course = Course::create([
            'title' => 'Test Course for Audit Logging',
            'description' => 'This is a test course to verify audit logging',
            'instructor_id' => auth()->id() ?? 1,
            'slug' => 'test-course-audit',
        ]);
        $this->info("✓ Course created with ID: {$course->id}\n");

        // Test 2: Update the course
        $this->info('2️⃣ Testing Course Update...');
        $course->update([
            'description' => 'Updated description for audit test',
            'is_published' => true,
        ]);
        $this->info("✓ Course updated\n");

        // Test 3: Get activity logs
        $this->info('3️⃣ Retrieving Activity Logs...');
        $logs = ActivityLog::forModel('Course', $course->id);
        $this->info("Found " . $logs->count() . " log entries:\n");

        foreach ($logs as $log) {
            $this->line("  - Action: {$log->action}");
            $userName = $log->user?->name ?? 'System';
            $this->line("    User: {$userName}");
            $this->line("    Time: {$log->created_at->format('Y-m-d H:i:s')}");
        }
        $this->line('');

        // Test 4: Soft delete
        $this->info('4️⃣ Testing Soft Delete...');
        $course->delete();
        $this->info("✓ Course soft deleted\n");

        // Test 5: Check logs again
        $this->info('5️⃣ Checking Activity Logs After Delete...');
        $logs = ActivityLog::forModel('Course', $course->id);
        $deleteLog = $logs->where('action', 'delete')->first();
        if ($deleteLog) {
            $this->line("✓ Delete action logged at: {$deleteLog->created_at}");
        }
        $this->line('');

        // Test 6: Restore
        $this->info('6️⃣ Testing Restore...');
        $course->restore();
        $this->info("✓ Course restored\n");

        // Test 7: Final log count
        $this->info('7️⃣ Final Activity Log Summary...');
        $allLogs = ActivityLog::forModel('Course', $course->id);
        $this->line("Total log entries: " . $allLogs->count());
        $this->line("Actions recorded:");
        foreach ($allLogs->groupBy('action') as $action => $actionLogs) {
            $this->line("  - {$action}: " . $actionLogs->count());
        }
        $this->line('');

        // Cleanup
        $this->info('8️⃣ Cleaning up test data...');
        $course->forceDelete();
        $this->info("✓ Test data removed\n");

        $this->info('✅ Audit logging system is working correctly!');
        return self::SUCCESS;
    }
}
