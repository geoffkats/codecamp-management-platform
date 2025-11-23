<?php

namespace App\Livewire\Feedback;

use App\Models\Course;
use App\Models\TeacherFeedback;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SubmitTeacherFeedback extends Component
{
    public $teacher_id;
    public $course_id;
    public $category = 'general';
    public $rating;
    public $feedback;
    public $is_anonymous = false;

    public function mount()
    {
        // Check if user is a student
        if (!Auth::user()->studentProfile) {
            abort(403, 'Only students can submit teacher feedback.');
        }
    }

    public function submit()
    {
        $this->validate([
            'teacher_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'category' => 'required|in:teaching_quality,communication,support,professionalism,general',
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'required|string|min:10|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        // Verify the teacher is actually a teacher
        $teacher = User::find($this->teacher_id);
        if (!$teacher->hasRole('teacher')) {
            session()->flash('error', 'Selected user is not a teacher.');
            return;
        }

        TeacherFeedback::create([
            'student_id' => Auth::id(),
            'teacher_id' => $this->teacher_id,
            'course_id' => $this->course_id,
            'category' => $this->category,
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'is_anonymous' => $this->is_anonymous,
            'status' => 'pending',
        ]);

        // Notify admins
        $admins = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Teacher Feedback',
                'message' => ($this->is_anonymous ? 'Anonymous student' : Auth::user()->name) . ' submitted feedback about ' . $teacher->name,
                'type' => 'info',
            ]);
        }

        session()->flash('message', 'Thank you! Your feedback has been submitted to the administration.');
        
        $this->reset(['teacher_id', 'course_id', 'category', 'rating', 'feedback', 'is_anonymous']);
    }

    public function render()
    {
        // Get teachers from enrolled courses
        $enrolledCourses = Auth::user()->enrollments()->with('course.instructor')->get();
        $teachers = $enrolledCourses->pluck('course.instructor')->unique('id')->filter();

        // Get all courses for the selected teacher
        $courses = collect();
        if ($this->teacher_id) {
            $courses = Course::where('instructor_id', $this->teacher_id)
                ->whereHas('enrollments', function($q) {
                    $q->where('user_id', Auth::id());
                })
                ->get();
        }

        return view('livewire.feedback.submit-teacher-feedback', [
            'teachers' => $teachers,
            'courses' => $courses,
        ]);
    }
}
