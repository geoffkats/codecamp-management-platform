<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class);
    }

    /**
     * Resolve free-text tag names to tags, creating any that don't exist yet.
     *
     * @param  iterable<string>  $names
     * @return Collection<int, Tag>
     */
    public static function findOrCreateMany(iterable $names): Collection
    {
        return collect($names)
            ->map(fn ($name) => trim(preg_replace('/\s+/', ' ', (string) $name)))
            ->filter(fn ($name) => $name !== '' && mb_strlen($name) <= 60)
            ->unique(fn ($name) => Str::slug($name))
            ->filter(fn ($name) => Str::slug($name) !== '')
            ->map(fn ($name) => static::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]))
            ->values();
    }
}
