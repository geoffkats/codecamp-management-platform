# Activity Logging & Audit Trail System

## Overview

A comprehensive audit logging system that tracks all changes, deletions, and restorations made to courses, lessons, assessments, quizzes, and assignments. Admins can view complete change histories, identify who made what changes, and restore deleted items or revert changes to previous versions.

## Features

✅ **Complete Activity Logging**
- Logs all create, update, delete, and restore actions
- Records user who made the change, timestamp, and IP address
- Captures before/after values for updates
- Automatically filters sensitive fields (passwords, tokens, etc.)

✅ **Soft Deletes**
- Deleted items are marked as deleted, not removed from database
- Can be restored with a single action
- Original deleted data remains intact and recoverable

✅ **Admin Dashboard**
- View all activity logs with filtering and searching
- Filter by action (create, update, delete, restore), model type, user, and date range
- Export logs as CSV for compliance/auditing
- View complete change history for any item

✅ **Restoration & Reversion**
- Restore soft-deleted items back to active status
- Revert specific changes to previous versions
- Permanently delete items (with confirmation)
- Track all restoration/reversion actions in audit log

## Implementation Details

### Database Changes

Two new migrations were created:
1. `2025_12_07_000001_add_soft_deletes_to_tables.php` - Adds `deleted_at` column to:
   - lessons
   - assessments
   - quizzes
   - assignments

2. `2025_12_07_000002_create_activity_logs_table.php` - Creates `activity_logs` table with:
   - user_id (who made the change)
   - action (create, update, delete, restore, revert)
   - model_type (Course, Lesson, Assessment, Quiz, Assignment)
   - model_id (ID of the item)
   - model_name (title/name of the item)
   - old_values (JSON of previous data)
   - new_values (JSON of new data)
   - ip_address, user_agent
   - created_at, updated_at

### Models Updated

All key models now use the `Auditable` trait:
- `App\Models\Course`
- `App\Models\Lesson`
- `App\Models\Assessment`
- `App\Models\Quiz`
- `App\Models\Assignment`

All models also use Laravel's `SoftDeletes` trait (except Course and StudentProfile which already had it).

### New Files Created

1. **Models**
   - `app/Models/ActivityLog.php` - Model for activity logs

2. **Traits**
   - `app/Traits/Auditable.php` - Trait that adds audit logging to models

3. **Controllers**
   - `app/Http/Controllers/Admin/AuditLogController.php` - Handles all audit log operations

4. **Views**
   - `resources/views/admin/audit/logs.blade.php` - Activity logs dashboard
   - `resources/views/admin/audit/show.blade.php` - Detailed change history for specific items
   - `resources/views/admin/audit/deleted-items.blade.php` - Restoration interface

5. **Routes**
   - Added audit routes to `routes/web.php` under admin prefix

6. **Commands**
   - `app/Console/Commands/TestAuditLogging.php` - Test command to verify system works

## Usage

### For Admins

#### View Activity Logs
Navigate to: **Admin → Audit Logs**

Features:
- Filter by action, model type, user, or date range
- Search by item name
- View detailed change history
- Export to CSV

#### View Item Change History
Click "View Details" on any log entry or navigate to:
`/admin/audit/{modelType}/{modelId}`

Shows:
- Complete change history with timestamps
- Who made each change
- Before/after values for each change
- Option to revert to any previous version

#### Restore Deleted Items
Navigate to: **Admin → Restore Deleted Items**

Shows all soft-deleted items organized by type:
- Courses
- Lessons
- Assessments
- Quizzes
- Assignments

Actions:
- **Restore** - Bring the item back to active status
- **History** - View change history before deletion
- **Permanently Delete** - Permanently remove from database (irreversible)

#### Revert Changes
From any item's change history view, click "Revert to this version" to:
- Restore the item to its state at that specific point in time
- Revert is logged as a new action

### For Developers

#### Adding Auditing to New Models

1. Add the `Auditable` trait to your model:
```php
use App\Traits\Auditable;

class MyModel extends Model
{
    use HasFactory, SoftDeletes, Auditable;
}
```

