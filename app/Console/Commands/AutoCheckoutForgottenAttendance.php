<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class AutoCheckoutForgottenAttendance extends Command
{
    protected $signature = 'attendance:auto-checkout';

    protected $description = 'Check out students who forgot to tap out after the session ended';

    public function handle(AttendanceService $attendance): int
    {
        $count = $attendance->autoCheckOutForgotten();

        $this->info($count > 0
            ? "Auto-checked out {$count} student(s) who forgot to tap out."
            : 'No forgotten check-outs to close.');

        return self::SUCCESS;
    }
}
