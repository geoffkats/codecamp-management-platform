# Quick Start: Visual Scratch Components

## 🎯 What You Get

Visual, engaging Scratch-style components for your lessons - just like Code.org!

## 🚀 Quick Test

**See all components in action:**
```
Visit: /test-visual-components
```

## ⚡ Add to Existing Lesson (Fastest Way)

```bash
php add-visual-to-lesson.php 4
```
Replace `4` with your lesson ID, then view at `/lessons/4/view`

## 📝 Manual Setup

### Step 1: Edit a Lesson
Go to `/curriculum/builder/4` and edit any lesson

### Step 2: Set Lesson Type
Change `lesson_type` to `interactive`

### Step 3: Add Visual Data
Use the script above OR manually add to database:

**scratch_project_id:**
```
1234567890
```

**lesson_steps:**
```json
[
    {"title": "Step 1", "description": "Do this", "image": null, "code": null},
    {"title": "Step 2", "description": "Then this", "image": null, "code": null}
]
```

**scratch_blocks:**
```json
[
    {"category": "motion", "text": "move (10) steps"},
    {"category": "events", "text": "when 🏴 clicked"}
]
```

### Step 4: View the Lesson
Go to `/lessons/{id}/view` - visual components appear automatically!

## 🎨 Block Categories & Colors

- `motion` - Blue (movement blocks)
- `looks` - Purple (appearance blocks)
- `sound` - Pink (audio blocks)
- `events` - Yellow (trigger blocks)
- `control` - Orange (loop/condition blocks)
- `sensing` - Cyan (detection blocks)
- `operators` - Green (math blocks)
- `variables` - Orange-red (data blocks)

## 📦 What Was Added

✅ 4 Blade components (lesson-card, scratch-embed, lesson-step, scratch-block)
✅ 3 database fields (scratch_project_id, lesson_steps, scratch_blocks)
✅ Auto-display in lesson viewer
✅ Test page at `/test-visual-components`
✅ Quick setup script `add-visual-to-lesson.php`

## 🔍 Where to See It

1. **Test Page:** `/test-visual-components` - See all components
2. **Lesson View:** `/lessons/{id}/view` - See in actual lessons
3. **Builder:** `/curriculum/builder/4` - Create/edit lessons

## 💡 Example Lesson

Run the seeder to create a complete example:
```bash
php artisan db:seed --class=ScratchLessonSeeder
```

## 📚 Full Documentation

- `VISUAL_COMPONENTS_GUIDE.md` - Complete usage guide
- `VISUAL_COMPONENTS_SUMMARY.md` - Implementation details
- `resources/views/lessons/scratch-lesson-example.blade.php` - Code example

## ✨ That's It!

The visual components are now integrated and ready to use. They automatically appear when viewing interactive lessons with the visual component data configured.
