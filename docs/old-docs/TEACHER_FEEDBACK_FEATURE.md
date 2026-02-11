# Teacher Feedback System - Implementation Guide

## Overview
A comprehensive system allowing students to submit feedback about their teachers to administrators.

## Database Structure

### Table: `teacher_feedback`
- `id` - Primary key
- `student_id` - Foreign key to users (student)
- `teacher_id` - Foreign key to users (teacher)
- `course_id` - Optional foreign key to courses
- `category` - Enum: teaching_quality, communication, support, professionalism, general
- `rating` - Integer 1-5 (optional)
- `feedback` - Text (required, 10-1000 chars)
- `is_anonymous` - Boolean
- `status` - Enum: pending, reviewed, resolved
- `admin_response` - Text (optional)
- `reviewed_by` - Foreign key to users (admin)
- `reviewed_at` - Timestamp
- `timestamps`

## Features Implemented

### 1. Student Submission Form
**Route:** `/feedback/teacher` (to be added)
**Component:** `App\Livewire\Feedback\SubmitTeacherFeedback`

**Features:**
- Select teacher from enrolled courses
- Optional course selection
- Category selection (5 types)
- Star rating (1-5, optional)
- Feedback text (10-1000 chars)
- Anonymous submission option
- Automatic admin notification

### 2. Admin Management (To Complete)
Create: `App\Livewire\Admin\ManageTeacherFeedback`

**Features Needed:**
- View all feedback
- Filter by: teacher, status, category, date
- Mark as reviewed/resolved
- Add admin response
- View statistics
- Export to CSV

## Installation Steps

1. **Run Migration:**
```bash
php artisan migrate
```

2. **Add Route** (in `routes/web.php`):
```php
// Student Feedback Routes
Route::middleware(['auth', 'student.profile'])->group(function () {
    Route::get('/feedback/teacher', \App\Livewire\Feedback\SubmitTeacherFeedback::class)->name('feedback.teacher');
});

// Admin Feedback Management
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/feedback', \App\Livewire\Admin\ManageTeacherFeedback::class)->name('feedback');
});
```

3. **Add to Student Sidebar** (in `resources/views/components/navigation/sidebar.blade.php`):
```php
<flux:navlist.item 
    icon="chat-bubble-left-right" 
    :href="route('feedback.teacher')" 
    :current="request()->routeIs('feedback.teacher')" 
    wire:navigate
>
    {{ __('Teacher Feedback') }}
</flux:navlist.item>
```

4. **Add to Admin Sidebar:**
```php
<flux:navlist.item 
    icon="chat-bubble-bottom-center-text" 
    :href="route('admin.feedback')" 
    :current="request()->routeIs('admin.feedback')" 
    wire:navigate
>
    {{ __('Teacher Feedback') }}
    @php
        $pendingFeedback = \App\Models\TeacherFeedback::where('status', 'pending')->count();
    @endphp
    @if($pendingFeedback > 0)
        <flux:badge size="sm" variant="danger">{{ $pendingFeedback }}</flux:badge>
    @endif
</flux:navlist.item>
```

## Admin Management Component (To Create)

Create file: `app/Livewire/Admin/ManageTeacherFeedback.php`

```php
<?php

namespace App\Livewire\Admin;

use App\Models\TeacherFeedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageTeacherFeedback extends Component
{
    use WithPagination;

    public $filterStatus = 'all';
    public $filterTeacher = '';
    public $filterCategory = 'all';
    public $selectedFeedback = null;
    public $adminResponse = '';

    public function mount()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403);
        }
    }

    public function viewFeedback($id)
    {
        $this->selectedFeedback = TeacherFeedback::with(['student', 'teacher', 'course'])->find($id);
        $this->adminResponse = $this->selectedFeedback->admin_response ?? '';
    }

    public function markAsReviewed()
    {
        if (!$this->selectedFeedback) return;

        $this->selectedFeedback->update([
            'status' => 'reviewed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_response' => $this->adminResponse,
        ]);

        session()->flash('message', 'Feedback marked as reviewed.');
        $this->selectedFeedback = null;
    }

    public function render()
    {
        $query = TeacherFeedback::with(['student', 'teacher', 'course'])
            ->orderBy('created_at', 'desc');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterTeacher) {
            $query->where('teacher_id', $this->filterTeacher);
        }

        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        $feedback = $query->paginate(20);

        $teachers = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->get();

        return view('livewire.admin.manage-teacher-feedback', [
            'feedback' => $feedback,
            'teachers' => $teachers,
        ]);
    }
}
```

## View File (To Create)

Create: `resources/views/livewire/admin/manage-teacher-feedback.blade.php`

Include:
- Filter controls (status, teacher, category)
- Feedback list with cards
- Modal for viewing details
- Admin response form
- Statistics dashboard

## Benefits

1. **For Students:**
   - Safe channel to provide feedback
   - Anonymous option for sensitive issues
   - Improves learning experience

2. **For Admins:**
   - Monitor teaching quality
   - Identify issues early
   - Data-driven decisions
   - Track improvements

3. **For Teachers:**
   - Constructive feedback
   - Improvement opportunities
   - Recognition of good work

## Security & Privacy

- Only students with profiles can submit
- Anonymous submissions protected
- Admin-only access to feedback
- Audit trail (reviewed_by, reviewed_at)

## Future Enhancements

1. Email notifications to admins
2. Feedback trends/analytics
3. Teacher response option (for non-anonymous)
4. Automated sentiment analysis
5. Monthly feedback reports
6. Integration with performance reviews

## Testing Checklist

- [ ] Student can submit feedback
- [ ] Anonymous submissions work
- [ ] Admin receives notification
- [ ] Admin can view all feedback
- [ ] Filters work correctly
- [ ] Admin can respond
- [ ] Status updates work
- [ ] Only enrolled teachers shown
- [ ] Validation works
- [ ] Mobile responsive

---

**Status:** Partially Implemented
**Next Steps:** Create admin management component and views
**Priority:** Medium
