# Quick Start: Audit Logging System

## What Was Implemented

You now have a complete audit logging system that tracks **who** changes, deletes, or edits courses, lessons, assessments, quizzes, and assignments.

## Key Features

### 1. **Activity Logging**
Every action is logged with:
- Who did it (user)
- What they did (create, update, delete, restore)
- When (timestamp)
- Where from (IP address)
- What changed (before/after values)

### 2. **Soft Deletes**
Items are never permanently deleted initially:
- Deleted items can be restored
- Original data is preserved
- All changes are reversible

### 3. **Admin Dashboard**
Located at: **Admin → Audit Logs**
- View all activity
- Filter by action, type, user, date
- Search by item name
- Export to CSV

### 4. **Restore & Revert**
Located at: **Admin → Restore Deleted Items**
- Restore deleted items
- Revert changes to previous versions
- Permanently delete if needed

## How to Use

### As an Admin

1. **Go to Admin Panel**
   - Click Admin in navigation
   - Look for "Audit Logs" menu

2. **View Activity**
   - See all changes made to courses, lessons, etc.
   - Filter by user, action, or date
   - Click "View Details" to see change history

3. **Restore Deleted Items**
   - Click "Restore Deleted Items"
   - Choose what type of item you want to restore
   - Click "Restore" on the item

4. **Revert Changes**
   - From any item's history, click "Revert to this version"
   - The item goes back to that state

## What Gets Tracked

✅ **Courses** - Title, description, enrollment type, etc.
✅ **Lessons** - Title, content, video, etc.
✅ **Assessments** - Questions, settings, criteria, etc.
✅ **Quizzes** - Questions, time limits, scoring, etc.
✅ **Assignments** - Title, instructions, due date, etc.

## Security Notes

🔐 All audit operations require admin permissions
🔐 User IP addresses are recorded
🔐 Passwords and tokens are never logged
🔐 All changes create an audit trail

## Testing

To verify the system is working:

```bash
php artisan app:test-audit-logging
```

This will test creating, updating, deleting, and restoring items.

## Database Changes

Two tables were added:
- `activity_logs` - Stores all logged activities
- Updated tables now have `deleted_at` column for soft deletes:
  - lessons
  - assessments
  - quizzes
  - assignments

## Next Steps

1. **Clear Cache** (already done):
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```

2. **Test It Out**:
   - Create a test course/lesson
   - Edit it
   - Delete it
   - Go to Admin → Restore Deleted Items to restore it
   - Check Admin → Audit Logs to see the activity

3. **Monitor Activity**:
   - Regularly check Audit Logs
   - Verify who's making changes
   - Export logs for compliance if needed

## Routes Available

| Path | Description |
|------|-------------|
| `/admin/audit/logs` | View all activity logs |
| `/admin/audit/deleted-items` | View deleted items & restore |
| `/admin/audit/{modelType}/{modelId}` | View change history for item |
| `/admin/audit/export` | Export logs as CSV |

## API Endpoints

```php
// Restore an item
POST /admin/audit/restore
Body: { "model_type": "Course", "model_id": 1 }

// Revert to previous version
POST /admin/audit/revert
Body: { "model_type": "Course", "model_id": 1, "log_id": 123 }

// Permanently delete
POST /admin/audit/force-delete
Body: { "model_type": "Course", "model_id": 1 }
```

## Troubleshooting

**Problem**: Can't see Audit Logs menu
**Solution**: Make sure you're logged in as admin with `manage_users` permission

**Problem**: No activities showing
**Solution**: Activities are logged going forward. Create/edit an item to see it logged.

**Problem**: Items not restoring
**Solution**: Check that the item was soft-deleted (not permanently deleted)

## Support

For detailed documentation, see: `docs/AUDIT_LOGGING_SYSTEM.md`

## Summary

You now have:
✅ Complete tracking of who changes what and when
✅ Ability to restore deleted items
✅ Ability to revert changes to previous versions
✅ Admin interface to view all activity
✅ Export logs for compliance/auditing

Start by going to **Admin Panel → Audit Logs** to explore!
