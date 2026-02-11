# Component Usage Guide: Where to Use Each Component

## 🎯 Quick Reference

| Component | Use Case | Where to Use |
|-----------|----------|--------------|
| `<x-progress-bar>` | Show completion percentage | Course pages, dashboards, lesson cards |
| `<x-streak-counter>` | Show consecutive days | Dashboard header, profile page |
| `<x-xp-display>` | Show points earned | Lesson cards, course header, profile |
| `<x-achievement-badge>` | Show unlocked achievements | Achievement page, profile, dashboard |
| `<x-subject-icon>` | Show subject type | Lesson cards, course cards, navigation |
| `<x-scratch-embed>` | Embed Scratch projects | Scratch lesson pages only |
| `<x-scratch-block>` | Show Scratch block visuals | Scratch lesson pages, tutorials |
| `<x-lesson-step>` | Interactive instructions | Any interactive lesson |
| `<x-lesson-card>` | Display lesson preview | Course pages, dashboards, search results |

---

## 📍 Component Placement Examples

### 1. Course Learning Page (`courses/learn.blade.php`)

```blade
{{-- Course Header --}}
<div class="course-header">
    <h1>{{ $course->title }}</h1>
    
    {{-- Show total XP --}}
    <x-xp-display :points="$totalXP" size="lg" />
    
    {{-- Show streak --}}
    <x-streak-counter :days="$streakDays" />
    
    {{-- Overall progress --}}
    <x-progress-bar 
        :percent="$courseProgress" 
        label="Course Progress" 
        color="purple" 
    />
</div>

{{-- Lesson List --}}
@foreach($lessons as $lesson)
    <div class="lesson-item">
        {{-- Subject icon --}}
        <x-subject-icon 
            :subject="$lesson->subject_type" 
            size="md" 
        />
        
        <h3>{{ $lesson->title }}</h3>
        
        {{-- XP for this lesson --}}
        <x-xp-display :points="$lesson->xp_value" size="sm" />
        
        {{-- Progress if started --}}
        @if($lesson->progress > 0)
            <x-progress-bar 
                :percent="$lesson->progress" 
                :showLabel="false" 
                size="sm" 
            />
        @endif
    </div>
@endforeach
```

---

### 2. Lesson View Page (`lessons/view.blade.php`)

```blade
{{-- For Scratch Lessons --}}
@if($lesson->scratch_project_id)
    {{-- Embed the project --}}
    <x-scratch-embed 
        :projectId="$lesson->scratch_project_id"
        :title="$lesson->title"
        :autostart="false"
    />
    
    {{-- Show blocks reference --}}
    <div class="blocks-reference">
        <h3>Blocks You'll Use</h3>
        @foreach($lesson->scratch_blocks as $block)
            <x-scratch-block 
                :type="$block['category']"
                :text="$block['text']"
            />
        @endforeach
    </div>
    
    {{-- Interactive steps --}}
    @foreach($lesson->lesson_steps as $index => $step)
        <x-lesson-step 
            :number="$index + 1"
            :title="$step['title']"
            :image="$step['image'] ?? null"
            :tryItUrl="$step['try_it_url'] ?? null"
        >
            {{ $step['description'] }}
        </x-lesson-step>
    @endforeach
@endif

{{-- Lesson progress --}}
<x-progress-bar 
    :percent="$lessonProgress" 
    label="Lesson Progress" 
    color="blue" 
/>
```

---

### 3. Student Dashboard

```blade
{{-- Welcome Header --}}
<div class="dashboard-header">
    <h1>Welcome Back, {{ $student->name }}! 👋</h1>
    
    <div class="stats">
        {{-- Streak --}}
        <x-streak-counter :days="$student->streak_days" />
        
        {{-- Total XP --}}
        <x-xp-display :points="$student->total_xp" size="lg" />
    </div>
</div>

{{-- Course Progress --}}
@foreach($enrolledCourses as $course)
    <div class="course-card">
        <h3>{{ $course->title }}</h3>
        <x-progress-bar 
            :percent="$course->progress" 
            :label="$course->title" 
            color="purple" 
        />
    </div>
@endforeach

{{-- Recent Achievements --}}
<div class="achievements">
    <h2>Recent Achievements</h2>
    @foreach($recentBadges as $badge)
        <x-achievement-badge 
            :title="$badge->title"
            :description="$badge->description"
            :icon="$badge->icon"
            :earned="true"
            :date="$badge->earned_at->diffForHumans()"
        />
    @endforeach
</div>

{{-- Continue Learning --}}
<div class="lesson-grid">
    @foreach($inProgressLessons as $lesson)
        <x-lesson-card 
            :lesson="$lesson"
            :progress="$lesson->progress"
        />
    @endforeach
</div>
```

