# Visual Components Library

This directory contains reusable Blade components for creating engaging, visual, game-like learning experiences.

## 📚 Component Categories

### Code Editors (NEW! 🆕)
- `code-editor.blade.php` - Python/JavaScript code editor with execution
- `web-editor.blade.php` - HTML/CSS/JS split-screen editor with live preview

### Scratch-Specific Components
- `scratch-embed.blade.php` - Embedded Scratch project player
- `scratch-block.blade.php` - Colorful Scratch block visuals
- `lesson-step.blade.php` - Interactive step-by-step instructions

### Progress & Gamification
- `progress-bar.blade.php` - Animated progress tracking
- `streak-counter.blade.php` - Fire emoji streak display
- `xp-display.blade.php` - Star-based XP/points system
- `achievement-badge.blade.php` - Unlockable achievement badges

### Subject & Navigation
- `subject-icon.blade.php` - Subject-specific icons (Scratch, Python, Web, etc.)
- `lesson-card.blade.php` - Visual lesson preview cards

---

## 💻 Code Editors (NEW!)

### `code-editor.blade.php`
Interactive code editor for Python and JavaScript with execution capabilities.

**Props:**
- `language` (required) - "python" or "javascript"
- `code` (optional) - Initial code
- `title` (optional) - Editor title
- `editable` (optional) - Allow editing (default: true)
- `showOutput` (optional) - Show output console (default: true)
- `height` (optional) - Editor height (default: "400px")

**Example:**
```blade
<x-code-editor 
    language="python"
    code="print('Hello, World!')"
    title="Python Practice"
/>
```

**Features:**
- ✅ Syntax-highlighted editor
- ✅ Run button with code execution
- ✅ Output console
- ✅ Error handling
- ✅ Reset button
- ✅ Line numbers
- ✅ Security restrictions

### `web-editor.blade.php`
Split-screen web development editor with HTML, CSS, and JavaScript tabs plus live preview.

**Props:**
- `html` (optional) - Initial HTML code
- `css` (optional) - Initial CSS code
- `javascript` (optional) - Initial JavaScript code
- `title` (optional) - Editor title
- `editable` (optional) - Allow editing (default: true)

**Example:**
```blade
<x-web-editor 
    html="<h1>Hello World!</h1>"
    css="h1 { color: blue; }"
    javascript="console.log('Ready!');"
    title="Web Playground"
/>
```

**Features:**
- ✅ Three tabs: HTML, CSS, JavaScript
- ✅ Live preview pane
- ✅ Layout toggle (horizontal/vertical)
- ✅ Auto-update on typing (debounced)
- ✅ Refresh button
- ✅ Fully responsive

---

## 🟦 Scratch Components

### `scratch-embed.blade.php`
Embeds a Scratch project with full player controls.

**Props:**
- `projectId` (required) - Scratch project ID from scratch.mit.edu
- `title` (optional) - Project title
- `autostart` (optional) - Auto-start project (default: false)

**Example:**
```blade
<x-scratch-embed 
    projectId="987654321"
    title="My Awesome Project"
    :autostart="false"
/>
```

### `scratch-block.blade.php`
Displays a colorful Scratch block with category-based styling.

**Props:**
- `type` (required) - Block category: motion, looks, sound, events, control, sensing, operators, variables
- `text` (required) - Block text content

**Example:**
```blade
<x-scratch-block 
    type="motion"
    text="move (10) steps"
/>
```

**Block Categories:**
- `motion` - Blue (movement blocks)
- `looks` - Purple (appearance blocks)
- `sound` - Pink (audio blocks)
- `events` - Yellow (trigger blocks)
- `control` - Orange (loops, conditions)
- `sensing` - Cyan (detection blocks)
- `operators` - Green (math blocks)
- `variables` - Red (data blocks)

### `lesson-step.blade.php`
Collapsible, numbered step-by-step instructions.

**Props:**
- `number` (required) - Step number
- `title` (required) - Step title
- `image` (optional) - Image URL
- `tryItUrl` (optional) - URL for "Try It Yourself" button

**Example:**
```blade
<x-lesson-step 
    :number="1"
    title="Add a Sprite"
    image="/images/step1.png"
    tryItUrl="https://scratch.mit.edu"
>
    <p>Click on "Choose a Sprite" and select your favorite character.</p>
</x-lesson-step>
```

---

## 📊 Progress & Gamification Components

### `progress-bar.blade.php`
Animated progress bar with multiple color themes.

**Props:**
- `percent` (required) - Progress percentage (0-100)
- `label` (optional) - Label text
- `showPercent` (optional) - Show percentage (default: true)
- `size` (optional) - sm|md|lg (default: md)
- `color` (optional) - purple|blue|green|orange (default: purple)

**Example:**
```blade
<x-progress-bar 
    :percent="75" 
    label="Course Progress" 
    color="purple" 
    size="md" 
/>
```

### `streak-counter.blade.php`
Fire emoji streak counter for consecutive days.

**Props:**
- `days` (required) - Number of consecutive days
- `label` (optional) - Label text (default: "Day Streak")
- `icon` (optional) - Emoji icon (default: 🔥)
- `size` (optional) - sm|md|lg (default: md)

**Example:**
```blade
<x-streak-counter 
    :days="7" 
    label="Day Streak" 
    icon="🔥" 
/>
```

### `xp-display.blade.php`
Star-based XP/points display.

