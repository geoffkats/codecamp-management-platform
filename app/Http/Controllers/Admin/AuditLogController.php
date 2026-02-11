<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Quiz;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !$user->hasAnyRole(['admin', 'supervisor'])) {
                abort(403, 'Access denied.');
            }
            return $next($request);
        });
    }

    /**
     * Display activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by model name
        if ($request->filled('search')) {
            $query->where('model_name', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $modelTypes = ['Course', 'Lesson', 'Assessment', 'Quiz', 'Assignment'];
        $actions = ['create', 'update', 'delete', 'restore'];

        return view('admin.audit.logs', compact('logs', 'modelTypes', 'actions'));
    }

    /**
     * Show activity details for a specific model
     */
    public function show(string $modelType, int $modelId)
    {
        $logs = ActivityLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $model = $this->getModel($modelType, $modelId, true);

        return view('admin.audit.show', compact('modelType', 'modelId', 'logs', 'model'));
    }

    /**
     * Show deleted items that can be restored
     */
    public function deletedItems(Request $request)
    {
        $modelType = $request->query('type', null);
        
        $deletedCourses = $modelType === null || $modelType === 'Course' 
            ? Course::onlyTrashed()->get() 
            : collect();
        
        $deletedLessons = $modelType === null || $modelType === 'Lesson' 
            ? Lesson::onlyTrashed()->get() 
            : collect();
        
        $deletedAssessments = $modelType === null || $modelType === 'Assessment' 
            ? Assessment::onlyTrashed()->get() 
            : collect();
        
        $deletedQuizzes = $modelType === null || $modelType === 'Quiz' 
            ? Quiz::onlyTrashed()->get() 
            : collect();
        
        $deletedAssignments = $modelType === null || $modelType === 'Assignment' 
            ? Assignment::onlyTrashed()->get() 
            : collect();

        return view('admin.audit.deleted-items', compact(
            'deletedCourses',
            'deletedLessons',
            'deletedAssessments',
            'deletedQuizzes',
            'deletedAssignments',
            'modelType'
        ));
    }

    /**
     * Restore a deleted item
     */
    public function restore(Request $request)
    {
        $this->assertAdmin();

        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');

        $model = $this->getModel($modelType, $modelId, true);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        if (!method_exists($model, 'restore')) {
            return response()->json(['message' => 'Model does not support restoration'], 400);
        }

        try {
            if ($modelType === 'Course') {
                $this->restoreCourseWithChildren($model);
            } else {
                $model->restore();
            }

            // Log the restoration
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'restore',
                'model_type' => $modelType,
                'model_id' => $modelId,
                'model_name' => $model->getDisplayName() ?? 'Unknown',
                'new_values' => json_encode($model->getAttributes()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return response()->json([
                'message' => "{$modelType} has been restored successfully",
                'model' => $model
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error restoring item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert model changes to a specific point in history
     */
    public function revert(Request $request)
    {
        $this->assertAdmin();

        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');
        $logId = $request->input('log_id');

        $log = ActivityLog::find($logId);
        if (!$log) {
            return response()->json(['message' => 'Log entry not found'], 404);
        }

        $model = $this->getModel($modelType, $modelId, false);
        if (!$model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        try {
            // Revert to the old values from the log
            if ($log->old_values) {
                $oldValues = json_decode($log->old_values, true);
                
                // Remove timestamps from revert to avoid issues
                unset($oldValues['created_at']);
                unset($oldValues['updated_at']);
                unset($oldValues['id']);
                unset($oldValues['deleted_at']);

                $model->update($oldValues);

                // Log the revert action
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'revert',
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'model_name' => $model->getDisplayName() ?? 'Unknown',
                    'old_values' => $log->new_values, // Current values are now old
                    'new_values' => $log->old_values,  // Reverted to these values
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ]);

                return response()->json([
                    'message' => "{$modelType} has been reverted successfully",
                    'model' => $model
                ]);
            } else {
                return response()->json(['message' => 'No previous values to revert to'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reverting changes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a soft-deleted item
     */
    public function forceDelete(Request $request)
    {
        $this->assertAdmin();

        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');

        $model = $this->getModel($modelType, $modelId, true);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        try {
            if (method_exists($model, 'forceDelete')) {
                $model->forceDelete();
            } else {
                $model->delete();
            }

            return response()->json(['message' => "{$modelType} has been permanently deleted"]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a model by type and id (including trashed if $includeTrashed is true)
     */
    private function getModel(string $modelType, int $modelId, bool $includeTrashed = false)
    {
        $query = match ($modelType) {
            'Course' => Course::query(),
            'Lesson' => Lesson::query(),
            'Assessment' => Assessment::query(),
            'Quiz' => Quiz::query(),
            'Assignment' => Assignment::query(),
            default => null,
        };

        if (!$query) {
            return null;
        }

        if ($includeTrashed && method_exists($query->getModel(), 'restore')) {
            $query = $query->withTrashed();
        }

        return $query->find($modelId);
    }

    private function restoreCourseWithChildren(Course $course): void
    {
        $course->modules()->withTrashed()->get()->each(function ($module) {
            $module->lessons()->withTrashed()->get()->each(function ($lesson) {
                Assessment::withTrashed()->where('lesson_id', $lesson->id)->get()->each->restore();
                Assignment::withTrashed()->where('lesson_id', $lesson->id)->get()->each->restore();
                $lesson->restore();
            });

            $module->restore();
        });

        $course->restore();
    }

    /**
     * Simple role guard: allow only admins (middleware already applied, but this avoids missing gates).
     */
    private function assertAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !(method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->isAdmin() ?? false))) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Export activity logs as CSV
     */
    public function export(Request $request)
    {
        $filename = 'activity-logs-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, [
                'ID', 'User', 'Action', 'Model Type', 'Model Name', 
                'Model ID', 'IP Address', 'Date', 'Time'
            ]);

            // Write data
            $query = ActivityLog::with('user');

            // Apply same filters as index
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            if ($request->filled('model_type')) {
                $query->where('model_type', $request->model_type);
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $query->orderBy('created_at', 'desc')
                ->chunk(100, function ($logs) use ($file) {
                    foreach ($logs as $log) {
                        fputcsv($file, [
                            $log->id,
                            $log->user?->name ?? 'Unknown',
                            $log->action,
                            $log->model_type,
                            $log->model_name,
                            $log->model_id,
                            $log->ip_address,
                            $log->created_at->format('Y-m-d'),
                            $log->created_at->format('H:i:s'),
                        ]);
                    }
                });

            fclose($file);
        };

        return \Response::stream($callback, 200, $headers);
    }
}
