# 403 Forbidden Error - Assessment Access Issue

## Problem
Users are getting a 403 Forbidden error when trying to access:
- URL: `https://codecamp.codeacademyug.org/assessments/266/take`
- User ID: 82

## Root Cause
The 403 error is triggered by the `checkAccess()` method in the Take component, which validates three conditions:
1. **Enrollment**: User must be enrolled in the course
2. **Lock Status**: Assessment must not be locked (or user must be admin/teacher)
3. **Approval Status**: Assessment must be approved (or user must be admin/teacher)

## Fixes Implemented

### 1. Enhanced Error Logging
Added detailed logging to `app/Livewire/Assessments/Take.php` that logs:
- Which check failed
- User ID and assessment ID
- Course information
- Reason for denial

Logs will appear in `storage/logs/laravel.log` with context.

### 2. Better Error Messages
Improved user-facing error messages to be more specific:
- "You must be enrolled in the course [Course Name]..."
- "This assessment is currently locked..."
- "This assessment has not been approved yet..."

### 3. Custom 403 Error Page
Created `resources/views/errors/403.blade.php` with:
- Clear explanation of the error
- Helpful tips on why access might be denied
- Action buttons (Dashboard, Go Back)
- Support contact information

### 4. Session Validation Middleware
Added `app/Http/Middleware/EnsureUserExists.php` to prevent orphaned sessions from causing issues. Registered in `bootstrap/app.php`.

### 5. Diagnostic Command
Created `php artisan assessment:diagnose` command for troubleshooting.

## How to Diagnose in Production

Run this command on your production server:

```bash
php artisan assessment:diagnose 266 82
```

This will show:
- Whether assessment 266 exists (or was deleted)
- Assessment details (title, course, status, locked state)
- User details (name, email, roles)
- Which access check is failing
- Specific actions needed to fix

## Quick Fixes

### If assessment doesn't exist:
Assessment 266 may have been deleted. Find the correct assessment ID or recreate it.

### If user not enrolled:
```bash
# In Laravel Tinker or directly in database
php artisan tinker
>>> $user = User::find(82);
>>> $course = Course::find([COURSE_ID]);
>>> $course->enrollments()->create(['user_id' => 82, 'enrolled_at' => now()]);
```

### If assessment is locked:
```bash
php artisan tinker
>>> Assessment::find(266)->update(['is_locked' => false]);
```

### If assessment not approved:
```bash
php artisan tinker
>>> Assessment::find(266)->update(['approval_status' => 'approved', 'approved_at' => now()]);
```

## Check Logs
After the error occurs, check:
```bash
tail -f storage/logs/laravel.log | grep "Assessment access denied"
```

Look for entries like:
```
[timestamp] local.WARNING: Assessment access denied: User not enrolled {"user_id":82,"assessment_id":266,"course_id":3,"reason":"not_enrolled"}
```

## Next Steps
1. Deploy these changes to production
2. Run the diagnostic command
3. Check the logs when the error occurs again
4. Apply the appropriate fix based on which check is failing
