# Visual Scratch-Style Components Guide

## Overview
The curriculum builder now supports visual, engaging Scratch-style components for interactive lessons. These components make lessons more engaging and easier to follow for students.

## Available Components

### 1. Lesson Card (`<x-lesson-card>`)
A colorful card to introduce lessons with visual appeal.

**Usage:**
```blade
<x-lesson-card 
    title="Make Your Sprite Move"
    description="Learn how to control sprite movement"
    difficulty="beginner"
    duration="30"
    icon="🎮"
/>
```

### 2. Scratch Embed (`<x-scratch-embed>`)
Embeds a Scratch project directly in the lesson.

**Usage:**
```blade
<x-scratch-embed 
    projectId="1234567890"
    title="Example Project"
    height="400"
/>
```

### 3. Lesson Step (`<x-lesson-step>`)
Step-by-step instructions with visual styling.

**Usage:**
```blade
<x-lesson-step 
    number="1"
    title="Add a Sprite"
    description="Click on the Choose a Sprite button"
    image="/images/step1.png"
    code="when flag clicked"
/>
```

### 4. Scratch Block (`<x-scratch-block>`)
Display Scratch blocks with proper color coding.

**Usage:**
```blade
<x-scratch-block 
    category="motion"
    text="move (10) steps"
/>
```

**Available Categories:**
- `motion` (blue)
- `looks` (purple)
- `sound` (pink)
- `events` (yellow)
- `control` (orange)
- `sensing` (cyan)
- `operators` (green)
- `variables` (orange-red)

## Adding Visual Components to Lessons

### Method 1: Using the Database Seeder

Run the seeder to create an example lesson:
```bash
php artisan db:seed --class=ScratchLessonSeeder
```

### Method 2: Manually in the Database

When creating/editing a lesson in the curriculum builder at `/curriculum/builder/4`, you can add these JSON fields:

**scratch_project_id:**
```
1234567890
```

**lesson_steps (JSON):**
```json
[
    {
        "title": "Add a Sprite",
        "description": "Click on the Choose a Sprite button",
        "image": null,
        "code": null
    },
    {
        "title": "Drag the Move Block",
        "description": "Find the blue move block",
        "image": null,
        "code": null
    }
]
```

**scratch_blocks (JSON):**
```json
[
    {
        "category": "motion",
        "text": "move (10) steps"
    },
    {
        "category": "events",
        "text": "when 🏴 clicked"
    }
]
```

### Method 3: Programmatically

```php
$lesson = Lesson::find(4);
$lesson->update([
    'lesson_type' => 'interactive',
    'scratch_project_id' => '1234567890',
    'lesson_steps' => [
        [
            'title' => 'Step 1',
            'description' => 'Do this first',
            'image' => null,
            'code' => null,
        ],
    ],
    'scratch_blocks' => [
        [
            'category' => 'motion',
            'text' => 'move (10) steps',
        ],
    ],
]);
```

## How It Works

When students view a lesson at `/lessons/{id}/view`:

1. **Regular lessons** show normal text/video content
2. **Interactive lessons** (lesson_type = 'interactive') automatically display:
   - Scratch project embed (if scratch_project_id is set)
   - Step-by-step instructions (if lesson_steps is set)
   - Block demonstrations (if scratch_blocks is set)

## Example: Complete Scratch Lesson

See `resources/views/lessons/scratch-lesson-example.blade.php` for a complete example of how all components work together.

## Testing

1. Go to `/curriculum/builder/4` (or your course ID)
2. Create or edit a lesson
3. Set `lesson_type` to "interactive"
4. Add the JSON data for visual components
5. Save and view the lesson at `/lessons/{id}/view`

The visual components will automatically appear below the main lesson content!

## Database Schema

The migration added these fields to the `lessons` table:
- `scratch_project_id` (string, nullable) - Scratch project ID for embedding
- `lesson_steps` (json, nullable) - Array of step objects
- `scratch_blocks` (json, nullable) - Array of block objects

## Next Steps

To fully integrate into the curriculum builder UI:
1. Add form fields in the builder for these new fields
2. Add a visual editor for creating steps
3. Add a block picker for selecting Scratch blocks
4. Add image upload for step screenshots
