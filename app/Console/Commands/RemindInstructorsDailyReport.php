<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class RemindInstructorsDailyReport extends Command
{
    protected $signature = 'reports:remind-instructors';

    protected $description = 'Send an optional 16:00 reminder to instructors to submit a Code Camp daily report';

    public function handle(): int
    {
        $today = today()->toDateString();
        $alreadySubmitted = DailyReport::whereDate('report_date', $today)
            ->pluck('instructor_id')
            ->all();

        $instructors = User::query()->get()->filter(function (User $user) {
            if (method_exists($user, 'isIctTeacher') && $user->isIctTeacher()) {
                return false;
            }
            if (method_exists($user, 'isStudent') && $user->isStudent()) {
                return false;
            }

            return method_exists($user, 'isCodecampTrainer') && $user->isCodecampTrainer();
        });

        $sent = 0;

        foreach ($instructors as $user) {
            if (in_array($user->id, $alreadySubmitted, true)) {
                continue;
            }

            if (method_exists($user, 'isStudent') && $user->isStudent()) {
                continue;
            }

            $exists = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', 'daily_report_reminder')
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Optional daily Code Camp report',
                'message' => 'If you ran a Code Camp session today, please submit the daily report. Ignore this on normal / non-camp days.',
                'type' => 'daily_report_reminder',
                'data' => [
                    'url' => '/daily-reports/submit',
                    'date' => $today,
                    'optional' => true,
                ],
                'is_read' => false,
            ]);

            $sent++;
        }

        $this->info("Sent {$sent} optional daily-report reminder(s).");

        return self::SUCCESS;
    }
}
