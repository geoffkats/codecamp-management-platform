# Code Academy Uganda - Platform Testing Guide

## Overview
This document provides comprehensive testing scenarios for the Code Academy Uganda E-Learning Platform. Use this guide to systematically test all features and ensure the platform works correctly.

---

## Table of Contents
1. [User Registration & Authentication](#1-user-registration--authentication)
2. [System Settings (Admin Only)](#2-system-settings-admin-only)
3. [Student Management](#3-student-management)
4. [Course Management](#4-course-management)
5. [Enrollment & Invitations](#5-enrollment--invitations)
6. [Attendance Tracking](#6-attendance-tracking)
7. [Learning Experience](#7-learning-experience)
8. [Dashboards](#8-dashboards)
9. [Content Approval Workflow](#9-content-approval-workflow)
10. [Gamification Features](#10-gamification-features)

---

## Test User Accounts

### Admin Account
- **Email:** admin  account 
- **Password:** [Your admin password]
- **Access:** Full system access

### Operations Manager Account
- **Email:** operations@example.com
- **Password:** [password]
- **Access:** Student management, attendance

### Teacher Account
- **Email:** teacher@example.com
- **Password:** [password]
- **Access:** Course creation, student updates

### Supervisor Account
- **Email:** supervisor@example.com
- **Password:** [password]
- **Access:** Course creation,

### Student Account
- **Email:** student@example.com
- **Password:** [password]
- **Access:** Course enrollment, learning

---

## 1. User Registration & Authentication

### Test 1.1: New User Registration
**Steps:**
1. Go to the homepage
2. Click "Get Started" or "Register"
3. Fill in registration form:
   - Name: Test User
   - Email: testuser@example.com
   - Password: TestPass123!
   - Confirm Password: TestPass123!
4. Click "Create Account"

**Expected Result:**
- Account created successfully
- User redirected to dashboard
- Dashboard shows "Student Registration Required" message
- User cannot access courses until registered as student

**Status:** ☐ Pass ☐ Fail

---

### Test 1.2: User Login
**Steps:**
1. Go to login page
2. Enter credentials
3. Click "Sign In"

**Expected Result:**
- User logged in successfully
- Redirected to appropriate dashboard based on role
- Navigation sidebar shows role-specific menu items

**Status:** ☐ Pass ☐ Fail

---

### Test 1.3: Password Reset
**Steps:**
1. Click "Forgot password?" on login page
2. Enter email address
3. Check email for reset link
4. Click link and set new password

**Expected Result:**
- Reset email sent
- Password updated successfully
- Can login with new password

**Status:** ☐ Pass ☐ Fail

---

## 2. System Settings (Admin Only)

### Test 2.1: Update Branding
**Steps:**
1. Login as Admin
2. Go to Administration → System Settings
3. Update the following:
   - Application Name: "My Code Academy"
   - Short Name: "MCA"
   - Tagline: "Learn to Code"
4. Upload favicon (32x32 PNG)
5. Upload logo (PNG with transparent background)
6. Click "Save Settings"

**Expected Result:**
- Settings saved successfully
- Favicon appears in browser tab
- Logo appears in navbar and sidebar
- App name updated throughout platform

**Status:** ☐ Pass ☐ Fail

---

### Test 2.2: Update Contact Information
**Steps:**
1. In System Settings, update:
   - Contact Email: info@myacademy.com
   - Contact Phone: +256 123 456789
   - Address: New address
2. Save changes

**Expected Result:**
- Contact info updated
- Appears on welcome page and student dashboard

**Status:** ☐ Pass ☐ Fail

---

## 3. Student Management

### Test 3.1: Register New Student (Operations Manager)
**Steps:**
1. Login as Operations Manager
2. Go to Students → Manage Students
3. Click "Register New Student"
4. Fill in student information:
   - **Personal Info:** Name, DOB, Gender, Email, Phone
   - **Parent 1:** Name, Phone, Email, Relationship
   - **Parent 2:** Name, Phone, Email, Relationship
   - **Gadgets:** Add laptop/tablet details
   - **Uniform:** Size, issued date
   - **Learning Accounts:** Scratch username/password, GitHub
5. Click "Register Student"

**Expected Result:**
- Student profile created
- Student ID generated (STU-2025-XXXX format)
- Student can now access courses
- Profile visible in student list

**Status:** ☐ Pass ☐ Fail

---

### Test 3.2: View Student Profile
**Steps:**
1. Go to Students → Manage Students
2. Click on a student name
3. Review all sections:
   - Personal Information
   - Parent/Guardian Details
   - Gadgets & Devices
   - Uniform Information
   - Learning Accounts
   - Enrolled Courses

**Expected Result:**
- All information displayed correctly
- Sections organized and readable
- Edit button visible for authorized users

**Status:** ☐ Pass ☐ Fail

---

### Test 3.3: Update Student Profile (Teacher)
**Steps:**
1. Login as Teacher
2. Go to Students → Manage Students
3. Click on student, then "Update Learning Profile"
4. Update:
   - Scratch username/password
   - GitHub username
5. Save changes

**Expected Result:**
- Learning accounts updated
- Changes reflected in student profile
- Operations manager can still edit full profile

**Status:** ☐ Pass ☐ Fail

---

## 4. Course Management

### Test 4.1: Create New Course (Teacher)
**Steps:**
1. Login as Teacher
2. Go to Courses → Create Course
3. Fill in course details:
   - Title: "Introduction to Web Development"
   - Description: Detailed description
   - Category: Web Development
   - Difficulty: Beginner
   - Duration: 40 hours
   - Price: 0 (Free)
   - Enrollment Type: Invite Only
4. Add "What You'll Learn" items
5. Add Requirements
6. Add Tags
7. Click "Create Course"

**Expected Result:**
- Course created with "Draft" status
- Course appears in teacher's course list
- Not visible to students yet

**Status:** ☐ Pass ☐ Fail

---

### Test 4.2: Build Course Curriculum
**Steps:**
1. Go to course details
2. Click "Curriculum Builder"
3. Add Module:
   - Title: "HTML Basics"
   - Description: "Learn HTML fundamentals"
4. Add Lessons to module:
   - Lesson 1: "Introduction to HTML"
   - Lesson 2: "HTML Tags and Elements"
5. Add content to lessons (text, video, code examples)
6. Add assessment/quiz
7. Save curriculum

**Expected Result:**
- Modules and lessons created
- Content saved properly
- Curriculum visible in course structure

**Status:** ☐ Pass ☐ Fail

---

### Test 4.3: Submit Course for Approval
**Steps:**
1. Go to course edit page
2. Check "Is Published"
3. Click "Submit for Approval"

**Expected Result:**
- Course status changes to "Pending"
- Admin/Supervisor notified
- Course appears in Content Approvals queue

**Status:** ☐ Pass ☐ Fail

---

### Test 4.4: Approve Course (Admin/Supervisor)
**Steps:**
1. Login as Admin or Supervisor
2. Go to Content Approval
3. Click on pending course
4. Review course details
5. Add approval notes
6. Click "Approve"

**Expected Result:**
- Course status changes to "Approved"
- Course now visible to students (with invitation requirement)
- Teacher notified of approval

**Status:** ☐ Pass ☐ Fail

---

## 5. Enrollment & Invitations

### Test 5.1: Send Course Invitation (Teacher)
**Steps:**
1. Login as Teacher
2. Go to course details
3. Click "Manage Enrollments"
4. Click "Send Invitation"
5. Select student(s)
6. Set expiration date (optional)
7. Add message
8. Click "Send Invitation"

**Expected Result:**
- Invitation created
- Student receives notification
- Invitation appears in student's Invitations page

**Status:** ☐ Pass ☐ Fail

---

### Test 5.2: Accept Invitation (Student)
**Steps:**
1. Login as Student
2. Go to Invitations
3. View invitation details
4. Click "Accept Invitation & Enroll"

**Expected Result:**
- Enrollment created
- Student can access course content
- Course appears in "My Enrollments"
- "Continue Learning" button visible

**Status:** ☐ Pass ☐ Fail

---

### Test 5.3: Attempt Enrollment Without Invitation
**Steps:**
1. Login as Student
2. Browse courses
3. Try to click "Enroll Now" on invite-only course

**Expected Result:**
- Message displayed: "Invitation Required"
- Cannot enroll without invitation
- Can only see first 2 lessons as preview

**Status:** ☐ Pass ☐ Fail

---

## 6. Attendance Tracking

### Test 6.1: Record Student Attendance
**Steps:**
1. Login as Teacher/Operations Manager
2. Go to Attendance → Student Attendance
3. Select date
4. Select course
5. Mark students as:
   - Present
   - Absent
   - Late
   - Excused
6. Add reason for absences
7. Click "Save Attendance"

**Expected Result:**
- Attendance recorded
- Visible in attendance records
- Statistics updated

**Status:** ☐ Pass ☐ Fail

---

### Test 6.2: Record Instructor Attendance
**Steps:**
1. Login as Operations Manager
2. Go to Attendance → Instructor Attendance
3. Select date
4. Select course
5. Mark instructor status
6. Add notes
7. Save

**Expected Result:**
- Instructor attendance recorded
- Visible in records
- Can be filtered and exported

**Status:** ☐ Pass ☐ Fail

---

### Test 6.3: View Attendance Records
**Steps:**
1. Go to Attendance → Attendance Records
2. Filter by:
   - Date range
   - Course
   - Status
3. Click "Export to CSV"

**Expected Result:**
- Records filtered correctly
- CSV file downloaded
- Contains all attendance data

**Status:** ☐ Pass ☐ Fail

---

## 7. Learning Experience

### Test 7.1: Access Course Content (Student)
**Steps:**
1. Login as enrolled Student
2. Go to My Enrollments
3. Click "Continue Learning"
4. Navigate through modules and lessons

**Expected Result:**
- Can view all lessons
- Video player works (if video lessons)
- Progress tracked
- Can navigate between lessons

**Status:** ☐ Pass ☐ Fail

---

### Test 7.2: Complete Lesson
**Steps:**
1. Open a lesson
2. Watch video/read content
3. Click "Mark as Complete"

**Expected Result:**
- Lesson marked complete
- Progress percentage updated
- Points awarded
- Next lesson unlocked

**Status:** ☐ Pass ☐ Fail

---

### Test 7.3: Take Assessment
**Steps:**
1. Navigate to assessment
2. Answer all questions
3. Submit assessment

**Expected Result:**
- Score calculated
- Results displayed
- Progress updated
- Certificate generated (if course complete)

**Status:** ☐ Pass ☐ Fail

---

### Test 7.4: Submit Assignment
**Steps:**
1. Open assignment
2. Upload file or enter text
3. Click "Submit"

**Expected Result:**
- Assignment submitted
- Teacher notified
- Submission visible in teacher's grading queue

**Status:** ☐ Pass ☐ Fail

---

## 8. Dashboards

### Test 8.1: Admin Dashboard
**Steps:**
1. Login as Admin
2. Review dashboard sections:
   - System statistics
   - Recent users
   - Course overview
   - Pending approvals
   - Leaderboard
   - System health

**Expected Result:**
- All statistics accurate
- Charts display correctly
- Quick actions work
- Real-time data

**Status:** ☐ Pass ☐ Fail

---

### Test 8.2: Operations Manager Dashboard
**Steps:**
1. Login as Operations Manager
2. Review:
   - Student statistics
   - Attendance overview
   - Recent registrations
   - Quick actions (Register Student, Record Attendance)

**Expected Result:**
- MIS-style interface
- Statistics cards accurate
- Quick actions functional
- Alerts displayed

**Status:** ☐ Pass ☐ Fail

---

### Test 8.3: Teacher Dashboard
**Steps:**
1. Login as Teacher
2. Review:
   - My courses
   - Student enrollments
   - Pending submissions
   - Course analytics

**Expected Result:**
- Course list accurate
- Can navigate to course management
- Submission queue works
- Analytics display correctly

**Status:** ☐ Pass ☐ Fail

---

### Test 8.4: Student Dashboard
**Steps:**
1. Login as registered Student
2. Review:
   - Enrolled courses
   - Progress tracking
   - Upcoming assignments
   - Achievements/badges
   - Leaderboard position

**Expected Result:**
- Courses displayed
- Progress accurate
- Gamification elements visible
- Can navigate to learning

**Status:** ☐ Pass ☐ Fail

---

### Test 8.5: Unregistered User Dashboard
**Steps:**
1. Login as new user (not registered as student)
2. View dashboard

**Expected Result:**
- "Student Registration Required" message
- Contact information displayed
- No access to courses
- Clear instructions on next steps

**Status:** ☐ Pass ☐ Fail

---

## 9. Content Approval Workflow

### Test 9.1: Submit Content for Approval
**Steps:**
1. Create/edit course, lesson, or assessment
2. Submit for approval

**Expected Result:**
- Status changes to "Pending"
- Appears in approval queue
- Supervisor/Admin notified

**Status:** ☐ Pass ☐ Fail

---

### Test 9.2: Review and Approve Content
**Steps:**
1. Login as Supervisor/Admin
2. Go to Content Approvals
3. Review pending item
4. Add notes
5. Approve or Reject

**Expected Result:**
- Status updated
- Creator notified
- Content published (if approved)
- Rejection reason visible (if rejected)

**Status:** ☐ Pass ☐ Fail

---

## 10. Gamification Features

### Test 10.1: Earn Points
**Steps:**
1. As student, complete various activities:
   - Enroll in course (50 points)
   - Complete lesson (5-15 points)
   - Complete course (100 points)
   - Submit assignment
   - Complete daily challenge

**Expected Result:**
- Points awarded correctly
- Total points updated
- Level progression tracked
- Visible in profile

**Status:** ☐ Pass ☐ Fail

---

### Test 10.2: Earn Badges
**Steps:**
1. Complete badge requirements:
   - First lesson
   - First course
   - 5 courses
   - Point milestones

**Expected Result:**
- Badge awarded
- Notification displayed
- Badge visible in profile
- Badge count updated

**Status:** ☐ Pass ☐ Fail

---

### Test 10.3: Leaderboard
**Steps:**
1. View leaderboard
2. Check rankings
3. Filter by timeframe

**Expected Result:**
- Rankings accurate
- Shows top performers
- Updates in real-time
- User can see their position

**Status:** ☐ Pass ☐ Fail

---

## Additional Testing Scenarios

### Test A1: Mobile Responsiveness
**Steps:**
1. Access platform on mobile device
2. Test all major features
3. Check navigation
4. Test forms

**Expected Result:**
- Responsive design works
- All features accessible
- Forms usable on mobile
- Navigation smooth

**Status:** ☐ Pass ☐ Fail

---

### Test A2: Dark Mode
**Steps:**
1. Toggle dark mode
2. Navigate through platform
3. Check all pages

**Expected Result:**
- Dark mode applies consistently
- Text readable
- Colors appropriate
- Images/logos adapt

**Status:** ☐ Pass ☐ Fail

---

### Test A3: Performance
**Steps:**
1. Load various pages
2. Check load times
3. Test with multiple users

**Expected Result:**
- Pages load quickly (<3 seconds)
- No lag or freezing
- Smooth transitions
- Handles concurrent users

**Status:** ☐ Pass ☐ Fail

---

### Test A4: Security
**Steps:**
1. Try accessing admin pages as student
2. Try accessing other users' data
3. Test SQL injection in forms
4. Test XSS attacks

**Expected Result:**
- Unauthorized access blocked
- Data protected
- Forms sanitized
- Security measures effective

**Status:** ☐ Pass ☐ Fail

---

## Bug Reporting Template

When you find a bug, please report it using this format:

```
**Bug ID:** [Unique identifier]
**Severity:** Critical / High / Medium / Low
**Test Case:** [Which test case]
**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Screenshots:**
[Attach screenshots if applicable]

**Browser/Device:**
[Chrome 120, Windows 11, etc.]

**Additional Notes:**
[Any other relevant information]
```

---

## Testing Checklist Summary

### Critical Features (Must Work)
- ☐ User registration and login
- ☐ Student registration by operations manager
- ☐ Course creation and approval
- ☐ Course invitation system
- ☐ Enrollment process
- ☐ Lesson access and completion
- ☐ Attendance tracking
- ☐ System settings

### Important Features (Should Work)
- ☐ Dashboards for all roles
- ☐ Progress tracking
- ☐ Assessment submission
- ☐ Assignment grading
- ☐ Gamification (points, badges)
- ☐ Content approval workflow
- ☐ Student profile management

### Nice to Have (Can Have Minor Issues)
- ☐ Leaderboard
- ☐ Daily challenges
- ☐ Discussions
- ☐ Notifications
- ☐ Analytics
- ☐ Certificate generation

---

## Testing Timeline

**Phase 1: Core Features**
- Authentication
- Student Management
- Course Management
- Enrollment

**Phase 2: Learning Features**
- Lesson Access
- Assessments
- Assignments
- Progress Tracking

**Phase 3: Administrative Features**
- Attendance
- Approvals
- System Settings
- Dashboards

**Phase 4: Polish & Performance**
- Mobile Testing
- Performance Testing
- Security Testing
- Bug Fixes

---

## Contact for Issues

**Technical Support:**
- Email:katogeoffreyg@gmail.com
- Phone: +256 742972689

**Report Critical Bugs Immediately**

---

## Notes for Testers

1. **Test thoroughly** - Don't rush through tests
2. **Document everything** - Take screenshots of issues
3. **Try edge cases** - Test unusual scenarios
4. **Test on different devices** - Mobile, tablet, desktop
5. **Test different browsers** - Chrome, Firefox, Safari, Edge
6. **Be creative** - Try to break things
7. **Provide feedback** - Suggest improvements

---

**Document Version:** 1.0
**Last Updated:** November 16, 2025
**Platform Version:** 1.0.0

---

Thank you for helping test Code Academy Uganda's E-Learning Platform! 🚀
