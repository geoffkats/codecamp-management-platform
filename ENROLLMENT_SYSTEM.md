# Course Enrollment System - Documentation

## Overview

The system now supports **3 enrollment types** to give teachers/admins full control over who can enroll:

1. **Open Enrollment** (default) - Any student can enroll
2. **Invite-Only** - Students need an invitation from teacher/admin
3. **Approval Required** - Students submit requests that must be approved

## Enrollment Types

### 1. Open Enrollment (`enrollment_type = 'open'`)

**How it works:**
- Any authenticated student can click "Enroll" and immediately join
- Only requires course to be published and approved
- No manual intervention needed

**Best for:**
- Public courses
- Large MOOCs
- Self-paced learning

### 2. Invite-Only (`enrollment_type = 'invite_only'`)

**How it works:**
- Teacher/admin must send invitations to specific students
- Students receive invitation notification
- Students can accept or decline invitation
- Invitations can have expiration dates
- Without invitation, students see "This course is invite-only" error

**Best for:**
- Private cohorts
- Premium courses
- Selected groups

**Teacher actions:**
```php
// Send invitation
CourseInvitation::create([
    'course_id' => $courseId,
    'user_id' => $studentId,
    'invited_by' => Auth::id(),
    'status' => 'pending',
    'invited_at' => now(),
    'expires_at' => now()->addDays(7), // Optional
    'message' => 'You are invited to join...',
]);
```

### 3. Approval Required (`enrollment_type = 'approval_required'`)

**How it works:**
- Student clicks "Enroll" → Creates enrollment request (status: pending)
- Teacher/admin receives notification
- Teacher/admin reviews and approves/rejects
- Upon approval → Student is enrolled
- Upon rejection → Student is notified with reason

**Best for:**
- Limited capacity courses
- Prerequisite checks
- Interview-based enrollment

**Student action:**
- Clicks "Enroll" button
- System creates `EnrollmentRequest` (pending)
- Sees "Enrollment request submitted. Waiting for instructor approval."

**Teacher action:**
```php
// Approve request
$request = EnrollmentRequest::find($id);
$request->update([
    'status' => 'approved',
    'reviewed_by' => Auth::id(),
    'reviewed_at' => now(),
]);

// Create actual enrollment
CourseEnrollment::create([
    'user_id' => $request->user_id,
    'course_id' => $request->course_id,
    'enrolled_at' => now(),
]);
```

## Additional Features

### Max Students Limit

Set `max_students` field to limit course capacity:
```php
$course->max_students = 50; // Only 50 students can enroll
```

When limit is reached, students see:
> "This course has reached maximum enrollment capacity."

### Enrollment Checks (All Types)

Before enrollment, system checks:
1. ✅ User is authenticated
2. ✅ Not already enrolled
3. ✅ Course is published
4. ✅ Course is approved
5. ✅ Enrollment type requirements met
6. ✅ Max students not reached

## Database Schema

### courses table (new fields)
```sql
enrollment_type ENUM('open', 'invite_only', 'approval_required') DEFAULT 'open'
max_students INT NULL
```

### course_invitations table
```sql
id, course_id, user_id, invited_by, status, invited_at, 
expires_at, responded_at, message, timestamps
```

### enrollment_requests table
```sql
id, course_id, user_id, status, message, rejection_reason,
reviewed_by, requested_at, reviewed_at, timestamps
```

## Models

- `CourseInvitation` - Manages course invitations
- `EnrollmentRequest` - Manages enrollment requests

## Notifications

**Instructor receives:**
- New enrollment request notification

**Student receives:**
- Invitation notification (if invited)
- Approval/rejection notification (if approval_required)

## Setting Enrollment Type

### In Course Create/Edit Form:
```php
<flux:select wire:model="enrollment_type">
    <option value="open">Open Enrollment</option>
    <option value="invite_only">Invite Only</option>
    <option value="approval_required">Approval Required</option>
</flux:select>

<flux:input 
    wire:model="max_students" 
    type="number"
    placeholder="Max students (optional)"
/>
```

## Usage Examples

### Example 1: Private Bootcamp
```php
$course = Course::create([
    'title' => 'Elite Coding Bootcamp',
    'enrollment_type' => 'invite_only',
    'max_students' => 25,
    // ... other fields
]);

// Send invitations to selected students
foreach ($selectedStudents as $student) {
    CourseInvitation::create([
        'course_id' => $course->id,
        'user_id' => $student->id,
        'invited_by' => Auth::id(),
        'invited_at' => now(),
        'expires_at' => now()->addDays(14),
    ]);
}
```

### Example 2: University Course
```php
$course = Course::create([
    'title' => 'Advanced Algorithms CS401',
    'enrollment_type' => 'approval_required',
    'max_students' => 40,
    // ... other fields
]);

// Students apply → Prof reviews prerequisites → Approves
```

### Example 3: Open MOOC
```php
$course = Course::create([
    'title' => 'Introduction to Python',
    'enrollment_type' => 'open', // Default
    'max_students' => null, // Unlimited
    // ... other fields
]);

// Anyone can enroll immediately
```

## Migration Command

```bash
php artisan migrate
```

This creates:
- `course_invitations` table
- `enrollment_requests` table
- Adds `enrollment_type` and `max_students` to `courses` table

## Next Steps

Teachers/admins should:
1. Review existing courses and set appropriate `enrollment_type`
2. For invite-only courses, create invitations
3. Monitor enrollment requests for approval_required courses

## Security

- All enrollment checks happen server-side in `Courses/Show.php::enroll()`
- Students cannot bypass restrictions
- Only course instructors and admins can:
  - Send invitations
  - Approve/reject requests
  - View enrollment requests/invitations

