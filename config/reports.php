<?php

return [
    'cutoff_time' => env('DAILY_REPORT_CUTOFF', '17:00'),
    'grace_minutes' => env('DAILY_REPORT_GRACE', 60),
    'reminder_time' => env('DAILY_REPORT_REMINDER', '16:00'),
    'reminder_times' => [
        '16:00',
    ],
];