**Props:**
- `points` (required) - Number of points
- `label` (optional) - Label text (default: "XP")
- `showLabel` (optional) - Show label (default: true)
- `size` (optional) - sm|md|lg (default: md)
- `animated` (optional) - Pulse animation (default: false)

**Example:**
```blade
<x-xp-display 
    :points="350" 
    label="XP" 
    size="md" 
    :animated="true" 
/>
```

### `achievement-badge.blade.php`
Unlockable achievement badges with earned/locked states.

**Props:**
- `title` (required) - Badge title
- `description` (optional) - Badge description
- `icon` (optional) - Emoji icon (default: 🏆)
- `earned` (optional) - Earned status (default: false)
- `date` (optional) - Date earned
- `size` (optional) - sm|md|lg (default: md)

**Example:**
```blade
<x-achievement-badge 
    title="First Steps" 
    description="Complete your first lesson" 
    icon="👣" 
    :earned="true" 
    date="2 days ago" 
/>
```

---

## 🎨 Subject & Navigation Components

### `subject-icon.blade.php`
Subject-specific icons with color themes.

**Props:**
- `subject` (required) - scratch|python|web|video|interactive|general
- `size` (optional) - sm|md|lg (default: md)

**Example:**
```blade
<x-subject-icon 
    subject="scratch" 
    size="md" 
/>
```

**Available Subjects:**
- `scratch` - 🟦 Orange theme
- `python` - 🐍 Blue theme
- `web` - 🌐 Green theme
- `video` - 🎥 Purple theme
- `interactive` - ⚡ Yellow theme
- `general` - 📚 Gray theme

### `lesson-card.blade.php`
Visual lesson preview card with progress tracking.

**Props:**
- `lesson` (optional) - Lesson object
- `title` (optional) - Lesson title
- `description` (optional) - Lesson description
- `difficulty` (optional) - beginner|intermediate|advanced
- `duration` (optional) - Duration in minutes
- `icon` (optional) - Emoji icon
- `progress` (optional) - Progress percentage (0-100)
- `thumbnail` (optional) - Thumbnail image URL

**Example:**
```blade
<x-lesson-card 
    title="Make Your Sprite Move"
    description="Learn motion blocks"
    difficulty="beginner"
    duration="30"
    icon="🟦"
    :progress="75"
/>
```

---

## 🎯 Usage Examples

### Dashboard Header
```blade
<div class="dashboard-header">
    <h1>Welcome Back!</h1>
    <x-streak-counter :days="7" />
    <x-xp-display :points="350" size="lg" />
</div>
```

### Course Progress
```blade
<x-progress-bar 
    :percent="$courseProgress" 
    label="Course Progress" 
    color="purple" 
/>
```

### Lesson List
```blade
@foreach($lessons as $lesson)
    <div class="lesson-item">
        <x-subject-icon :subject="$lesson->subject" />
        <h3>{{ $lesson->title }}</h3>
        <x-xp-display :points="$lesson->xp" size="sm" />
    </div>
@endforeach
```

### Achievement Gallery
```blade
<div class="badge-grid">
    @foreach($badges as $badge)
        <x-achievement-badge 
            :title="$badge->title"
            :icon="$badge->icon"
            :earned="$badge->earned"
            :date="$badge->earned_at"
        />
    @endforeach
</div>
```

### Scratch Lesson
```blade
<x-scratch-embed :projectId="$lesson->scratch_project_id" />

@foreach($lesson->scratch_blocks as $block)
    <x-scratch-block 
        :type="$block['category']"
        :text="$block['text']"
    />
@endforeach

@foreach($lesson->lesson_steps as $index => $step)
    <x-lesson-step 
        :number="$index + 1"
        :title="$step['title']"
    >
        {{ $step['description'] }}
    </x-lesson-step>
@endforeach
```

---

## 📖 Documentation

- **Component Usage Guide:** `/COMPONENT_USAGE_GUIDE.md` - Detailed usage examples
- **Teacher Quick Start:** `/TEACHER_QUICK_START.md` - How to add visual components
- **Implementation Guide:** `/STUDENT_EXPERIENCE_ENHANCEMENTS.md` - Technical details
- **Before/After:** `/BEFORE_AFTER_COMPARISON.md` - Visual transformation examples

---

## 🧪 Testing

- **Preview Page:** `/student-dashboard-preview` - See all components in action
- **Test Page:** `/test-visual-components` - Component testing page

---

## 🎨 Design System

### Colors by Subject
- Scratch: Orange/Pink gradient
- Python: Blue gradient
- Web Dev: Green gradient
- Video: Purple gradient
- Interactive: Yellow gradient

### Sizes
- `sm` - Compact, inline use
- `md` - Standard, most common
- `lg` - Prominent, headers

### Responsive
All components are fully responsive and work on:
- ✅ Desktop
- ✅ Tablet
- ✅ Mobile

---

## ✅ Best Practices

1. **Use consistent sizes** across similar contexts
2. **Match colors to subjects** for visual consistency
3. **Show progress** to motivate students
4. **Display XP** to encourage completion
5. **Use subject icons** for quick recognition
6. **Combine components** for rich experiences

---

## 🚀 Quick Start

1. Choose the component you need
2. Copy the example code
3. Replace props with your data
4. Test on different screen sizes
5. Adjust as needed

Your platform now has everything needed to create an engaging, game-like learning experience!
