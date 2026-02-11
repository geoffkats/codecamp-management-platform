<?php

return [
    'cutoff_time' => env('DAILY_REPORT_CUTOFF', '17:00'),
    'grace_minutes' => env('DAILY_REPORT_GRACE', 60),
    'reminder_times' => [
        '16:30',
        '17:05',
    ],
];
