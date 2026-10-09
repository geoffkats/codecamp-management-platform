<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentUniform extends Model
{
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    protected $fillable = [
        'student_profile_id',
        'size',
        'paid',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'paid_at' => 'date',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
