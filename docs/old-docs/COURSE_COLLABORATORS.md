# Course Collaborators Feature

## Overview
The Course Collaborators feature allows course instructors and admins to add other teachers as collaborators to their courses, enabling team-based course management.

## Features

### 1. Collaborator Roles
- **Editor**: Can view and modify course content (lessons, modules, assessments)
- **Viewer**: Can only view course content (read-only access)

### 2. Access Control
- Course instructors can add/remove collaborators
- Admins and supervisors can see all courses
- Teachers see courses where they are:
  - The instructor (owner)
  - Added as a collaborator

### 3. UI Components

#### Manage Collaborators Component
Location: `app/Livewire/Course/ManageCollaborators.php`

Features:
- Add new collaborators with role selection
- Search for teachers by name or email
- Change collaborator roles (editor/viewer)
- Remove collaborators
- Real-time updates

#### Curriculum Builder Integration
- Collaborator badge shows on courses where user is not the owner
- All courses (owned + collaborated) appear in course selection

## Usage

### For Admins/Instructors

**Adding a Collaborator:**
1. Go to course edit page
2. Find the "Course Collaborators" section
3. Click "Add Collaborator"
4. Search and select a teacher
5. Choose role (Editor or Viewer)
6. Click "Add Collaborator"

**Managing Collaborators:**
- Change role using the dropdown next to each collaborator
- Remove collaborators using the trash icon
- View who invited each collaborator and when

### For Collaborators

**Accessing Shared Courses:**
1. Go to Curriculum Builder
2. See all courses where you're instructor or collaborator
3. Courses with "Collaborator" badge are shared with you
4. Edit permissions depend on your role (Editor/Viewer)

## Database Schema

### course_collaborators Table
```sql
- id: Primary key
- course_id: Foreign key to courses
- user_id: Foreign key to users (the collaborator)
- role: enum('editor', 'viewer')
- invited_at: Timestamp when added
- invited_by: Foreign key to users (who added them)
- created_at, updated_at: Timestamps
- Unique constraint on (course_id, user_id)
```

## API Methods

### Course Model
```php
// Relationships
$course->collaborators() // HasMany CourseCollaborator
$course->collaboratorUsers() // BelongsToMany User

// Helper Methods
$course->isCollaborator($user) // Check if user is collaborator
$course->canUserEdit($user) // Check if user can edit (owner or editor)
```

### CourseCollaborator Model
```php
$collaborator->canEdit() // Returns true if role is 'editor'
```

## Integration Points

### Routes
The existing `curriculum.builder` route automatically includes collaborated courses.

### Permissions
- Uses existing `edit_courses` permission
- Collaborators inherit course access based on their role
- Supervisors have `edit_courses` permission for oversight

## Future Enhancements

Potential improvements:
- Email notifications when added as collaborator
- Activity log for collaborator actions
- Bulk add collaborators
- Collaborator groups/teams
- Time-limited access (expiration dates)
- More granular permissions (e.g., can edit lessons but not assessments)

## Testing

To test the feature:
1. Create a course as Teacher A
2. Add Teacher B as a collaborator
3. Log in as Teacher B
4. Verify Teacher B sees the course in Curriculum Builder
5. Test editing based on role (editor vs viewer)
6. Remove collaborator and verify access is revoked
