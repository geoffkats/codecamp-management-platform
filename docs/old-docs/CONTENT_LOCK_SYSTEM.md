# Content Lock System

## Overview
The content lock system allows instructors to control when students can access lessons, quizzes, and assignments. This ensures students progress through the course at the intended pace.

## Features

### 1. Lesson Locking
- **Lock/Unlock Control**: Hover over any lesson in the curriculum builder sidebar to see a lock icon
- **Red Lock Icon** = Lesson is locked (students cannot access)
- **Green Unlock Icon** = Lesson is unlocked (students can access)
- **Click to Toggle**: Simply click the icon to lock or unlock the lesson

### 2. Quiz/Assessment Locking
- **Lock/Unlock Control**: Hover over any quiz/assessment in the curriculum builder sidebar
- **Same Icon System**: Red = locked, Green = unlocked
- **Quick Toggle**: Click to instantly lock or unlock

### 3. Assignment Locking
- Assignments are locked by default when created
- Can be unlocked through the assignment management interface
- Locked assignments show "Wait for instructor" message to students

### 4. Student Experience

#### When Lesson is Locked:
- Students see a professional "Lesson Locked" page
- Message: "Please wait for your instructor to unlock it"
- Can still access:
  - Unlocked quizzes/assessments
  - Unlocked assignments
- Cannot view lesson content until unlocked

#### When Quiz is Locked:
- Quiz appears in the list but is not clickable
- Shows lock icon and "Locked" status
- Students must wait for instructor to unlock

#### When Assignment is Locked:
- Assignment shows "Locked - Wait for instructor" message
- Not accessible until instructor unlocks it

### 5. Lock Management Dashboard
In the curriculum builder course overview, you'll see:
- **Content Lock Management** section
- Statistics showing:
  - Number of locked vs unlocked lessons
  - Number of locked vs unlocked quizzes
- Visual indicators with red (locked) and green (unlocked) icons

## How to Use

### Locking/Unlocking Content

1. **Go to Curriculum Builder**
   - Navigate to: Curriculum → Builder
   - Select your course

2. **Lock/Unlock Lessons**
   - Hover over any lesson in the left sidebar
   - Click the lock icon that appears
   - Red = Locked, Green = Unlocked

3. **Lock/Unlock Quizzes**
   - Hover over any quiz under a lesson
   - Click the lock icon
   - Toggle between locked and unlocked

4. **View Lock Status**
   - Check the "Content Lock Management" section in the course overview
   - See counts of locked/unlocked content at a glance

### Best Practices

1. **Start Locked**: Keep all content locked initially
2. **Progressive Unlock**: Unlock lessons as students progress
3. **Quiz Timing**: Unlock quizzes only when students should take them
4. **Assignment Pacing**: Unlock assignments one at a time to prevent students from rushing ahead

### Default Behavior

- **Lessons**: Unlocked by default (can be changed in lesson settings)
- **Quizzes**: Unlocked by default
- **Assignments**: **Locked by default** (must be manually unlocked)

## Technical Details

### Database Fields
- `lessons.is_locked` (boolean)
- `assessments.is_locked` (boolean)
- `assignments.is_locked` (boolean)

### Access Control
- Instructors, admins, and supervisors can always view locked content
- Students are blocked from locked content
- Lock status is checked on every page load

### API Methods
- `toggleLessonLock($lessonId)` - Toggle lesson lock status
- `toggleAssessmentLock($assessmentId)` - Toggle quiz lock status
- `toggleAssignmentLock($assignmentId)` - Toggle assignment lock status
