<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CodeClub extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'slug',
        'description',
        'day_of_week',
        'session_start',
        'session_end',
        'status',
        'max_capacity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'max_capacity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $club) {
            if (empty($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CodeClubMembership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->hasMany(CodeClubMembership::class)->where('status', 'active');
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(CodeClubInstructor::class);
    }

    public function activeInstructors(): HasMany
    {
        return $this->hasMany(CodeClubInstructor::class)->where('status', 'active');
    }

    public function sessionReports(): HasMany
    {
        return $this->hasMany(ClubSessionReport::class);
    }

    public function termReportDrafts(): HasMany
    {
        return $this->hasMany(CodeClubTermReportDraft::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClubSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function hasScheduleRows(): bool
    {
        if ($this->relationLoaded('schedules')) {
            return $this->schedules->isNotEmpty();
        }

        return $this->schedules()->exists();
    }

    public function getScheduleLabelAttribute(): string
    {
        if ($this->hasScheduleRows()) {
            return $this->buildScheduleLabelFromRows($this->schedules);
        }

        return $this->buildScheduleLabelFromLegacy();
    }

    private function buildScheduleLabelFromLegacy(): string
    {
        $parts = array_filter([
            $this->formatDayAbbrev($this->day_of_week),
            $this->session_start ? substr((string) $this->session_start, 0, 5) : null,
            $this->session_end ? substr((string) $this->session_end, 0, 5) : null,
        ]);

        if (count($parts) >= 3) {
            return "{$parts[0]} {$parts[1]}–{$parts[2]}";
        }

        return implode(' ', $parts) ?: 'Schedule TBD';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ClubSchedule>|\Illuminate\Database\Eloquent\Collection<int, ClubSchedule>  $rows
     */
    private function buildScheduleLabelFromRows($rows): string
    {
        $dayOrder = array_flip(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);

        $sorted = $rows->sortBy(fn (ClubSchedule $row) => $dayOrder[strtolower($row->day_of_week)] ?? 99)->values();

        $groups = [];
        foreach ($sorted as $row) {
            $timeKey = ($row->effectiveSessionStart($this) ?? '') . '|' . ($row->effectiveSessionEnd($this) ?? '');
            $groups[$timeKey]['times'] = [
                'start' => $row->effectiveSessionStart($this),
                'end' => $row->effectiveSessionEnd($this),
            ];
            $groups[$timeKey]['days'][] = strtolower($row->day_of_week);
        }

        $segments = [];
        foreach ($groups as $group) {
            $dayLabel = $this->formatDayRange($group['days']);
            $start = $group['times']['start'];
            $end = $group['times']['end'];

            if ($start && $end) {
                $segments[] = "{$dayLabel} {$start}–{$end}";
            } elseif ($start) {
                $segments[] = "{$dayLabel} {$start}";
            } else {
                $segments[] = $dayLabel;
            }
        }

        return implode('; ', $segments) ?: 'Schedule TBD';
    }

    /**
     * @param  array<int, string>  $days
     */
    private function formatDayRange(array $days): string
    {
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $indices = collect($days)
            ->map(fn (string $day) => array_search(strtolower($day), $dayOrder, true))
            ->filter(fn ($index) => $index !== false)
            ->sort()
            ->values()
            ->all();

        if ($indices === []) {
            return implode(', ', $days);
        }

        $ranges = [];
        $rangeStart = $indices[0];
        $prev = $indices[0];

        for ($i = 1; $i <= count($indices); $i++) {
            $current = $indices[$i] ?? null;
            if ($current !== null && $current === $prev + 1) {
                $prev = $current;

                continue;
            }

            $ranges[] = $rangeStart === $prev
                ? $this->formatDayAbbrev($dayOrder[$rangeStart])
                : $this->formatDayAbbrev($dayOrder[$rangeStart]) . '–' . $this->formatDayAbbrev($dayOrder[$prev]);

            if ($current !== null) {
                $rangeStart = $current;
                $prev = $current;
            }
        }

        return implode(', ', $ranges);
    }

    private function formatDayAbbrev(?string $day): ?string
    {
        if (! $day) {
            return null;
        }

        return match (strtolower(trim($day))) {
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
            default => ucfirst(substr(strtolower(trim($day)), 0, 3)),
        };
    }
}
