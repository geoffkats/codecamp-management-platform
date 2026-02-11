# Lesson Approval Workflow

## Overview

Teachers cannot auto-approve their own lessons. All lessons must go through an approval process by Supervisors or Admins.

## Roles & Permissions

### Teachers
- ✅ Can create lessons (status: `draft`)
- ✅ Can edit their own lessons
- ✅ Can submit lessons for approval
- ❌ **CANNOT** approve their own lessons
- ❌ **CANNOT** publish unapproved lessons
- ❌ **CANNOT** set `approval_status` to `approved`

### Supervisors
- ✅ Can view all lessons
- ✅ Can approve/reject lessons
- ✅ Can access Curriculum Builder
- ✅ Can view Content Approval queue
- ✅ Can publish approved lessons

### Admins
- ✅ Full access to all lessons
- ✅ Can approve/reject lessons
- ✅ Can override any status
- ✅ Can publish any lesson

## Approval Statuses

| Status | Description | Who Can Set |
|--------|-------------|-------------|
| `draft` | Initial state, not submitted | Teacher, Supervisor, Admin |
| `pending` | Submitted for review | Teacher (via submit button), Supervisor, Admin |
| `approved` | Reviewed and approved | **Supervisor, Admin ONLY** |
| `rejected` | Reviewed and rejected | Supervisor, Admin |

## Workflow Steps

### 1. Teacher Creates Lesson
```
Status: draft
Published: false
```

Teacher fills out lesson details in Curriculum Builder and saves.

### 2. Teacher Submits for Approval
```
Status: draft → pending
Published: false
Submitted at: [timestamp]
```

Teacher clicks "Submit for Approval" button. This:
- Changes status to `pending`
- Creates a `ContentApproval` record
- Notifies Supervisors/Admins
- Prevents further editing of approval status

### 3. Supervisor/Admin Reviews
```
Status: pending → approved/rejected
Published: false (until manually published)
```

Supervisor/Admin goes to Content Approval page and:
- Reviews lesson content
- Approves or rejects with notes
- If approved: lesson can now be published
- If rejected: teacher can edit and resubmit

### 4. Publishing (After Approval)
```
Status: approved
Published: false → true
```

Only after approval can the lesson be published:
- Supervisor/Admin sets `is_published = true`
- Lesson becomes visible to students

## Security Measures

### 1. Controller Level (Builder.php)
```php
// Teachers can NEVER approve their own lessons
if (!$canApprove) {
    if ($data['approval_status'] === 'approved') {
        $data['approval_status'] = 'draft';
        session()->flash('error', 'Teachers cannot approve their own lessons.');
    }
}
```

### 2. Model Level (Lesson.php)
```php
// Model observer prevents approval status manipulation
static::updating(function ($lesson) {
    if ($lesson->approval_status === 'approved') {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSupervisor()) {
            // Revert to original status
            $lesson->approval_status = $lesson->getOriginal('approval_status');
        }
    }
});
```

### 3. Publishing Prevention
```php
// Cannot publish unapproved lessons
if ($lesson->is_published && $lesson->approval_status !== 'approved') {
    $lesson->is_published = false;
}
```

## UI/UX Flow

### Teacher View (Curriculum Builder)

**Draft Lesson:**
```
[Edit] [Submit for Approval] [Delete]
Status: Draft
```

**Pending Lesson:**
```
[View] (Edit disabled)
Status: Pending Approval
Message: "This lesson is awaiting approval from a supervisor or admin."
```

**Approved Lesson:**
```
[Edit] [View]
Status: Approved
Message: "This lesson has been approved and can be published."
```

**Rejected Lesson:**
```
[Edit] [Resubmit]
Status: Rejected
Reason: "[rejection reason from supervisor]"
```

### Supervisor/Admin View (Content Approval)

```
Pending Lessons:
┌─────────────────────────────────────────┐
│ Lesson: "Introduction to Python"       │
│ Teacher: John Doe                       │
│ Submitted: 2 hours ago                  │
│                                         │
│ [View Details] [Approve] [Reject]      │
└─────────────────────────────────────────┘
```

## API/Form Validation

### Lesson Creation/Update Rules

```php
// Teachers
'approval_status' => 'in:draft,pending', // Cannot set to 'approved'
'is_published' => 'boolean', // Will be forced to false if not approved

// Supervisors/Admins
'approval_status' => 'in:draft,pending,approved,rejected',
'is_published' => 'boolean',
```

## Database Schema

```sql
lessons table:
- approval_status: enum('draft', 'pending', 'approved', 'rejected')
- submitted_for_approval_at: timestamp
- approved_at: timestamp
- approved_by: foreign key to users
- approval_notes: text
- rejection_reason: text
- is_published: boolean

content_approvals table:
- approvable_type: 'App\Models\Lesson'
- approvable_id: lesson_id
- status: enum('pending', 'approved', 'rejected')
- submitted_by: foreign key to users
- reviewed_by: foreign key to users
- submitted_at: timestamp
- reviewed_at: timestamp
- category: 'lesson'
- priority: enum('low', 'normal', 'high')
```

## Testing Checklist

### Teacher Tests
- [ ] Create lesson → status is 'draft'
- [ ] Try to set approval_status to 'approved' → fails
- [ ] Submit for approval → status becomes 'pending'
- [ ] Try to publish unapproved lesson → fails
- [ ] Edit pending lesson → approval_status preserved
- [ ] Edit rejected lesson → can resubmit

### Supervisor Tests
- [ ] View pending lessons in approval queue
- [ ] Approve lesson → status becomes 'approved'
- [ ] Reject lesson with reason → status becomes 'rejected'
- [ ] Publish approved lesson → is_published = true

### Admin Tests
- [ ] All supervisor tests pass
- [ ] Can override any status
- [ ] Can force publish (not recommended)

## Notifications

### When Lesson Submitted
- **To**: All Supervisors and Admins
- **Message**: "New lesson submitted for approval: [Lesson Title] by [Teacher Name]"

### When Lesson Approved
- **To**: Lesson creator (Teacher)
- **Message**: "Your lesson '[Lesson Title]' has been approved!"

### When Lesson Rejected
- **To**: Lesson creator (Teacher)
- **Message**: "Your lesson '[Lesson Title]' was rejected. Reason: [rejection reason]"

## Common Issues & Solutions

### Issue: Teacher sees "approved" option in form
**Solution**: Remove approval_status field from teacher's form view

### Issue: Lesson published without approval
**Solution**: Model observer prevents this automatically

### Issue: Teacher bypasses approval via API
**Solution**: Controller and model both validate permissions

### Issue: Supervisor can't find pending lessons
**Solution**: Check Content Approval page, not Curriculum Builder

## Future Enhancements

1. **Email Notifications**: Send emails when lessons are submitted/approved/rejected
2. **Approval History**: Track all status changes with timestamps
3. **Bulk Approval**: Allow supervisors to approve multiple lessons at once
4. **Auto-Approval Rules**: Set criteria for automatic approval (e.g., experienced teachers)
5. **Revision Requests**: Allow supervisors to request specific changes
6. **Approval Workflow**: Multi-step approval (peer review → supervisor → admin)

## Summary

✅ **Teachers CANNOT approve their own lessons**
✅ **All lessons must be approved by Supervisor or Admin**
✅ **Unapproved lessons cannot be published**
✅ **Security enforced at multiple levels (Controller, Model, Database)**
✅ **Clear workflow with proper notifications**

This ensures quality control and prevents unauthorized content from being published to students.
