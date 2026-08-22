# Curriculum Builder Sidebar Redesign
## Simplifying Cognitive Load for All User Levels

**Problem:** Current sidebar mixes navigation, management, and status reporting = cognitive overload.

**Solution:** Pure navigation sidebar + delegated management flows.

---

## 🎯 3-Level UX Architecture

### Level 1: Beginner Instructors
**Goal:** Get them from course → lesson → editing in <10 seconds.

**What They See:**
- Course title
- Module list (collapsed by default)
- Click module → see lessons
- Click lesson → edit it
- One "Add" button per context

**What's Hidden:**
- No archived view
- No status dots
- No restore buttons
- No action menus
- Single-purpose buttons

**Interaction Pattern:**
```
Course Overview
├─ Module 1 → [Click] → 
│   ├─ Lesson A
│   ├─ Lesson B
│   └─ + Add Lesson
└─ Module 2
   └─ Lesson C
```

---

### Level 2: Power Instructors
**Goal:** Full course building + quick content management without leaving sidebar.

**What They See:**
- Everything in Level 1
- PLUS: Status indicators (green = approved, yellow = pending)
- PLUS: Quick action menu (⋮ More)
- PLUS: Drag-to-reorder (future)
- PLUS: Inline quick-edit option

**Interaction Pattern:**
```
Course Overview
├─ Module 1 [3/4 approved] → 
│   ├─ Lesson A ✓
│   ├─ Lesson B ⏳
│   ├─ Lesson C ○
│   ├─ ⋮ [Edit, Duplicate, Archive]
│   └─ + Add Lesson
└─ Module 2 [2/2 approved]
```

**Status Meanings:**
- ✓ Green = Approved
- ⏳ Yellow = Pending approval
- ○ Gray = Draft
- 🔒 Lock icon = Locked to students

---

### Level 3: Admin/Supervisor
**Goal:** system oversight + batch operations + approval management

**What They See:**
- Everything in Level 2
- PLUS: Course-wide approval status indicator
- PLUS: Archived view (in sidebar footer as "Show Archived")
- PLUS: Batch actions (select multiple, archive/approve/restore)
- PLUS: User info (who made changes)
- PLUS: Restore options for deleted content

**Interaction Pattern:**
```
Course Overview [5/8 lessons approved]
├─ Module 1 (Last edited: Sarah Chen, 2 days ago)
│   ├─ ☑ Lesson A ✓ (approved by Alex)
│   ├─ ☑ Lesson B ⏳ (pending review)
│   └─ ⋮ [Edit, Duplicate, Lock, Archive, History]
└─ [⊕ Show Archived (2 items)]
```

---

## 🏗️ Information Architecture Changes

### What Stays in Sidebar
- ✅ Module navigation
- ✅ Lesson list per module
- ✅ Add Module / Add Lesson buttons
- ✅ Status indicators (Level 2+)
- ✅ Quick actions menu (Level 2+)

### What Moves to Course Settings Panel
- ❌ Archive/Restore (→ Course Settings → Archived Content)
- ❌ Course-wide settings (→ Course Settings → Details)
- ❌ Collaborator management (→ Course Settings → Team)
- ❌ Approval workflow (→ Course Settings → Approval)
- ❌ Content locking (→ Course Settings → Access Control)

### What Moves to Lesson Editor
- ❌ Individual lesson settings (approval status detail)
- ❌ Assessment list (already there)
- ❌ Detailed action menus

---

## 🎨 Visual Simplification

### Current Sidebar
```
W: 384px
├─ Module Expansion Panels (details open)
├─ Table-like lesson rows
│  ├─ Status dot
│  ├─ Title
│  ├─ Hover state
│  ├─ Action menu button
│  └─ Hidden dropdown
└─ 5+ interactive cues per row
```

### Simplified Sidebar
```
W: 320px (slightly narrower)
├─ Module list (clean card layout)
│  ├─ Title + status badge (Level 2+)
│  │  └─ Click → expand lessons
│  └─ Plain text lesson list
│     ├─ Status dot (Level 2+)
│     ├─ Title
│     └─ ⋮ menu (only 3 actions max)
└─ 2-3 interactive cues per row
```

---

## 📋 Implementation Roadmap

