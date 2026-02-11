# 📧 Course Invitations - Complete Guide

## Where Invitations Go

When a teacher or admin sends a course invitation, it:

1. **Creates a record** in the `course_invitations` table
2. **Sends a notification** to the student
3. **Stores invitation details**:
   - Course information
   - Inviter (who sent it)
   - Personal message (optional)
   - Expiration date
   - Status (pending/accepted/declined/expired)

## How Students Access Invitations

Students can access their invitations in **two ways**:

### 1. **Through the Sidebar Menu** 📋

- Click **"Invitations"** in the sidebar (under "Learning" section for students)
- The menu item shows a **badge** with the count of pending invitations
- Direct URL: `/invitations` or `http://127.0.0.1:8000/invitations`

### 2. **Through Notifications** 🔔

- When an invitation is sent, students receive a **notification**
- Click the notification to go directly to the course or invitation
- View all notifications: `/notifications`

## What Students Can Do with Invitations

### View All Invitations Page

The invitations page shows:

- **Stats Cards**: Pending, Accepted, Declined, Expired counts
- **Filter Tabs**: Filter by status (Pending, Accepted, Declined, Expired, All)
- **Invitation Cards**: Each card shows:
  - Course title and thumbnail
  - Who invited them
  - Personal message (if any)
  - Sent date and expiration date
  - Status badge

### Actions Available

**For Pending Invitations:**
- ✅ **Accept** - Enrolls in the course immediately
- ❌ **Decline** - Rejects the invitation

**For Accepted Invitations:**
- 🎓 **Go to Course** - Navigate to start learning

**For Declined/Expired:**
- 👁️ **View Course** - See course details (can request enrollment if course allows)

## Acceptance Flow

When a student accepts an invitation:

1. **Checks enrollment** - Verifies student isn't already enrolled
2. **Checks expiration** - Ensures invitation hasn't expired
3. **Checks capacity** - Verifies course hasn't reached max students
4. **Accepts invitation** - Updates invitation status to "accepted"
5. **Creates enrollment** - Adds student to the course
6. **Awards points** - Gives 50 XP for enrollment
7. **Shows success message** - Confirms enrollment

## Enrollment Types & Invitations

### Invite-Only Courses
- **Only** students with invitations can enroll
- Students must accept invitation before enrolling
- If they try to enroll without invitation → Error message shown

### Approval-Required Courses
- Students request enrollment
- Teachers/admins approve/reject requests
- No invitations needed (different system)

### Open Enrollment Courses
- No invitations needed
- Anyone can enroll directly

## Notification Integration

Invitations automatically create notifications:

- **Title**: "Course Invitation!"
- **Message**: Includes course name and inviter name
- **Data**: Contains course ID and invitation ID for direct linking
- **Type**: "info"

## Quick Access Routes

| Action | Route | URL |
|--------|-------|-----|
| View Invitations | `invitations.index` | `/invitations` |
| Accept Invitation | Livewire action | Click "Accept" button |
| Decline Invitation | Livewire action | Click "Decline" button |
| Go to Course | `courses.show` | `/courses/{id}` |
| Start Learning | `courses.learn` | `/courses/{id}/learn` |

## For Teachers/Admins

### Sending Invitations

1. **Course-Specific**: Go to course → "Manage Enrollments" → "Invitations" tab → "Send Invitations"
2. **Global**: Go to "Enrollment Management" (admin sidebar) → "Invitations" tab → "Send New Invitation"

### Who Can Send Invitations?

- **Course Teacher** - For their own courses
- **Admin** - For any course
- **Supervisor** - For any course

## Tips for Students

- ✅ **Check notifications regularly** - New invitations appear as notifications
- ✅ **Act quickly** - Invitations can expire
- ✅ **Check sidebar badge** - See pending invitation count at a glance
- ✅ **Filter by status** - Use tabs to find specific invitations
- ✅ **Read messages** - Personal messages from teachers appear on invitation cards

## System Features

- **Automatic expiration** - Expired invitations marked automatically
- **Duplicate prevention** - Can't accept if already enrolled
- **Capacity checking** - Can't accept if course is full
- **Point rewards** - Get 50 XP when accepting invitation
- **Progress tracking** - Enrollment automatically tracked





