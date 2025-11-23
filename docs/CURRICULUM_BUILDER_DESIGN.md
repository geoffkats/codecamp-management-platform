# Curriculum Builder - Design Reference (DEPRECATED)

> **⚠️ This document is deprecated. See [CURRICULUM_BUILDER_COMPLETE.md](./CURRICULUM_BUILDER_COMPLETE.md) for the complete implementation guide.**

## Overview
The new curriculum builder features a clean, teacher-friendly interface inspired by code.org and Google Classroom. It's designed for clarity, ease of use, and professional educational environments.

## Design Philosophy

### 🎨 Visual Style
- **Soft, calm colors**: Light grays, whites, blues
- **Clear section organization**: Each section has an icon and title
- **Large, readable text**: Easy to scan and understand
- **Professional appearance**: Clean, flat components
- **Excellent dark mode**: High contrast, pleasant for night editing

### 👩‍🏫 Teacher-Centered UX
- **No training required**: Intuitive interface
- **Clear workflow**: Objectives → Content → Video → Settings
- **No hidden surprises**: Everything visible and accessible
- **Reduces mistakes**: Clear labels and helper text
- **Fast lesson creation**: Streamlined form layout

## Layout Structure

### Two-Column Responsive Layout
- **Left Column (2/3 width)**: Main content sections
- **Right Column (1/3 width)**: Settings and metadata
- **Mobile**: Stacks vertically for smaller screens

## Form Sections

### 1. Basic Information
**Icon**: Blue info circle  
**Fields**:
- Title (required, large input)
- Module selector (dropdown)
- Lesson type selector (text/video/interactive/quiz with emojis)
- Summary (short description, 2 rows)

**Helper text**: Clear guidance on what each field is for

### 2. Learning Objectives
**Icon**: Green checkmark circle  
**Purpose**: What students will learn  
**Field**: Large textarea with bullet-point placeholder  
**Style**: Light background, monospace font for better readability

**Example placeholder**:
```
• Understand the concept of variables
• Create and assign values to variables
• Use variables in simple programs
• Explain the difference between variable types
```

### 3. Lesson Content
**Icon**: Purple document  
**Field**: Large textarea (12 rows)  
**Features**: 
- Supports HTML formatting
- Ample space for detailed content
- Clear, clean input area

### 4. Video Lesson Settings (Conditional)
**Icon**: Blue video camera  
**Visibility**: Only shown when lesson_type = 'video'  
**Fields**:
- Video URL (required for video lessons, with validation)
- Video duration (optional, in minutes)

**Style**: Blue border to indicate special section

### 5. Publishing & Access (Right Column)
**Toggle switches** (Google Material style):
- **Published**: Make visible to students
- **Active**: Enable lesson access
- **Free Preview**: Allow non-enrolled users
- **Locked**: Require prerequisites

**Toggle design**:
- Blue when active
- Gray when off
- Smooth animations
- Clear labels with descriptions

### 6. Lesson Settings (Right Column)
**Fields**:
- Difficulty Level (dropdown with emoji indicators)
  - 🟢 Beginner
  - 🟡 Intermediate
  - 🔴 Advanced
- Duration (minutes)
- Order Position (number input)

### 7. Save Actions (Right Column)
- **Primary button**: Blue, full width, prominent
- **Cancel button**: Gray, secondary style
- Clear visual hierarchy

## Module Form

Simpler, focused form for creating/editing modules:
- Title (required)
- Description (textarea)
- Order Position

Clean, single-column layout with same visual style.

## All Implemented Fields

### Lesson Fields (Complete)
✅ title  
✅ module_id  
✅ lesson_type  
✅ content  
✅ summary  
✅ objectives  
✅ video_url (conditional)  
✅ video_duration  
✅ difficulty_level  
✅ duration_minutes  
✅ order_index  
✅ is_published  
✅ is_active  
✅ is_free_preview  
✅ is_locked  

### Module Fields
✅ title  
✅ description  
✅ order_index  

## Color Scheme

### Light Mode
- Background: `bg-gray-50`
- Cards: `bg-white` with `border-gray-200`
- Text: `text-gray-900`
- Accents: `blue-600`, `green-600`, `purple-600`

### Dark Mode
- Background: `bg-gray-900`
- Cards: `bg-gray-800` with `border-gray-700`
- Text: `text-white`
- Accents: `blue-400`, `green-400`, `purple-400`

## Section Icons & Colors

| Section | Icon | Color |
|---------|------|-------|
| Basic Information | Info circle | Blue |
| Learning Objectives | Checkmark circle | Green |
| Lesson Content | Document | Purple |
| Video Settings | Video camera | Blue |

## Validation

### Required Fields
- Title
- Module
- Lesson Type
- Order Index

### Conditional Required
- Video URL (required when lesson_type = 'video')

### Optional Fields
- Summary
- Objectives
- Content
- Video Duration
- Duration Minutes
- Difficulty Level

## Success Messages
Green banner at top of content area with:
- Checkmark icon
- Success message text
- Auto-dismisses on next action

## User Flow

1. **Select Course** → Course list in sidebar
2. **View Course Structure** → Modules and lessons tree
3. **Add Module** → Simple form
4. **Add Lesson** → Comprehensive form with all fields
5. **Edit Items** → Click any module/lesson to edit
6. **Save** → Success message + return to structure view

## Accessibility Features
- Clear labels for all inputs
- Helper text for guidance
- High contrast in dark mode
- Keyboard navigation support
- Screen reader friendly
- Focus indicators on all interactive elements

## Mobile Responsiveness
- Two-column layout on desktop
- Single column stack on mobile
- Touch-friendly buttons and inputs
- Adequate spacing for mobile interaction

## Why This Design Works for Schools

1. **Familiar**: Teachers recognize the pattern from code.org/Classroom
2. **Professional**: Clean, calm appearance suitable for education
3. **Efficient**: Fast lesson creation with clear workflow
4. **Accessible**: Works for all teachers, regardless of tech skill
5. **Scalable**: Easy to add more fields or sections later
6. **Maintainable**: Clear structure, easy to update

## Technical Implementation

### Framework
- Laravel Livewire for reactive components
- Tailwind CSS for styling
- Wire:model for two-way data binding

### Files
- View: `resources/views/livewire/curriculum/new-builder.blade.php`
- Component: `app/Livewire/Curriculum/NewBuilder.php`
- Model: `app/Models/Lesson.php`

### Key Features
- Real-time validation
- Conditional field display (video settings)
- Auto-save form state
- Success/error messaging
- Dark mode support
