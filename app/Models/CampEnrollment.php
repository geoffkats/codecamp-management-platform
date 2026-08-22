<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CampEnrollment extends Model
{
    protected $fillable = [
        'camp_id',
        'student_id',
        'enrolled_by',
        'previous_camp_id',
        'status',
        'enrolled_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relations

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function previousCamp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'previous_camp_id');
    }

    /**
     * @param  int[]  $fromCampIds
     * @return Collection<int, self> keyed by previous_camp_id
     */
    public static function findTransferDestinations(int $studentId, array $fromCampIds): Collection
    {
        if (empty($fromCampIds)) {
            return collect();
        }

        return static::where('student_id', $studentId)
            ->whereIn('previous_camp_id', $fromCampIds)
            ->with('camp:id,name')
            ->get()
            ->keyBy('previous_camp_id');
    }
}
