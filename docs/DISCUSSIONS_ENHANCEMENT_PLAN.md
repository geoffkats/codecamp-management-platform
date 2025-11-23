# Discussions Enhancement Plan 🗣️

## Current State
Your discussions page is functional but basic:
- ❌ Plain text threads
- ❌ No rich content (code, images, Scratch embeds)
- ❌ No engagement features (likes, reactions, badges)
- ❌ Not linked to lessons
- ❌ No gamification

## Enhancement Goals
Transform discussions into an **active learning hub** with:
- ✅ Rich content support (code, images, Scratch projects)
- ✅ Engagement features (upvotes, reactions, badges)
- ✅ Lesson integration
- ✅ Gamification (helper badges, XP for participation)
- ✅ Real-time updates

---

## Phase 1: Rich Content Support

### 1.1 Code Syntax Highlighting
**Add to discussion posts:**
```blade
{{-- In discussion content --}}
@if(str_contains($content, '```'))
    <x-code-block :code="$extractedCode" :language="$detectedLanguage" />
@endif
```

**Features:**
- Auto-detect language (python, javascript, html, css)
- Syntax highlighting
- Copy button
- Line numbers

### 1.2 Scratch Project Embeds
**Allow users to paste Scratch URLs:**
```blade
@if($discussion->scratch_project_id)
    <x-scratch-embed :projectId="$discussion->scratch_project_id" />
@endif
```

### 1.3 Image Uploads
**Add image support:**
- Drag & drop images
- Paste from clipboard
- Screenshot uploads
- Image gallery view

### 1.4 Rich Text Editor
**Replace plain textarea with:**
- TipTap or Quill editor
- Markdown support
- Code block insertion
- Image embedding
- Link previews

---

## Phase 2: Engagement Features

### 2.1 Upvotes/Reactions
**Add reaction system:**
```php
// Database: discussion_reactions table
- discussion_id
- user_id
- reaction_type (upvote, helpful, love, celebrate)
- created_at
```

**UI Component:**
```blade
<div class="flex items-center gap-2">
    <button wire:click="react('upvote')" class="reaction-btn">
        👍 {{ $upvotes }}
    </button>
    <button wire:click="react('helpful')" class="reaction-btn">
        💡 {{ $helpful }}
    </button>
    <button wire:click="react('love')" class="reaction-btn">
        ❤️ {{ $loves }}
    </button>
</div>
```

### 2.2 Best Answer Marking
**Allow marking helpful replies:**
- Teacher can mark "Best Answer"
- Original poster can mark "Solved"
- Best answers get highlighted
- User earns "Helper" badge

### 2.3 Helper Badges
**Award badges for participation:**
- 🥉 **Helper** - 5 helpful answers
- 🥈 **Code Mentor** - 20 helpful answers
- 🥇 **Discussion Master** - 50 helpful answers
- 🏆 **Community Leader** - 100 helpful answers

---

## Phase 3: Lesson Integration

### 3.1 Lesson-Specific Discussions
**Auto-create discussion threads for each lesson:**
```php
// In Lesson model
public function discussion()
{
    return $this->hasOne(Discussion::class);
}

// Auto-create on lesson publish
Discussion::create([
    'title' => "Discussion: {$lesson->title}",
    'lesson_id' => $lesson->id,
    'course_id' => $lesson->course_id,
    'type' => 'lesson_discussion',
    'is_pinned' => true,
]);
```

### 3.2 "Discuss This Lesson" Button
**Add to lesson view:**
```blade
<flux:button 
    href="{{ route('discussions.lesson', $lesson) }}"
    variant="ghost"
    icon="chat">
    Discuss This Lesson ({{ $lesson->discussion->replies_count }})
</flux:button>
```

### 3.3 Subject Tags
**Auto-tag discussions by subject:**
- 🟦 #Scratch
- 🐍 #Python
- 🌐 #WebDev
- ⚡ #JavaScript

**Filter by tag:**
```blade
<div class="flex gap-2">
    <flux:button wire:click="filterByTag('scratch')">🟦 Scratch</flux:button>
    <flux:button wire:click="filterByTag('python')">🐍 Python</flux:button>
    <flux:button wire:click="filterByTag('web')">🌐 Web Dev</flux:button>