2. Add SoftDeletes if not already present:
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class MyModel extends Model
{
    use SoftDeletes, Auditable;
}
```

#### Customizing Display Names

By default, the system uses the `title` field for display names. Override the `getDisplayName()` method:

```php
public function getDisplayName(): ?string
{
    return "{$this->title} (v{$this->version})";
}
```

#### Querying Activity Logs

```php
// Get all logs for a specific model
$logs = ActivityLog::forModel('Course', $courseId);

// Get all activity by a user
$logs = ActivityLog::byUser($userId);

// Get only deletion logs
$logs = ActivityLog::getDeletedItems();

// Get only update logs
$logs = ActivityLog::getUpdateLogs();

// Get recent activity
$logs = ActivityLog::getRecent(50);
```

#### Accessing Logs from Models

```php
$course = Course::find(1);
$logs = $course->activityLogs();
```

## API Endpoints

### View Activity Logs
```
GET /admin/audit/logs
```

Query Parameters:
- `action` - Filter by action (create, update, delete, restore)
- `model_type` - Filter by model type (Course, Lesson, etc.)
- `user_id` - Filter by user
- `from_date` - Filter from date (YYYY-MM-DD)
- `to_date` - Filter to date (YYYY-MM-DD)
- `search` - Search by item name
- `page` - Pagination

### View Item History
```
GET /admin/audit/{modelType}/{modelId}
```

Parameters:
- `modelType` - Model type (Course, Lesson, Assessment, Quiz, Assignment)
- `modelId` - ID of the model

### Restore Item
```
POST /admin/audit/restore
```

Body:
```json
{
  "model_type": "Course",
  "model_id": 1
}
```

### Revert to Previous Version
```
POST /admin/audit/revert
```

Body:
```json
{
  "model_type": "Course",
  "model_id": 1,
  "log_id": 123
}
```

### Permanently Delete
```
POST /admin/audit/force-delete
```

Body:
```json
{
  "model_type": "Course",
  "model_id": 1
}
```

### Export Logs
```
GET /admin/audit/export
```

Query Parameters:
- Same as the logs view endpoint
- Returns CSV file

## Testing

Test the audit logging system:

```bash
php artisan app:test-audit-logging
```

This command will:
1. Create a test course
2. Update it
3. View logs
4. Soft delete it
5. Verify delete log
6. Restore it
7. Check final logs
8. Clean up test data

## Security Considerations

✅ **Protected Routes** - All audit routes require `manage_users` permission (admin only)

✅ **Sensitive Data** - The following fields are automatically filtered from logs:
- password
- password_confirmation
- token
- api_token
- secret
- private_key
- public_key
- remember_token
- two_factor_secret

✅ **IP Tracking** - User's IP address is recorded with each action

✅ **User Agent** - Browser/client information is recorded

✅ **Authorization** - Restore, revert, and delete operations require explicit admin authorization

## Performance Considerations

✅ **Indexed Fields**:
- `model_type` and `model_id` (for fast lookups)
- `user_id` and `created_at` (for filtering)
- `action` (for activity type filtering)

✅ **JSON Storage** - Old and new values are stored as JSON for flexibility

✅ **Soft Deletes** - No data is permanently lost, reducing accidental deletion risk

## Troubleshooting

### Activity logs not being created

1. Check that the model has the `Auditable` trait:
```php
use App\Traits\Auditable;
class MyModel extends Model {
    use Auditable;
}
```

2. Check that migrations ran successfully:
```bash
php artisan migrate:status
```

3. Check Laravel logs for errors:
```bash
tail -f storage/logs/laravel.log
```

### Restore button not working

1. Ensure user has `manage_users` permission
2. Check browser console for JavaScript errors
3. Verify the item was actually soft-deleted (check `deleted_at` column)

### Performance issues with large audit tables

Consider archiving old logs periodically:
```php
// Archive logs older than 6 months
ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();
```

## Future Enhancements

Potential additions:
- Audit log retention policies
- Automated log archival
- Email notifications for deletions
- Granular permission controls per item type
- Diff view for large text changes
- Undo/Redo functionality
- API audit logging
- Webhook notifications on changes
