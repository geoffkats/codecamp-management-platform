# 📍 Enrollment System - Where to Click Guide

## 🎯 Quick Navigation

### For STUDENTS:

#### 1. Browse & Enroll in Courses
```
http://127.0.0.1:8000/courses
```
**Steps:**
1. Click **"Courses"** in sidebar (or go to `/courses`)
2. Browse available courses
3. Click on any course to see details
4. Click **"Enroll Now"** button

**What happens:**
- **Open enrollment** → Immediate enrollment ✅
- **Invite-only** → Shows "This course is invite-only" error ❌
- **Approval required** → "Enrollment request submitted" message 📝

#### 2. View Your Enrollments
```
http://127.0.0.1:8000/enrollments
```
**Steps:**
1. Click **"My Enrollments"** in sidebar
2. See all your enrolled courses
3. Click **"Continue Learning"** to start course

---

### For TEACHERS:

#### 1. Create Course & Set Enrollment Type
```
http://127.0.0.1:8000/courses/create
```
**Steps:**
1. Click **"Courses"** → **"Create"** button
2. Fill in course details
3. **NEW:** Scroll to **"Enrollment Settings"** section
4. Select enrollment type:
   - `Open` - Anyone can enroll
   - `Invite Only` - Need invitation
   - `Approval Required` - Need approval
5. Optional: Set **Max Students** limit
6. Click **"Submit for Approval"** or **"Save Draft"**

#### 2. Edit Existing Course
```
http://127.0.0.1:8000/courses/{id}/edit
```
**Steps:**
1. Go to **"My Courses"** 
2. Click **"Edit"** on any course
3. Update **"Enrollment Settings"** section
4. Change enrollment type or max students
5. Click **"Update Course"**

#### 3. Manage Enrollments (NEW!)
```
http://127.0.0.1:8000/courses/{id}/enrollments
```
**Steps:**
1. Go to course details page
2. Click **"Manage Enrollments"** button (need to add this button!)
3. Three tabs available:
   - **Enrollment Requests** - Approve/reject requests
   - **Invitations** - Send/cancel invitations
   - **Enrolled Students** - View current students

**For Invite-Only courses:**
- Click **"Send Invitations"** button
- Search for students
- Select students
- Add optional message
- Set expiration (7 days default)
- Click **"Send"**

**For Approval-Required courses:**
- View pending requests
- Click **"Approve"** or **"Reject"**
- For rejection, provide reason
- Student gets notified automatically

---

## 🖼️ UI Update Needed

### Add "Manage Enrollments" Button to Course Show Page

Edit: `resources/views/livewire/courses/show.blade.php`

Find this section (around line 70-78):
```blade
@if(auth()->check() && auth()->user()->hasRole('teacher') && $course->instructor_id === auth()->id())
    <flux:button href="{{ route('courses.edit', $course) }}" variant="outline" wire:navigate>
        Edit Course
    </flux:button>
    <flux:button href="{{ route('curriculum.builder', $course) }}" variant="primary" wire:navigate>
        Curriculum Builder
    </flux:button>
```

**Add this button:**
```blade
@if(auth()->check() && auth()->user()->hasRole('teacher') && $course->instructor_id === auth()->id())
    <flux:button href="{{ route('courses.edit', $course) }}" variant="outline" wire:navigate>
        Edit Course
    </flux:button>
    <flux:button href="{{ route('curriculum.builder', $course) }}" variant="primary" wire:navigate>
        Curriculum Builder
    </flux:button>
    <!-- NEW: Enrollment Management Button -->
    @if(in_array($course->enrollment_type ?? 'open', ['invite_only', 'approval_required']))
        <flux:button href="{{ route('courses.enrollments', $course) }}" variant="ghost" wire:navigate>
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Manage Enrollments
        </flux:button>
    @endif
```

---

## 📋 Complete Navigation Map

```
Dashboard (/)
│
├─ Courses (/courses)
│  ├─ Course List
│  ├─ Click Course → Course Details (/courses/{id})
│  │  ├─ [STUDENT] → "Enroll Now" button
│  │  └─ [TEACHER] → "Manage Enrollments" button
│  │
│  ├─ Create Course (/courses/create)
│  │  └─ Set enrollment_type & max_students
│  │
│  └─ Edit Course (/courses/{id}/edit)
│     └─ Update enrollment settings
│
├─ My Enrollments (/enrollments)
│  └─ [STUDENT] View enrolled courses
│
└─ Course Enrollments (/courses/{id}/enrollments)
   └─ [TEACHER] Manage invitations & requests
      ├─ Tab 1: Enrollment Requests
      ├─ Tab 2: Invitations
      └─ Tab 3: Enrolled Students
```

---

## 🎨 Visual Flow Diagram

### Student Flow:
```
1. Login
   ↓
2. Browse Courses (/courses)
   ↓
3. Click Course
   ↓
4. Click "Enroll Now"
   ↓
5a. [OPEN] → Enrolled immediately ✅
5b. [INVITE-ONLY] → Error message (need invitation) ❌
5c. [APPROVAL] → Request submitted, wait for teacher 📝
```

### Teacher Flow (Invite-Only):
```
1. Create Course
   ↓
2. Set enrollment_type = "invite_only"
   ↓
3. Go to Course Details
   ↓
4. Click "Manage Enrollments"
   ↓
5. Click "Invitations" tab
   ↓
6. Click "Send Invitations" button
   ↓
7. Search & select students
   ↓
8. Click "Send"
   ↓
9. Students receive notification
```

### Teacher Flow (Approval Required):
```
1. Create Course
   ↓
2. Set enrollment_type = "approval_required"
   ↓
3. Students submit enrollment requests
   ↓
4. Teacher receives notification
   ↓
5. Teacher goes to "Manage Enrollments"
   ↓
6. Click "Enrollment Requests" tab
   ↓
7. Review request
   ↓
8. Click "Approve" or "Reject"
   ↓
9. Student receives notification
```

---

## 🚀 Testing Instructions

### Test Open Enrollment:
1. Teacher creates course with `enrollment_type = "open"`
2. Student goes to `/courses`
3. Student clicks course
4. Student clicks "Enroll Now"
5. ✅ Student is enrolled immediately

### Test Invite-Only:
1. Teacher creates course with `enrollment_type = "invite_only"`
2. Teacher goes to `/courses/{id}/enrollments`
3. Teacher sends invitation to Student
4. Student receives notification
5. Student clicks "Enroll Now"
6. ✅ Student is enrolled (invitation accepted)

### Test Approval Required:
1. Teacher creates course with `enrollment_type = "approval_required"`
2. Student clicks "Enroll Now"
3. Request created (status: pending)
4. Teacher gets notification
5. Teacher goes to `/courses/{id}/enrollments`
6. Teacher clicks "Approve"
7. ✅ Student is enrolled

---

## 📝 Current Status

✅ **Working:**
- Database tables created
- Models created
- Backend logic in `Courses/Show.php`
- ManageEnrollments component created
- Route added: `/courses/{id}/enrollments`
- Create/Edit forms updated with enrollment fields

❌ **Need to Add:**
1. "Manage Enrollments" button in course show page
2. Enrollment settings section in create/edit views
3. View for ManageEnrollments component

---

## 🎯 Next Steps

1. **Update Course Show View** - Add "Manage Enrollments" button
2. **Update Course Create View** - Add enrollment settings fields
3. **Update Course Edit View** - Add enrollment settings fields
4. **Create ManageEnrollments View** - Build the UI
5. **Test each enrollment type** - Verify everything works

Would you like me to add these UI elements now?