### Phase 1: Beginner-First Sidebar (This PR)
- Remove archived tab
- Remove management buttons
- Remove approval status dots
- Remove mode-dependent UI
- Simplify lesson row structure
- Clean up table styling

**Result:** Minimal, intentional sidebar

### Phase 2: Status Indicators (Next PR)
- Add small status badges next to lessons
- Show approval status colors
- Add `canViewStatus` permission check
- Light-weight visual only (no actions here)

### Phase 3: Power User Menu (Following PR)
- Add ⋮ menu with Edit, Duplicate, Archive
- Make menu contextual (different for Level 1 vs Level 2)
- Add drag-to-reorder skeleton

### Phase 4: Admin Oversight (Future)
- Batch selection
- Course-wide progress indicator
- Restore UI for archived content
- Change history

---

## 🔧 Blade Implementation Strategy

### Sidebar Component Logic
```php
// BuilderSidebar.php

// Determine user level
$userLevel = match(true) {
    $this->isAdmin || $this->isSupervisor => 'admin',
    $this->canManageCourse => 'power',
    default => 'beginner'
};

// Pass only relevant data per level
$viewData = match($userLevel) {
    'beginner' => [
        'modules' => $this->activeModules,
        'lessons' => $this->activeLessonsByModule,
    ],
    'power' => [
        'modules' => $this->activeModules,
        'lessons' => $this->activeLessonsByModule,
        'approvalStatus' => $this->lessonApprovalStatus,
    ],
    'admin' => [
        'modules' => $this->allModules, // includes archived
        'lessons' => $this->allLessonsByModule,
        'approvalStatus' => $this->lessonApprovalStatus,
        'archived' => $this->archivedStructureModules,
    ]
};
```

### Sidebar Template Structure
```blade
{{-- Pure navigation, no management logic --}}

@foreach($modules as $module)
    <div class="module-card">
        <button @click="toggleModule({{ $module->id }})">
            {{ $module->title }}
            @if($userLevel !== 'beginner')
                <span class="status-badge">{{ moduleStatusCount }}</span>
            @endif
        </button>
        
        @if($isModuleOpen)
            <div class="lessons">
                @foreach($lessons as $lesson)
                    <button @click="selectLesson({{ $lesson->id }})">
                        @if($userLevel !== 'beginner')
                            <span class="status-dot">{{ $lesson->approvalStatus }}</span>
                        @endif
                        {{ $lesson->title }}
                    </button>
                @endforeach
                
                <button class="add-lesson">+ Add Lesson</button>
            </div>
        @endif
    </div>
@endforeach
```

---

## 🎓 User Research Validation

### Test Question for Level 1 Users
"Without thinking, click where you'd add a new lesson to Module 1."

**Success:** Under 3 seconds, no hesitation.

**Current Failure:** "Is it the + button? Or the menu? Or...?"

### Test Question for Level 2 Users
"Show me all lessons pending approval."

**Success:** Scan sidebar, identify yellow dots, know immediately.

**Current Failure:** Have to open lesson editor for each one.

### Test Question for Level 3 Users
"Archive lesson A and restore lesson B."

**Success:** Both operations in sidebar or dedicated settings panel.

**Current Failure:** Mixed signals about where to do it.

---

## ✅ Success Metrics

| Metric | Before | Target |
|--------|--------|--------|
| Time to add lesson | 15s | <5s |
| Sidebar visual complexity | 9/10 | 3/10 |
| Cognitive load (user survey) | 4.2/5 | 1.8/5 |
| "Feels like admin software" | 62% agree | 15% agree |
| "Feels creative" | 28% agree | 78% agree |

---

## 🚀 Design Principles Going Forward

1. **Navigation is separate from management**
   - Sidebar = Where am I?
   - Settings panel = What can I do?

2. **Hide complexity by default**
   - Level 1 → Beginner-optimized
   - Level 2+ → Unlock advanced features as needed

3. **Status is information, not control**
   - Show approval status
   - But don't manage approval in sidebar

4. **One action per interaction**
   - Click lesson → open editor
   - Don't do click → open dropdown → select action

5. **Preserve mental model stability**
   - UI layout doesn't change based on mode
   - What worked yesterday works today

