<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_profile_id',
        'course_id',
        'camp_id',
        'club_id',
        'attendance_date',
        'status',
        'source',
        'code_used',
        'reason',
        'notes',
        'recorded_by',
        'recorded_at',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'club_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function clockInCarbon(): ?Carbon
    {
        return $this->clockTimeOnDate($this->clock_in);
    }

    public function clockOutCarbon(): ?Carbon
    {
        return $this->clockTimeOnDate($this->clock_out);
    }

    public function formattedClockIn(): ?string
    {
        return $this->clockInCarbon()?->format('h:i A');
    }

    public function formattedClockOut(): ?string
    {
        return $this->clockOutCarbon()?->format('h:i A');
    }

    /**
     * @return array{hours: int, minutes: int}|null
     */
    public function sessionDuration(): ?array
    {
        $checkIn = $this->clockInCarbon();
        $checkOut = $this->clockOutCarbon();

        if (! $checkIn || ! $checkOut) {
            return null;
        }

        $totalHours = $checkIn->diffInHours($checkOut, true);
        $hours = (int) floor($totalHours);

        return [
            'hours' => $hours,
            'minutes' => (int) round(($totalHours - $hours) * 60),
        ];
    }

    protected function clockTimeOnDate(mixed $time): ?Carbon
    {
        if (blank($time) || ! $this->attendance_date) {
            return null;
        }

        if ($time instanceof Carbon) {
            return Carbon::parse(
                $this->attendance_date->format('Y-m-d') . ' ' . $time->format('H:i:s')
            );
        }

        $timeString = (string) $time;

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $timeString)) {
            return Carbon::parse($timeString);
        }

        return Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $timeString);
    }
}