</div>
```

---

## Phase 4: Gamification

### 4.1 XP for Participation
**Award XP for:**
- Creating discussion: +5 XP
- Replying: +2 XP
- Getting upvote: +1 XP
- Best answer: +10 XP
- Helping 5 people in a day: +20 XP bonus

### 4.2 Leaderboard
**Show top contributors:**
```blade
<div class="top-contributors">
    <h3>Top Helpers This Week</h3>
    @foreach($topHelpers as $helper)
        <div class="helper-card">
            <img src="{{ $helper->avatar }}" />
            <span>{{ $helper->name }}</span>
            <x-xp-display :points="$helper->weekly_xp" size="sm" />
        </div>
    @endforeach
</div>
```

### 4.3 Streak Tracking
**Track discussion participation streaks:**
- Days in a row helping others
- Display streak counter
- Award bonus XP for streaks

---

## Phase 5: Real-Time Features

### 5.1 Live Updates
**Use Livewire polling:**
```blade
<div wire:poll.5s>
    {{-- New replies appear automatically --}}
    @foreach($replies as $reply)
        <x-discussion-reply :reply="$reply" />
    @endforeach
</div>
```

### 5.2 Active Indicator
**Show who's viewing:**
```blade
<div class="active-viewers">
    <span class="pulse-dot"></span>
    {{ $activeViewers }} viewing now
</div>
```

### 5.3 Typing Indicator
**Show when someone is typing:**
```blade
<div wire:poll.1s class="typing-indicator">
    @if($someoneTyping)
        <span class="animate-pulse">{{ $typingUser }} is typing...</span>
    @endif
</div>
```

---

## Phase 6: Teacher Tools

### 6.1 Pin Important Posts
**Already exists, enhance UI:**
```blade
@if($discussion->is_pinned)
    <div class="pinned-banner">
        📌 Pinned by {{ $discussion->pinned_by->name }}
    </div>
@endif
```

### 6.2 Filter by Subject
**Add subject filter:**
```blade
<flux:select wire:model.live="subjectFilter">
    <option value="all">All Subjects</option>
    <option value="scratch">🟦 Scratch</option>
    <option value="python">🐍 Python</option>
    <option value="web">🌐 Web Dev</option>
</flux:select>
```

### 6.3 Export Discussions
**Add export button:**
```blade
<flux:button wire:click="exportDiscussions" icon="download">
    Export to PDF
</flux:button>
```

---

## Phase 7: Attendance Integration

### 7.1 Check-In Requirement
**Only checked-in students can post:**
```php
public function createReply()
{
    // Check if student checked in today
    $checkedIn = Attendance::where('user_id', auth()->id())
        ->whereDate('check_in_time', today())
        ->exists();
    
    if (!$checkedIn) {
        session()->flash('error', 'Please check in to participate in discussions.');
        return;
    }
    
    // Create reply...
}
```

### 7.2 Attendance Badge
**Show check-in status:**
```blade
@if($user->checkedInToday())
    <span class="badge badge-success">✓ Checked In</span>
