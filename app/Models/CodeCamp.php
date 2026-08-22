<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CodeCamp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'start_date',
        'end_date',
        'status',
        'max_capacity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'max_capacity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $camp) {
            if (empty($camp->slug)) {
                $camp->slug = Str::slug($camp->name);
            }
        });
    }

    // Relations

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CampEnrollment::class, 'camp_id');
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(CampEnrollment::class, 'camp_id')->where('status', 'active');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'camp_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers

    public function getDateRangeAttribute(): string
    {
        $start = $this->start_date->format('d M Y');
        $end   = $this->end_date ? $this->end_date->format('d M Y') : 'Ongoing';
        return "{$start} – {$end}";
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'    => 'green',
            'upcoming'  => 'blue',
            'completed' => 'gray',
            'archived'  => 'red',
            default     => 'gray',
        };
    }

    public function advanceStatus(): void
    {
        $next = match($this->status) {
            'upcoming'  => 'active',
            'active'    => 'completed',
            'completed' => 'archived',
            default     => null,
        };

        if ($next) {
            $this->update(['status' => $next]);
        }
    }
}
