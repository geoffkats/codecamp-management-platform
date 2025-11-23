<?php

namespace App\Livewire\Admin;

use App\Models\TeacherFeedback;
use App\Models\User;
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
    public $showModal = false;

    public function mount()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function viewFeedback($id)
    {
        $this->selectedFeedback = TeacherFeedback::with(['student', 'teacher', 'course', 'reviewer'])->find($id);
        $this->adminResponse = $this->selectedFeedback->admin_response ?? '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedFeedback = null;
        $this->adminResponse = '';
    }

    public function markAsReviewed()
    {
        if (!$this->selectedFeedback) {
            return;
        }

        $this->selectedFeedback->update([
            'status' => 'reviewed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_response' => $this->adminResponse,
        ]);

        // Notify student if not anonymous
        if (!$this->selectedFeedback->is_anonymous) {
            \App\Models\Notification::create([
                'user_id' => $this->selectedFeedback->student_id,
                'title' => 'Feedback Reviewed',
                'message' => 'Your feedback about ' . $this->selectedFeedback->teacher->name . ' has been reviewed by administration.',
                'type' => 'info',
            ]);
        }

        session()->flash('message', 'Feedback marked as reviewed and response saved.');
        $this->closeModal();
    }

    public function markAsResolved($id)
    {
        $feedback = TeacherFeedback::find($id);
        if ($feedback) {
            $feedback->update([
                'status' => 'resolved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            session()->flash('message', 'Feedback marked as resolved.');
        }
    }

    public function exportCSV()
    {
        $query = TeacherFeedback::with(['student', 'teacher', 'course']);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterTeacher) {
            $query->where('teacher_id', $this->filterTeacher);
        }
        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        $feedback = $query->get();

        $filename = 'teacher_feedback_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($feedback) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Student', 'Teacher', 'Course', 'Category', 'Rating', 'Feedback', 'Status', 'Anonymous']);

            foreach ($feedback as $item) {
                fputcsv($file, [
                    $item->created_at->format('Y-m-d H:i'),
                    $item->is_anonymous ? 'Anonymous' : $item->student->name,
                    $item->teacher->name,
                    $item->course ? $item->course->title : 'General',
                    ucfirst(str_replace('_', ' ', $item->category)),
                    $item->rating ?? 'N/A',
                    $item->feedback,
                    ucfirst($item->status),
                    $item->is_anonymous ? 'Yes' : 'No',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        $feedbackList = $query->paginate(15);

        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => TeacherFeedback::count(),
            'pending' => TeacherFeedback::where('status', 'pending')->count(),
            'reviewed' => TeacherFeedback::where('status', 'reviewed')->count(),
            'resolved' => TeacherFeedback::where('status', 'resolved')->count(),
            'average_rating' => round(TeacherFeedback::whereNotNull('rating')->avg('rating'), 1),
        ];

        return view('livewire.admin.manage-teacher-feedback', [
            'feedbackList' => $feedbackList,
            'teachers' => $teachers,
            'stats' => $stats,
        ]);
    }
}
