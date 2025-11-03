# Assignment Grading System Guide

## ✅ Grading System Status
The grading system is **fully implemented** and ready to use.

## How to Grade Assignments

### Method 1: From Assignment Page (Recommended)

1. **Navigate to Assignment**
   - Go to: `/assignments/{assignment_id}` or `/assignments/{assignment_id}/show`
   - Example: `http://127.0.0.1:8000/assignments/3`

2. **View Student Submissions**
   - Scroll to "Student Submissions" section (visible for Teachers/Admins)
   - See list of all student submissions with status badges:
     - 🟡 **Pending** - Needs grading
     - 🟢 **Graded** - Already graded

3. **Click "Grade" Button**
   - Click the **"Grade"** button next to any pending submission
   - This opens the grading interface at: `/grades/{submission_id}/grade`

### Method 2: From Submissions Index

1. **Go to Submissions Page**
   - Navigate to: `/submissions` or use the "View All" link from assignment page

2. **Find Assignment Submissions**
   - Filter by assignment or browse all submissions
   - Look for submissions with "Pending" status

3. **Click "Grade" Button**
   - Click the "Grade" button next to the submission

### Method 3: Direct Route

- Direct grading URL: `/grades/{submission_id}/grade`
- Example: `http://127.0.0.1:8000/grades/5/grade`

## Grading Interface Features

### What You Can Grade:

1. **Points/Score**
   - Enter total score (0 to max_points)
   - Auto-calculates percentage
   - Auto-generates letter grade (A+, A, A-, B+, B, etc.)

2. **Feedback**
   - Add written feedback for the student
   - Supports full text formatting

3. **Rubric-Based Grading** (if assignment has rubric)
   - Score individual rubric criteria
   - Add feedback per criterion
   - Auto-calculates total from rubric scores

### Grading Features:

✅ **Automatic Calculations**
- Percentage calculation
- Letter grade generation (A+ to F)
- Pass/Fail determination (70% = pass)

✅ **XP Awarding**
- Automatically awards XP if student passes (≥70%)
- XP amount from `assignment->xp_reward`

✅ **Notifications**
- Sends notification to student when graded
- Includes score, percentage, and feedback

✅ **Grade Tracking**
- Creates Grade record in database
- Updates submission status to "graded"
- Tracks who graded and when

## Access Control

### Teachers:
- ✅ Can grade submissions from their own courses only
- ✅ Full access to grading interface
- ✅ Can view all submissions for their assignments

### Admins:
- ✅ Can grade any submission
- ✅ Full access to all assignments
- ✅ Can override any grade

### Students:
- ❌ Cannot grade submissions
- ✅ Can view their own grades after grading

## Grade Storage

Grades are stored in:
- **`grades` table**: Full grade record with feedback
- **`assignment_submissions` table**: Quick reference (points_earned, graded_at, graded_by)

## Grading Workflow

1. **Student submits** → Status: `submitted`
2. **Teacher/Admin grades** → Status: `graded`
3. **Grade saved** → Creates Grade record + Updates submission
4. **Student notified** → Receives notification with grade
5. **XP awarded** → If passed (≥70%), awards XP points

## Quick Access Routes

| Route | Purpose | Access |
|-------|---------|--------|
| `/assignments/{id}` | View assignment & submissions | Teacher/Admin |
| `/grades/{submission}/grade` | Grade a submission | Teacher/Admin |
| `/submissions` | View all submissions | Teacher/Admin |
| `/submissions/{id}` | View submission details | Teacher/Admin/Student (own) |

## File Attachments in Grading

When grading:
- ✅ View all submitted files
- ✅ Download attachments for review
- ✅ Files stored in `storage/app/public/assignments/`
- ✅ Supports multiple file types (PDF, DOC, Images, etc.)

## Grading Statistics

On assignment page, teachers see:
- Total submissions count
- Graded count
- Pending count
- Average score

## Troubleshooting

### "You can only grade submissions from your own courses"
- **Solution**: Only teachers who created the course can grade. Admins can grade any submission.

### Grade not saving
- Check: Are you logged in as Teacher/Admin?
- Check: Does the submission exist?
- Check: Is the score within valid range (0 to max_points)?

### Student not receiving notification
- Check: NotificationService exists and is configured
- Check: Student has valid email/user account

## Related Components

- **Component**: `app/Livewire/Grades/Grade.php`
- **View**: `resources/views/livewire/grades/grade.blade.php`
- **Model**: `app/Models/Grade.php`
- **Model**: `app/Models/AssignmentSubmission.php`

## Testing Grading

1. Create a test assignment
2. Submit as a student
3. Login as teacher/admin
4. Navigate to assignment page
5. Click "Grade" on the submission
6. Enter score and feedback
7. Click "Save Grade"
8. Verify student receives notification
9. Check grade appears in student view


