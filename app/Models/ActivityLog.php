<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'model_name',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get recent activity logs
     */
    public static function getRecent(int $limit = 50)
    {
        return static::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity logs for a specific model
     */
    public static function forModel(string $modelType, int $modelId)
    {
        return static::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get activity logs by user
     */
    public static function byUser(int $userId)
    {
        return static::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get deletion logs
     */
    public static function getDeletedItems()
    {
        return static::where('action', 'delete')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get update logs
     */
    public static function getUpdateLogs()
    {
        return static::where('action', 'update')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
