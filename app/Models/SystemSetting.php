<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("setting.{$key}");
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return self::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');
        self::query()->get()->each(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }
}
