# Attendance Clock In/Out System

## Changes Made

The attendance system has been updated to track clock in/clock out times instead of "Late" and "Excused" statuses.

### Database Changes
- Added `clock_in` (time) field to `student_attendances` table
- Added `clock_out` (time) field to `student_attendances` table
- Migration: `2025_11_22_171144_add_clock_times_to_student_attendances_table.php`

### Features
1. **Status Options**: Now only "Present" and "Absent"
2. **Clock In/Out**: When a student is marked as "Present", you can enter:
   - Clock In time
   - Clock Out time
   - System automatically calculates total hours on premises
3. **Reason Field**: Still available for "Absent" status

### How to Use
1. Navigate to `/attendance/students`
2. Select a student and mark them as "Present"
3. Enter their clock in time (e.g., 08:00)
4. Enter their clock out time (e.g., 16:30)
5. The system will display total hours (e.g., 8.50 hours)
6. Click "Submit Attendance" to save

### Benefits
- Track exact hours each student was on premises
- Better for compliance and reporting
- Automatic calculation of total time
- Cleaner interface with only relevant options
