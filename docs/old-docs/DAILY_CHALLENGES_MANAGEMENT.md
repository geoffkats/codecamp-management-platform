# Daily Challenges Management System

## Overview
Added complete CRUD (Create, Read, Update, Delete) functionality for teachers and admins to manage daily challenges.

## Changes Made

### 1. Index Page Updates
**File:** `resources/views/livewire/daily-challenges/index.blade.php`
- Added "Create Challenge" button in header (visible only to users with `manage_badges` permission)
- Added "Edit" button on each challenge card for admins/teachers

### 2. Create Component
**File:** `app/Livewire/DailyChallenges/Create.php`
- Complete form handling for creating new daily challenges
- Fields include:
  - Title (required)
  - Description (required)
  - Type (general, coding, quiz, project, reading)
  - Category (optional)
  - Difficulty Level (Easy, Medium, Hard)
  - Reward Points (1-1000 XP)
  - Requirements (multi-line text, each line becomes a requirement)
  - Date (optional, for scheduled challenges)
  - Is Active (checkbox)
- Authorization check using `manage_badges` permission
- Success message and redirect to index on save

### 3. Edit Component
**File:** `app/Livewire/DailyChallenges/Edit.php`
- Complete form handling for updating existing challenges
- Same fields as Create component
- Added Delete functionality with confirmation
- Shows challenge statistics if attempts exist:
  - Total attempts
  - Completed attempts
  - Completion rate percentage
- Authorization check using `manage_badges` permission

### 4. Create View
**File:** `resources/views/livewire/daily-challenges/create.blade.php`
- Professional form layout using Flux UI components
- Two-column grid for related fields
- Help text and hints for clarity
- Cancel and Create buttons

### 5. Edit View
**File:** `resources/views/livewire/daily-challenges/edit.blade.php`
- Same layout as Create view
- Additional statistics panel showing engagement metrics
- Delete button with confirmation dialog
- Cancel, Delete, and Update buttons

## Permissions
Both create and edit routes require the `manage_badges` permission, which is typically granted to:
- Admins
- Teachers/Instructors
- Supervisors

## Features

### Create Challenges
- Navigate to `/daily-challenges` and click "Create Challenge"
- Fill in all required fields
- Optionally set a specific date or leave blank for evergreen challenges
- Set as active/inactive to control visibility

### Edit Challenges
- Click "Edit" button on any challenge card
- Update any field
- View engagement statistics
- Delete challenge if needed (with confirmation)

### Challenge Types
1. **General** - Standard challenges
2. **Coding** - Programming exercises
3. **Quiz** - Knowledge assessments
4. **Project** - Larger assignments
5. **Reading** - Reading comprehension

### Difficulty Levels
- **Easy** - Beginner-friendly challenges
- **Medium** - Intermediate difficulty
- **Hard** - Advanced challenges

## Usage Flow

### For Teachers/Admins:
1. Go to `/daily-challenges`
2. Click "Create Challenge" button
3. Fill in challenge details
4. Set reward points (XP)
5. Optionally schedule for specific date
6. Mark as active to make visible to students
7. Edit anytime by clicking "Edit" on challenge cards
8. View engagement statistics on edit page

### For Students:
- See all active challenges on `/daily-challenges`
- Filter by status, difficulty, or search
- Start challenges and earn XP upon completion
- Track completion progress

## Technical Notes
- Requirements field converts newline-separated text into JSON array
- Date field supports both scheduled and evergreen challenges (null date)
- Reward points capped at 1-1000 XP range
- Statistics only show when challenge has attempts
- Delete requires confirmation dialog
- All forms use Livewire for reactive updates
