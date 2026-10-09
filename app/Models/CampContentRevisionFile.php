<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CampContentRevisionFile extends Model
{
    protected $fillable = [
        'camp_content_revision_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (self $file) => Storage::disk($file->disk)->delete($file->path));
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(CampContentRevision::class, 'camp_content_revision_id');
    }

    public function humanSize(): string
    {
        $size = (int) $this->size;

        return match (true) {
            $size >= 1048576 => round($size / 1048576, 1).' MB',
            $size >= 1024 => round($size / 1024).' KB',
            default => $size.' B',
        };
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }
}
