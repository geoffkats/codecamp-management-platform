# Curriculum Builder - Debugging Guide

## Current Issue
Curriculum Builder not working after Tiptap removal.

## What Was Fixed

### 1. ✅ Added `updatedCourseId()` Method
```php
public function updatedCourseId()
{
    // This method is automatically called when courseId changes
    $this->loadCourse();
}
```

This ensures that when you select a course from the dropdown, it automatically loads.

### 2. ✅ Improved `loadCourse()` Method
- Resets `$course` and `$modules` before loading
- Checks if `courseId` is empty
- Added try-catch for error handling
- Shows user-friendly error messages
- Removed caching temporarily
- Added supervisor permission check

### 3. ✅ Cleared All Caches
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## How to Test

### Step 1: Check Browser Console
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for any JavaScript errors
4. **Common errors to look for:**
   - Livewire errors
   - Alpine.js errors
   - Network errors

### Step 2: Check Laravel Logs
```bash
# View last 50 lines of log
Get-Content storage\logs\laravel.log -Tail 50
```

Look for:
- PHP errors
- Database errors
- Permission errors

### Step 3: Test Course Loading
1. Go to Curriculum Builder: `/curriculum/builder`
2. Select a course from dropdown
3. **Expected**: Modules, lessons, assessments should appear
4. **If not working**: Check console and logs

### Step 4: Check Network Tab
1. Open DevTools → Network tab
2. Select a course
3. Look for Livewire requests
4. Check if they're returning 200 OK or errors

## Common Issues & Solutions

### Issue 1: "Course not found" Error
**Cause**: Course doesn't exist or no permission

**Solution**:
1. Check if course exists in database
2. For teachers: Can only view own courses
3. For supervisors/admins: Can view all courses

**Test**:
```bash
php artisan tinker
>>> App\Models\Course::count()
>>> App\Models\Course::where('instructor_id', 1)->get()
```

### Issue 2: Nothing Happens When Selecting Course
**Cause**: Livewire not working or JavaScript error

**Solution**:
1. Check browser console for errors
2. Verify Livewire is loaded: Check for `window.Livewire` in console
3. Check if Alpine.js is loaded: Check for `window.Alpine` in console

**Test in Browser Console**:
```javascript
// Check if Livewire is loaded
console.log(window.Livewire);

// Check if Alpine is loaded
console.log(window.Alpine);

// Check for Livewire components
console.log(Livewire.all());
```

### Issue 3: Modules Not Showing
**Cause**: Course has no modules or database issue

**Solution**:
1. Check if course has modules in database
2. Check relationships are working

**Test**:
```bash
php artisan tinker
>>> $course = App\Models\Course::find(1)
>>> $course->modules
>>> $course->modules->count()
```

### Issue 4: JavaScript Errors
**Cause**: Asset build issues or conflicts

**Solution**:
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Issue 5: Livewire Not Updating
**Cause**: Wire model not working

**Solution**:
1. Check `wire:model.live="courseId"` is in the select
2. Check `updatedCourseId()` method exists
3. Check no JavaScript errors blocking Livewire

## Diagnostic Commands

### Check Database
```bash
php artisan tinker

# Count courses
>>> App\Models\Course::count()

# Get user's courses
>>> App\Models\Course::where('instructor_id', 1)->get()

# Check course with modules
>>> $course = App\Models\Course::with('modules')->first()
>>> $course->modules->count()
```

### Check Livewire
```bash
# List all Livewire components
php artisan livewire:list

# Should show: curriculum.builder
```

### Check Routes
```bash
# Check if route exists
php artisan route:list | findstr curriculum

# Should show: curriculum.builder route
```

### Check Permissions
```bash
php artisan tinker

# Check user roles
>>> $user = App\Models\User::find(1)
>>> $user->roles
>>> $user->isAdmin()
>>> $user->isTeacher()
>>> $user->isSupervisor()
```

## Manual Test Steps

### 1. Basic Page Load
- [ ] Go to `/curriculum/builder`
- [ ] Page loads without errors
- [ ] Course dropdown is visible
- [ ] No JavaScript errors in console

### 2. Course Selection
- [ ] Select a course from dropdown
- [ ] Modules section appears
- [ ] Lessons section appears
- [ ] Assessments section appears
- [ ] No errors in console

### 3. Module Creation
- [ ] Click "+ Add" in Modules section
- [ ] Modal opens
- [ ] Fill in module details
- [ ] Click "Save"
- [ ] Module appears in list

### 4. Lesson Creation
- [ ] Click "+ Add" in Lessons section
- [ ] Modal opens
- [ ] Select module
- [ ] Fill in lesson details
- [ ] Click "Save"
- [ ] Lesson appears in list

## Expected Behavior

### When Page Loads
1. Course dropdown shows all available courses
2. Empty state message if no course selected
3. No JavaScript errors

### When Course Selected
1. Livewire sends request to server
2. `updatedCourseId()` is called
3. `loadCourse()` loads course data
4. Modules, lessons, assessments are displayed
5. Stats cards show correct counts

### When Creating Items
1. Modal opens with form
2. Form fields are editable
3. Save button works
4. Item appears in list
5. Modal closes

## If Still Not Working

### 1. Check File Permissions
```bash
# Windows
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

### 2. Regenerate Autoload
```bash
composer dump-autoload
```

### 3. Clear Everything
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
npm run build
```

### 4. Check .env File
```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

### 5. Enable Query Logging
Add to `app/Providers/AppServiceProvider.php`:
```php
public function boot()
{
    \DB::listen(function($query) {
        \Log::info($query->sql);
        \Log::info($query->bindings);
    });
}
```

## Get Help

If still not working, provide:
1. Browser console errors (screenshot)
2. Laravel log errors (last 50 lines)
3. Network tab showing Livewire requests
4. Steps to reproduce the issue

## Summary

✅ **Fixed**: Added `updatedCourseId()` method
✅ **Fixed**: Improved error handling in `loadCourse()`
✅ **Fixed**: Removed caching temporarily
✅ **Fixed**: Added supervisor permissions
✅ **Cleared**: All Laravel caches

**Next**: Test the curriculum builder and check console for errors.
