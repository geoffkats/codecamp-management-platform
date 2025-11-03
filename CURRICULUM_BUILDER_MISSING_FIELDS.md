# Curriculum Builder - Missing Lesson Fields Analysis

## Current Form Fields
The curriculum builder lesson modal currently has:
1. ✅ Title
2. ✅ Module (module_id)
3. ✅ Lesson Type (lesson_type)
4. ✅ Content
5. ✅ Duration (minutes)
6. ✅ Order Index

## Missing Critical Fields

### 🔴 HIGH PRIORITY - Used in Lesson View

#### 1. **Video URL** (`video_url`)
- **Status:** MISSING
- **Used in:** `resources/views/livewire/lessons/view.blade.php` (lines 242, 254)
- **Impact:** Video lessons cannot function without this field
- **Required when:** `lesson_type === 'video'`
- **Example usage:**
  ```blade
  @if(($lesson->video_url || $lesson->lesson_type === 'video') && $lesson->video_url)
      <video><source src="{{ $lesson->video_url }}" /></video>
  @endif
  ```

#### 2. **Learning Objectives** (`objectives`)
- **Status:** MISSING
- **Used in:** `resources/views/livewire/lessons/view.blade.php` (lines 178, 187)
- **Impact:** Learning objectives section in lesson view will be empty
- **Display:** Shown prominently at top of lesson with gradient background
- **Example usage:**
  ```blade
  @if($lesson->objectives)
      <div class="bg-gradient-to-r from-blue-50...">
          <h2>Learning Objectives</h2>
          <p>{{ $lesson->objectives }}</p>
      </div>
  @endif
  ```

#### 3. **Attachments** (`attachments`)
- **Status:** MISSING
- **Used in:** `resources/views/livewire/lessons/view.blade.php` (lines 359-373)
- **Impact:** No way to attach files/resources to lessons
- **Type:** JSON array storing file paths and names
- **Display:** Grouped by file type (PDFs, documents, images, archives)

#### 4. **Summary** (`summary`)
- **Status:** MISSING
- **Usage:** Short description/preview text
- **Impact:** Less informative lesson previews

### 🟡 MEDIUM PRIORITY - Important for Course Management

#### 5. **Difficulty Level** (`difficulty_level`)
- **Status:** MISSING
- **Used in:** `resources/views/livewire/lessons/view.blade.php` (line 490)
- **Options:** `beginner`, `intermediate`, `advanced`
- **Impact:** Displayed in lesson sidebar, used for points calculation
- **Default:** Should default to `beginner`

#### 6. **Publishing Status** (`is_published`)
- **Status:** MISSING
- **Used in:** Lesson index views
- **Type:** Boolean
- **Impact:** Cannot control lesson visibility
- **Default:** Should default to `false`

#### 7. **Free Preview** (`is_free_preview`)
- **Status:** MISSING
- **Type:** Boolean
- **Impact:** Cannot mark lessons as free previews for marketing
- **Default:** Should default to `false`

#### 8. **Lock Status** (`is_locked`)
- **Status:** MISSING
- **Type:** Boolean
- **Impact:** Cannot lock lessons to prevent access
- **Default:** Should default to `false`

#### 9. **Active Status** (`is_active`)
- **Status:** MISSING
- **Type:** Boolean
- **Impact:** Cannot deactivate lessons without deleting
- **Default:** Should default to `true`

### 🟢 LOW PRIORITY - Additional Features

#### 10. **Video Duration** (`video_duration`)
- **Status:** MISSING
- **Type:** Integer (seconds or minutes)
- **Usage:** Display video length info

#### 11. **Question of Day** (`question_of_day`)
- **Status:** MISSING
- **Type:** Text/Integer
- **Usage:** Special daily question feature

#### 12. **Implementation Guidance** (`implementation_guidance`)
- **Status:** MISSING
- **Type:** Text
- **Usage:** Additional guidance for instructors/students

#### 13. **Has Levels** (`has_levels`)
- **Status:** MISSING
- **Type:** Boolean
- **Usage:** Multi-level lessons feature

#### 14. **Total Levels** (`total_levels`)
- **Status:** MISSING
- **Type:** Integer
- **Usage:** Number of levels if `has_levels` is true

## Recommended Implementation Order

### Phase 1 - Critical (Implement First)
1. **Video URL** - Conditional field when lesson_type = 'video'
2. **Learning Objectives** - Textarea field
3. **Attachments** - File upload component
4. **Summary** - Textarea field

### Phase 2 - Important (Implement Second)
5. **Difficulty Level** - Select dropdown
6. **Publishing Status** - Checkbox/toggle
7. **Free Preview** - Checkbox
8. **Lock Status** - Checkbox
9. **Active Status** - Checkbox

### Phase 3 - Nice to Have (Implement Later)
10. Video Duration
11. Question of Day
12. Implementation Guidance
13. Has Levels & Total Levels

## Model Fields Reference

From `app/Models/Lesson.php` fillable array:
```php
[
    'course_id',           // Auto-set from course
    'module_id',           // ✅ HAS
    'title',               // ✅ HAS
    'slug',                // Auto-generated
    'content',             // ✅ HAS
    'summary',             // ❌ MISSING
    'order',               // ❌ MISSING (uses order_index)
    'order_index',         // ✅ HAS
    'duration_minutes',    // ✅ HAS
    'video_url',           // ❌ MISSING - CRITICAL
    'video_duration',      // ❌ MISSING
    'question_of_day',     // ❌ MISSING
    'objectives',          // ❌ MISSING - CRITICAL
    'implementation_guidance', // ❌ MISSING
    'lesson_type',         // ✅ HAS
    'difficulty_level',    // ❌ MISSING
    'has_levels',          // ❌ MISSING
    'total_levels',        // ❌ MISSING
    'is_published',        // ❌ MISSING
    'is_free_preview',     // ❌ MISSING
    'is_locked',           // ❌ MISSING
    'is_active',           // ❌ MISSING
    'attachments',         // ❌ MISSING - CRITICAL
]
```

## Form Validation Updates Needed

Current validation in `Builder.php` line 323-329:
```php
'lesson' => [
    'formData.title' => 'required|string|max:255',
    'formData.content' => 'nullable|string',
    'formData.module_id' => 'required|exists:course_modules,id',
    'formData.lesson_type' => 'required|in:text,video,interactive,quiz',
    'formData.order_index' => 'required|integer',
],
```

**Needs to add:**
- `formData.video_url` => 'required_if:formData.lesson_type,video|nullable|url'
- `formData.objectives` => 'nullable|string'
- `formData.difficulty_level` => 'nullable|in:beginner,intermediate,advanced'
- `formData.is_published` => 'nullable|boolean'
- etc.

## Component Updates Needed

1. **getDefaultFormData()** - Add default values for new fields
2. **editItem()** - Load new fields when editing
3. **saveLesson()** - Save new fields
4. **View template** - Add form fields for new properties