---

### 4. Achievement/Badge Page

```blade
<div class="badge-gallery">
    <h1>Your Achievements</h1>
    
    <div class="badge-grid">
        {{-- Earned badges --}}
        @foreach($earnedBadges as $badge)
            <x-achievement-badge 
                :title="$badge->title"
                :description="$badge->description"
                :icon="$badge->icon"
                :earned="true"
                :date="$badge->earned_at->diffForHumans()"
                size="lg"
            />
        @endforeach
        
        {{-- Locked badges --}}
        @foreach($lockedBadges as $badge)
            <x-achievement-badge 
                :title="$badge->title"
                :description="$badge->description"
                :icon="$badge->icon"
                :earned="false"
                size="lg"
            />
        @endforeach
    </div>
</div>
```

---

### 5. Profile Page

```blade
<div class="profile-header">
    <img src="{{ $student->avatar }}" alt="{{ $student->name }}">
    <h1>{{ $student->name }}</h1>
    
    {{-- Stats --}}
    <div class="profile-stats">
        <div class="stat">
            <x-xp-display :points="$student->total_xp" size="lg" />
            <span>Total XP</span>
        </div>
        
        <div class="stat">
            <x-streak-counter :days="$student->streak_days" size="lg" />
        </div>
        
        <div class="stat">
            <span class="number">{{ $student->completed_lessons }}</span>
            <span>Lessons Completed</span>
        </div>
    </div>
</div>

{{-- Course Progress --}}
<div class="course-progress">
    @foreach($courses as $course)
        <x-progress-bar 
            :percent="$course->progress" 
            :label="$course->title" 
            color="purple" 
            size="lg" 
        />
    @endforeach
</div>

{{-- Top Badges --}}
<div class="top-badges">
    @foreach($topBadges as $badge)
        <x-achievement-badge 
            :title="$badge->title"
            :icon="$badge->icon"
            :earned="true"
            size="md"
        />
    @endforeach
</div>
```

---

### 6. Leaderboard Page

```blade
<div class="leaderboard">
    <h1>Top Students</h1>
    
    @foreach($topStudents as $index => $student)
        <div class="leaderboard-row">
            <span class="rank">{{ $index + 1 }}</span>
            <span class="name">{{ $student->name }}</span>
            
            {{-- XP --}}
            <x-xp-display :points="$student->total_xp" size="md" />
            
            {{-- Streak --}}
            <x-streak-counter :days="$student->streak_days" size="sm" />
            
            {{-- Badge count --}}
            <span class="badges">{{ $student->badge_count }} 🏆</span>
        </div>
    @endforeach
</div>
```

---

### 7. Course Card (Grid View)

```blade
<div class="course-grid">
    @foreach($courses as $course)
        <div class="course-card">
            {{-- Subject icon --}}
            <x-subject-icon 
                :subject="$course->subject" 
                size="lg" 
            />
            
            <h3>{{ $course->title }}</h3>
            <p>{{ $course->description }}</p>
            
            {{-- Progress --}}
            <x-progress-bar 
                :percent="$course->progress" 
                color="purple" 
            />
            
            {{-- XP available --}}
            <x-xp-display 
                :points="$course->total_xp" 
                label="XP Available" 
            />
            
            <button>Continue Learning</button>
        </div>
    @endforeach
</div>
```

---

### 8. Lesson Search Results

```blade
<div class="search-results">
    @foreach($lessons as $lesson)
        <div class="result-item">
            {{-- Subject icon --}}
            <x-subject-icon 
                :subject="$lesson->subject" 
                size="sm" 
            />
            
            <div class="info">
                <h4>{{ $lesson->title }}</h4>
                <p>{{ $lesson->description }}</p>
                
                {{-- XP --}}
                <x-xp-display :points="$lesson->xp_value" size="sm" />
            </div>
            
            {{-- Progress if started --}}
            @if($lesson->progress > 0)
                <x-progress-bar 
                    :percent="$lesson->progress" 
                    size="sm" 
                />
            @endif
        </div>
    @endforeach
</div>
```

---

## 🎨 Color Themes by Subject