@endif
```

---

## Implementation Priority

### Quick Wins (1-2 hours)
1. ✅ Add subject tags/icons
2. ✅ Add upvote buttons
3. ✅ Add "Discuss This Lesson" button to lessons
4. ✅ Add XP for participation

### Medium Effort (3-5 hours)
1. ✅ Code syntax highlighting
2. ✅ Scratch embed support
3. ✅ Best answer marking
4. ✅ Helper badges

### Long Term (1-2 days)
1. ✅ Rich text editor
2. ✅ Image uploads
3. ✅ Real-time updates
4. ✅ Leaderboard

---

## Database Changes Needed

### Add to discussions table:
```php
$table->foreignId('lesson_id')->nullable()->constrained();
$table->string('subject_tag')->nullable(); // scratch, python, web
$table->integer('upvotes')->default(0);
$table->integer('helpful_count')->default(0);
$table->boolean('has_best_answer')->default(false);
```

### Create discussion_reactions table:
```php
Schema::create('discussion_reactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('discussion_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('reaction_type'); // upvote, helpful, love, celebrate
    $table->timestamps();
    
    $table->unique(['discussion_id', 'user_id', 'reaction_type']);
});
```

### Create discussion_attachments table:
```php
Schema::create('discussion_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('discussion_id')->constrained()->cascadeOnDelete();
    $table->string('file_path');
    $table->string('file_type'); // image, code, scratch
    $table->string('mime_type')->nullable();
    $table->timestamps();
});
```

---

## Component Files to Create

### 1. Discussion Card Component
```blade
{{-- resources/views/components/discussion-card.blade.php --}}
<x-discussion-card 
    :discussion="$discussion"
    :showSubject="true"
    :showReactions="true"
/>
```

### 2. Code Block Component
```blade
{{-- resources/views/components/code-block.blade.php --}}
<x-code-block 
    :code="$code"
    :language="$language"
    :showLineNumbers="true"
/>
```

### 3. Discussion Reply Component
```blade
{{-- resources/views/components/discussion-reply.blade.php --}}
<x-discussion-reply 
    :reply="$reply"
    :canMarkBest="$canMarkBest"
    :isBestAnswer="$isBestAnswer"
/>
```

### 4. Reaction Buttons Component
```blade
{{-- resources/views/components/reaction-buttons.blade.php --}}
<x-reaction-buttons 
    :discussionId="$discussion->id"
    :reactions="$discussion->reactions"
/>
```

---

## Example Enhanced Discussion Card

```blade
<div class="discussion-card">
    {{-- Header --}}
    <div class="flex items-start gap-4">
        <x-subject-icon :subject="$discussion->subject_tag" size="md" />
        <div class="flex-1">
            <h3>{{ $discussion->title }}</h3>
            <div class="meta">
                <span>{{ $discussion->user->name }}</span>
                @if($discussion->user->checkedInToday())
                    <span class="badge">✓ Active</span>
                @endif
                <span>{{ $discussion->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    {{-- Content with rich formatting --}}
    <div class="content">
        {!! $discussion->formatted_content !!}
        
        @if($discussion->scratch_project_id)
            <x-scratch-embed :projectId="$discussion->scratch_project_id" />
        @endif
        
        @if($discussion->code_snippets)
            @foreach($discussion->code_snippets as $snippet)
                <x-code-block :code="$snippet['code']" :language="$snippet['language']" />
            @endforeach
        @endif
    </div>

    {{-- Reactions --}}
    <x-reaction-buttons :discussionId="$discussion->id" />

    {{-- Stats --}}
    <div class="stats">
        <span>💬 {{ $discussion->replies_count }} replies</span>
        <span>👁️ {{ $discussion->views_count }} views</span>
        @if($discussion->has_best_answer)
            <span class="text-green-600">✓ Solved</span>
        @endif
    </div>
</div>
```

---

## Next Steps

1. **Start with Quick Wins:**
   - Add subject tags to discussions
   - Add upvote buttons
   - Link discussions to lessons

2. **Then Add Rich Content:**
   - Code syntax highlighting
   - Scratch embed support
   - Image uploads

3. **Finally Add Gamification:**
   - Helper badges
   - XP system
   - Leaderboard

---

## Summary

Your discussions will transform from:
- ❌ Plain text threads
- ❌ No engagement
- ❌ Disconnected from lessons

To:
- ✅ Rich content (code, images, Scratch)
- ✅ Active engagement (reactions, badges, XP)
- ✅ Integrated with curriculum
- ✅ Gamified learning hub
- ✅ Real-time collaboration

**This will make discussions a core part of the learning experience, not just an afterthought!** 🚀
