# ICDL Platform System Plan (Student–Teacher–Admin)

_Last updated: 2026-02-09_

## 1) Student Dashboard
### Core Features
- **Payment Section**
  - Teacher enters payment amounts.
  - Once submitted and verified, no edits allowed.
  - Dashboard shows:
    - Total amount required
    - Total amount submitted
    - Balance remaining
- **Student Profile**
  - Email (optional)
  - Password (optional) — used for ICDL login
  - ICDL Number (optional)
  - Printable student details (PDF/print view)
- **Modules Overview**
  - Shows modules the student is currently enrolled in
  - Shows progress in each module
  - Shows readiness status (teacher-marked)

## 2) Teacher Dashboard
### Student Management
- Add a student
- Edit basic details (except payments once verified)
- Enroll student into modules
- Add additional modules at any time
- View what modules each student is currently doing

### Performance & Exam Readiness
- Teacher enters:
  - ICDL exam marks (after student sits the exam)
  - Internal test performance
- Teacher can mark:
  - **Student is exam-ready**
  - **Student needs more practice**
- Student can also signal readiness by:
  - Sending an email through the system, OR
  - Ticking a **Ready for Exam** checkbox

### Exam Requests
- Teacher can request an ICDL exam session if they have the minimum quorum
- Request includes:
  - List of students
  - Modules they want to sit
  - Preferred date/time
- Admin receives the request and approves or declines

### Course Content
- Teacher sees all content assigned to them
- Teacher can assign tests or practice modules to students
- Teacher can track who has passed internal tests

## 3) Admin Dashboard
### Verification & Oversight
- Approve or reject:
  - Payments
  - Exam requests
  - Module enrollments (optional)
- View:
  - All students
  - All teachers
  - All modules
  - All exam readiness statuses

### Exam Management
- Review teacher exam requests
- Confirm exam dates
- Send confirmation back to teacher through the system

## 4) Workflow Logic (Clean & Simple)
A. **Student joins**
- Teacher → Adds student → Enters optional email/password → Enters payment → Admin verifies → Student appears active

B. **Student begins modules**
- Teacher → Enrolls student into modules → Student starts internal tests

C. **Student completes internal tests**
- System → Marks tests passed → Teacher reviews → Teacher marks **Exam Ready**

D. **Student sits ICDL exam**
- Teacher → Enters ICDL marks → Admin reviews → Status updated

E. **Teacher requests exam session**
- Teacher → Requests exam → Admin approves → Teacher notified → Submits payment for approved students → Admin sets exam date

## 5) Extra Improvements (Recommended)
### Smart Automation
- Auto-calc balance after payments
- Auto-flag students who passed all internal tests
- Auto-notify teacher when student marks themselves **Ready**

### Security
- Role-based access:
  - Student
  - Teacher
  - Admin
- Audit logs for:
  - Payments
  - Exam readiness changes
  - Module enrollments

### Reporting
- Teacher reports:
  - Students ready for exam
  - Students who passed internal tests
- Admin reports:
  - Payments summary
  - Exam sessions summary
  - Module performance trends

## 6) Safe Implementation Checklist
- **Permissions**: confirm role guards are enforced on all new routes and actions.
- **Data integrity**: lock payment edits once verified; log changes in audit tables.
- **Validation**: server-side validation for readiness changes, exam requests, and marks.
- **Notifications**: centralize email/notification dispatch for auditability.
- **Testing**: add tests for payment verification, readiness toggles, and exam approval flow.
- **Migration safety**: add new fields with defaults and backfill scripts where needed.
