<?php

return [
    /** Daily code check-in allowed between these times (24h, local). */
    'check_in_start' => env('ATTENDANCE_CHECK_IN_START', '07:00'),
    'check_in_end'   => env('ATTENDANCE_CHECK_IN_END', '14:00'),

    /** After this time, check-in counts as late (until lock). */
    'late_after' => env('ATTENDANCE_LATE_AFTER', '09:30'),

    /** No edits after this time except admin/supervisor. */
    'lock_time' => env('ATTENDANCE_LOCK_TIME', env('DAILY_REPORT_CUTOFF', '17:00')),

    /** Minimum minutes between check-in and check-out. */
    'min_checkout_minutes' => (int) env('ATTENDANCE_MIN_CHECKOUT_MINUTES', 60),

    /** Default clock-out for manual bulk present marks. */
    'default_clock_out' => env('ATTENDANCE_DEFAULT_CLOCK_OUT', '17:00'),

    /** Close forgotten check-ins after the session ends. */
    'auto_checkout' => filter_var(env('ATTENDANCE_AUTO_CHECKOUT', true), FILTER_VALIDATE_BOOLEAN),

    /** How many days back teachers may mark or correct attendance. */
    'teacher_backfill_days' => (int) env('ATTENDANCE_TEACHER_BACKFILL_DAYS', 30),

    /** Code Club: minutes before session_start that check-in opens. */
    'club_check_in_early_minutes' => (int) env('CLUB_CHECK_IN_EARLY_MINUTES', 15),

    /** Code Club: minutes after session_start before check-in counts as late. */
    'club_late_grace_minutes' => (int) env('CLUB_LATE_GRACE_MINUTES', 15),

    /** Code Club: minimum minutes between check-in and check-out. */
    'club_min_checkout_minutes' => (int) env('CLUB_MIN_CHECKOUT_MINUTES', 30),

    /** Fallback session times when a club has no schedule configured. */
    'club_default_session_start' => env('CLUB_DEFAULT_SESSION_START', '15:00'),
    'club_default_session_end' => env('CLUB_DEFAULT_SESSION_END', '16:30'),
];
