# Daily Attendance Code System

## Overview
A secure, automated attendance tracking system where students check in/out using a unique daily code that changes automatically at midnight.

## Features Implemented

### 1. Daily Code Generation
- **Automatic**: Code regenerates at midnight via Laravel scheduler
- **Manual**: Teachers can generate new codes anytime
- **Format**: 6-character alphanumeric code (e.g., `FTAILA`)
- **QR Code**: Auto-generated for easy scanning

### 2. Teacher Dashboard (`/attendance/code`)
- Display today's attendance code
- Large, readable format for projection
- QR code for mobile scanning
- Print-friendly layout
- Manual code regeneration button
- Instructions for students

### 3. Student Check-In/Out (`/attendance/check-in`)
- Simple code entry interface
- Check-in when arriving
- Check-out when leaving
- Real-time status display
- Automatic hours calculation
- Validation to prevent duplicate entries

### 4. Database Structure

**daily_attendance_codes**
- `id` - Primary key
- `code` - 6-character unique code
- `date` - Date for this code
- `is_active` - Active status
- `timestamps`

**attendance_logs**
- `id` - Primary key
- `student_profile_id` - Foreign key to student
- `attendance_date` - Date of attendance
- `check_in_time` - Time student arrived
- `check_out_time` - Time student left
- `code_used` - Code they entered
- `timestamps`

## How It Works

### For Teachers:
1. Visit `/attendance/code`
2. Display the code on projector/board
3. Students use this code to check in/out
4. Code automatically changes at midnight

### For Students:
1. Visit `/attendance/check-in`
2. Enter today's code (shown by teacher)
3. Click "Check In" when arriving
4. Wait at least 1 hour (minimum session time)
5. Enter code again and click "Check Out" when leaving
6. System shows total hours on premises

**Note**: Students must wait at least 1 hour after check-in before they can check out. This prevents accidental immediate check-outs and ensures minimum session attendance.

## Setup Commands

```bash
# Generate today's code manually
php artisan attendance:generate-code

# Run migrations
php artisan migrate

# Set up scheduler (add to crontab)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Scheduler Configuration
The system automatically generates a new code at midnight via Laravel's scheduler (configured in `routes/console.php`).

## Routes
- `/attendance/code` - Teacher code display (Daily Code in sidebar)
- `/attendance/check-in` - Student check-in/out (Check In/Out in sidebar)
- `/attendance/students` - Manual attendance (Manual Attendance in sidebar)

## Sidebar Navigation

### For Admins:
Under "Content Management":
- **Manual Attendance** - Traditional teacher-marked attendance
- **Daily Code** - Display today's attendance code
- **Check In/Out** - Student self check-in interface

### For Operations Managers:
Under "Operations":
- **Manual Attendance** - Traditional teacher-marked attendance
- **Daily Code** - Display today's attendance code
- **Check In/Out** - Student self check-in interface
- **Instructor Attendance** - Track instructor attendance

### For Students:
Under "Learning":
- **Check In/Out** - Self check-in/out using daily code

## Security Features
- Code changes daily (prevents reuse)
- Validates code before logging
- Prevents duplicate check-ins
- Requires check-in before check-out
- **Minimum 1-hour wait** between check-in and check-out
- Tracks which code was used
- Real-time countdown for remaining wait time

## Benefits
✅ No hardware needed (RFID, biometric)
✅ Works on any device with browser
✅ Automatic time tracking
✅ Prevents attendance fraud
✅ Easy for students to use
✅ Scalable for any class size
✅ QR code support for mobile

## Next Steps (Optional Enhancements)
- SMS/Email code delivery
- Mobile app integration
- Geofencing (location verification)
- Analytics dashboard
- Export reports (CSV/PDF)
- Parent notifications
- Late arrival alerts
