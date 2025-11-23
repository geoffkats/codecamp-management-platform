# Enrollment Management Access Control

## Who Can Manage Enrollments?

### ✅ FULL ACCESS:
1. **Course Instructor (Teacher)** - Can manage their own course enrollments
2. **Admin** - Can manage ALL course enrollments
3. **Supervisor** - Can manage ALL course enrollments

### 📋 Permissions by Role:

| Action | Student | Teacher (Owner) | Teacher (Other) | Admin | Supervisor |
|--------|---------|-----------------|-----------------|-------|------------|
| View Enrollment Requests | ❌ | ✅ | ❌ | ✅ | ✅ |
| Approve/Reject Requests | ❌ | ✅ | ❌ | ✅ | ✅ |
| Send Invitations | ❌ | ✅ | ❌ | ✅ | ✅ |
| View Enrolled Students | ❌ | ✅ | ❌ | ✅ | ✅ |
| Cancel Invitations | ❌ | ✅ | ❌ | ✅ | ✅ |

## Access Control Code

### Authorization Check (app/Livewire/Courses/ManageEnrollments.php):
```php
public function mount(Course $course)
{
    // Check authorization - instructor, admin, or supervisor can manage
    if (!Auth::user()->hasAnyRole(['admin', 'supervisor']) && $course->instructor_id !== Auth::id()) {
        abort(403, 'Unauthorized');
    }
    
    $this->course = $course;
}
```

### Button Visibility (resources/views/livewire/courses/show.blade.php):

**For Course Instructor:**
- Shows "Edit Course" button
- Shows "Curriculum Builder" button
- Shows "Manage Enrollments" button (if invite-only or approval-required)

**For Admin/Supervisor:**
- Shows "Edit Course" button (can edit ANY course)
- Shows "View All Enrollments" button (always visible)
- Shows "Manage Enrollments" button (if invite-only or approval-required)

**For Students:**
- Shows "Enroll Now" button
- Shows "Continue Learning" button (if enrolled)

## Use Cases

### Use Case 1: Admin Needs to Help Teacher
**Scenario:** Teacher can't access system, student needs urgent approval

**Solution:**
1. Admin goes to course page
2. Clicks "View All Enrollments"
3. Approves student's request
4. Student can now enroll

### Use Case 2: Supervisor Audits Enrollments
**Scenario:** Supervisor needs to review all pending enrollment requests

**Solution:**
1. Supervisor goes to any course
2. Clicks "View All Enrollments"
3. Reviews all pending requests
4. Can approve/reject as needed

### Use Case 3: Admin Sends Invitations
**Scenario:** Admin wants to invite VIP students to exclusive course

**Solution:**
1. Admin goes to course
2. Clicks "View All Enrollments"
3. Goes to "Invitations" tab
4. Searches for students
5. Sends invitations

## Testing

### Test Admin Access:
```bash
# Login as Admin
# Go to: http://127.0.0.1:8000/courses/{id}
# You should see "View All Enrollments" button
# Click it
# You should access /courses/{id}/enrollments
```

### Test Supervisor Access:
```bash
# Login as Supervisor
# Go to: http://127.0.0.1:8000/courses/{id}
# You should see "View All Enrollments" button
# Click it
# You should access /courses/{id}/enrollments
```

### Test Teacher (Non-Owner) Access:
```bash
# Login as Teacher (NOT the course instructor)
# Go to: http://127.0.0.1:8000/courses/{id}
# You should NOT see "Manage Enrollments" button
# If you manually try /courses/{id}/enrollments
# You should get 403 Forbidden error
```

## Security Notes

✅ Authorization happens server-side in `mount()` method
✅ Button visibility is just UI convenience - doesn't affect security
✅ Route is protected by authentication middleware
✅ Direct URL access is blocked by `abort(403)` for unauthorized users

## Updated Flow

```
User tries to access /courses/{id}/enrollments
    ↓
Is user authenticated?
    ↓ No → Redirect to login
    ↓ Yes
    ↓
Is user Admin OR Supervisor?
    ↓ Yes → ✅ Allow access
    ↓ No
    ↓
Is user the course instructor?
    ↓ Yes → ✅ Allow access
    ↓ No → ❌ 403 Forbidden
```