Use these color values for consistency:

```php
// In your Blade templates
@php
$subjectColors = [
    'scratch' => 'orange',  // Orange/pink gradient
    'python' => 'blue',     // Blue gradient
    'web' => 'green',       // Green gradient
    'video' => 'purple',    // Purple gradient
    'general' => 'purple',  // Default purple
];

$color = $subjectColors[$lesson->subject] ?? 'purple';
@endphp

<x-progress-bar :percent="$progress" :color="$color" />
```

---

## 📏 Size Guidelines

### Progress Bars
- `sm` - Inline, compact (lesson lists)
- `md` - Standard (most uses)
- `lg` - Prominent (headers, profiles)

### XP Display
- `sm` - Inline with text
- `md` - Standard badges
- `lg` - Headers, emphasis

### Subject Icons
- `sm` - Lists, inline
- `md` - Cards, standard
- `lg` - Headers, featured

### Achievement Badges
- `sm` - Compact lists
- `md` - Standard grid
- `lg` - Featured, profile

---

## 🔄 Dynamic Content Examples

### Calculate XP Based on Difficulty
```blade
@php
$xp = match($lesson->difficulty_level) {
    'beginner' => 5,
    'intermediate' => 10,
    'advanced' => 15,
    default => 10,
};
@endphp

<x-xp-display :points="$xp" />
```

### Determine Subject from Lesson
```blade
@php
$subject = 'general';
if ($lesson->scratch_project_id) {
    $subject = 'scratch';
} elseif (str_contains(strtolower($lesson->title), 'python')) {
    $subject = 'python';
} elseif (str_contains(strtolower($lesson->title), 'web')) {
    $subject = 'web';
} elseif ($lesson->video_url) {
    $subject = 'video';
}
@endphp

<x-subject-icon :subject="$subject" />
```

### Progress Color Based on Completion
```blade
@php
$color = $progress < 30 ? 'orange' : 
         ($progress < 70 ? 'blue' : 'green');
@endphp

<x-progress-bar :percent="$progress" :color="$color" />
```

---

## ✅ Best Practices

### DO:
✅ Use consistent sizes across similar contexts  
✅ Match colors to subject themes  
✅ Show progress bars for in-progress items  
✅ Display XP to motivate students  
✅ Use subject icons for quick recognition  
✅ Combine components for rich displays  

### DON'T:
❌ Mix size inconsistently on same page  
❌ Use wrong colors for subjects  
❌ Show progress bars for not-started items  
❌ Overuse animations (keep it subtle)  
❌ Hide important progress information  
❌ Use components without proper data  

---

## 🎯 Component Combinations

### Lesson Card (Full Featured)
```blade
<div class="lesson-card">
    <x-subject-icon :subject="$lesson->subject" size="md" />
    <h3>{{ $lesson->title }}</h3>
    <p>{{ $lesson->description }}</p>
    <x-xp-display :points="$lesson->xp" size="sm" />
    <x-progress-bar :percent="$lesson->progress" size="sm" />
</div>
```

### Student Stats Panel
```blade
<div class="stats-panel">
    <x-streak-counter :days="$streak" />
    <x-xp-display :points="$totalXP" size="lg" />
    <x-progress-bar :percent="$courseProgress" label="Overall Progress" />
</div>
```

### Achievement Showcase
```blade
<div class="achievement-showcase">
    <x-achievement-badge 
        title="Scratch Master" 
        icon="🟦" 
        :earned="true" 
        size="lg" 
    />
    <x-xp-display :points="100" label="XP Earned" :animated="true" />
</div>
```

---

## 📱 Responsive Considerations

All components are responsive, but consider:

- **Mobile:** Use `size="sm"` or `size="md"` for better fit
- **Tablet:** `size="md"` works well
- **Desktop:** `size="lg"` for headers and featured content

---

## 🚀 Quick Start

1. **Copy examples** from this guide
2. **Replace variables** with your data
3. **Test on different screen sizes**
4. **Adjust sizes** as needed
5. **Match colors** to your subjects

---

## 💡 Pro Tips

1. **Consistency is key** - Use same sizes for same contexts
2. **Color coding helps** - Students learn to recognize subjects
3. **Progress motivates** - Show it everywhere
4. **XP is addictive** - Display it prominently
5. **Badges are goals** - Make them visible
6. **Icons are quick** - Use them for instant recognition

---

Your platform now has all the visual components needed to create an engaging, game-like learning experience!
