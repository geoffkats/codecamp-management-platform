<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Holds original model values during an update without persisting.
     */
    protected array $originalValuesCache = [];

    /**
     * Boot the trait to register model event listeners
     */
    public static function bootAuditable(): void
    {
        // Log on create
        static::created(function ($model) {
            static::logActivity('create', $model, null, $model->getAttributes());
        });

        // Log on update
        static::updating(function ($model) {
            $originalAttributes = $model->getOriginal();

            // Store original values for comparison in updated event without persisting as an attribute
            $model->originalValuesCache = $originalAttributes;
        });

        static::updated(function ($model) {
            if (!isset($model->originalValuesCache)) {
                $model->originalValuesCache = [];
            }

            static::logActivity('update', $model, $model->originalValuesCache, $model->getAttributes());
        });

        // Log on delete (soft delete)
        static::deleting(function ($model) {
            static::logActivity('delete', $model, $model->getAttributes(), null);
        });

        // Log on restore
        static::restored(function ($model) {
            static::logActivity('restore', $model, null, $model->getAttributes());
        });
    }

    /**
     * Log activity for the model
     */
    protected static function logActivity(string $action, $model, ?array $oldValues, ?array $newValues): void
    {
        try {
            $user = Auth::user();
            $userId = $user?->id;

            // Get the model's display name
            $modelName = null;
            if (method_exists($model, 'getDisplayName')) {
                $modelName = $model->getDisplayName();
            } elseif (isset($model->title)) {
                $modelName = $model->title;
            } elseif (isset($model->name)) {
                $modelName = $model->name;
            }

            // Filter out sensitive fields
            $oldValues = static::filterSensitiveData($oldValues);
            $newValues = static::filterSensitiveData($newValues);

            // Only log if there are actual changes for update
            if ($action === 'update') {
                $hasChanges = false;
                if ($oldValues && $newValues) {
                    foreach ($newValues as $key => $value) {
                        if (!isset($oldValues[$key]) || $oldValues[$key] != $value) {
                            $hasChanges = true;
                            break;
                        }
                    }
                }
                if (!$hasChanges) {
                    return; // Don't log if nothing changed
                }
            }

            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'model_name' => $modelName,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't let logging errors break the app
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Filter out sensitive fields from logging
     */
    protected static function filterSensitiveData(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'secret',
            'private_key',
            'public_key',
            'remember_token',
            'two_factor_secret',
        ];

        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * Get activity logs for this model instance
     */
    public function activityLogs()
    {
        return ActivityLog::where('model_type', class_basename($this))
            ->where('model_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the display name of the model (override in specific models if needed)
     */
    public function getDisplayName(): ?string
    {
        if (isset($this->title)) {
            return $this->title;
        }
        if (isset($this->name)) {
            return $this->name;
        }
        return null;
    }
}
