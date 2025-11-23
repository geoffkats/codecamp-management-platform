# Lesson Re-Approval Workflow

## Overview
Implemented a comprehensive approval workflow that requires lessons to be re-approved when teachers update already-approved content.

## Features Implemented

### 1. Automatic Re-Approval on Update
- ✅ When a teacher updates an **approved** lesson, it automatically changes to **pending** status
- ✅ Admin/Supervisor approval is required before the lesson is approved again
- ✅ Admins and supervisors can update lessons without triggering re-approval

### 2. Notifications System
**For Teachers:**
- Notified when their lesson is approved
- Notified when their lesson is rejected/disapproved with reason

**For Admins/Supervisors:**
- Notified when a new lesson is created and needs approval
- Notified when an approved lesson is updated and needs re-approval

### 3. Approval Actions

**Approve Lesson:**
- Admins/supervisors can approve pending lessons
- Teacher receives notification of approval
- Lesson status changes to "approved"

**Disapprove Lesson:**
- Admins/supervisors can disapprove even **approved** lessons
- Must provide a reason for disapproval
- Teacher receives notification with the reason
- Lesson status changes to "rejected"

### 4. Visual Indicators

**In Sidebar:**
- ✅ Green checkmark for approved lessons
- ⏳ Yellow clock for pending lessons
- ✗ Red X for rejected lessons
- 📄 Gray document for draft lessons

**In Lesson Form:**
- Warning message when updating approved lessons (teachers only)
- Approval status badge with color coding
- Rejection reason display for rejected lessons
- Approval timestamp for approved lessons

### 5. User Experience

**Teachers:**
1. Create lesson → Auto-set to "pending"
2. Wait for admin/supervisor approval
3. If rejected → See reason and can resubmit
4. If approved → Can view/edit
5. Update approved lesson → Auto-sent for re-approval with warning

**Admins/Supervisors:**
1. Receive notification of pending lessons
2. Review lesson content
3. Can approve or reject with reason
4. Can disapprove previously approved lessons
5. Their own lessons are auto-approved

## Database Fields Used
- `approval_status`: 'draft', 'pending', 'approved', 'rejected'
- `submitted_for_approval_at`: Timestamp when submitted
- `approved_at`: Timestamp when approved
- `approved_by`: User ID of approver
- `rejection_reason`: Text explaining why rejected

## Notification Types
- `lesson_approval`: New lesson needs approval
- `lesson_approved`: Lesson was approved
- `lesson_rejected`: Lesson was rejected/disapproved

## UI Components

### Approval Section (Right Sidebar)
```
For Admins/Supervisors:
- Approve button (green)
- Reject/Disapprove button (red/orange)
- Approval status display

For Teachers:
- Status indicator
- Rejection reason (if rejected)
- Resubmit button (if rejected)
- Re-approval warning (when updating approved)
```

### Rejection Modal
- Textarea for rejection reason (required)
- Confirm/Cancel buttons
- Loading states
- Works for both reject and disapprove actions

## Workflow Diagram

```
Teacher Creates Lesson
        ↓
   Status: Pending
        ↓
Admin/Supervisor Notified
        ↓
    Review Lesson
        ↓
    ┌───────┴───────┐
    ↓               ↓
 Approve         Reject
    ↓               ↓
Status:         Status:
Approved        Rejected
    ↓               ↓
Teacher         Teacher
Notified        Notified
                (with reason)
    ↓               ↓
Teacher         Teacher
Updates         Fixes &
Lesson          Resubmits
    ↓               ↓
Status:         Status:
Pending         Pending
(Re-approval)       ↓
    ↓           (Loop back)
Admin/Supervisor
Notified Again
```

## Benefits

1. **Quality Control**: Ensures all lesson updates are reviewed
2. **Accountability**: Clear audit trail of who approved what
3. **Communication**: Automatic notifications keep everyone informed
4. **Flexibility**: Admins can disapprove lessons if issues are found later
5. **Transparency**: Teachers always know the status and reasons for rejection

## Testing Checklist

- [ ] Teacher creates new lesson → Goes to pending
- [ ] Admin receives notification for new lesson
- [ ] Admin approves lesson → Teacher receives notification
- [ ] Admin rejects lesson with reason → Teacher sees reason
- [ ] Teacher updates approved lesson → Goes back to pending
- [ ] Admin receives notification for updated lesson
- [ ] Admin can disapprove approved lesson
- [ ] Teacher receives disapproval notification with reason
- [ ] Admin/Supervisor lessons auto-approve
- [ ] Sidebar shows correct status icons
- [ ] Warning appears when updating approved lesson
