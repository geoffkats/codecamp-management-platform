<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public Course $course;
    public $enrolled = false;
    public $enrollment = null;

    public function mount(Course $course)
    {
        $this->course = $course->load(['instructor', 'modules.lessons', 'assessments']);
        
        if (Auth::check()) {
            $this->enrollment = CourseEnrollment::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->first();
            $this->enrolled = $this->enrollment !== null;
        }
    }

    public function enroll()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'));
        }

        if ($this->enrolled) {
            session()->flash('message', 'You are already enrolled in this course.');
            return;
        }

        // Check if course is published and approved
        if (!$this->course->is_published) {
            session()->flash('error', 'This course is not yet published.');
            return;
        }

        if ($this->course->approval_status !== 'approved') {
            session()->flash('error', 'This course is pending approval and not available for enrollment yet.');
            return;
        }

        // Check enrollment type restrictions
        $enrollmentType = $this->course->enrollment_type ?? 'open';

        if ($enrollmentType === 'invite_only') {
            // Check if user has an active invitation
            $invitation = \App\Models\CourseInvitation::where('course_id', $this->course->id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (!$invitation || $invitation->isExpired()) {
                session()->flash('error', 'This course is invite-only. You need an invitation to enroll.');
                return;
            }

            // Accept the invitation
            $invitation->accept();
        } elseif ($enrollmentType === 'approval_required') {
            // Check if user already has a pending request
            $existingRequest = \App\Models\EnrollmentRequest::where('course_id', $this->course->id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                session()->flash('message', 'Your enrollment request is pending approval.');
                return;
            }

            // Create enrollment request
            \App\Models\EnrollmentRequest::create([
                'course_id' => $this->course->id,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // Notify instructor
            \App\Models\Notification::create([
                'user_id' => $this->course->instructor_id,
                'title' => 'New Enrollment Request',
                'message' => Auth::user()->name . ' has requested to enroll in "' . $this->course->title . '"',
                'type' => 'info',
                'data' => [
                    'course_id' => $this->course->id,
                    'student_id' => Auth::id(),
                ],
                'is_read' => false,
            ]);

            session()->flash('message', 'Enrollment request submitted. Waiting for instructor approval.');
            return;
        }

        // Check max students limit
        if ($this->course->max_students) {
            $currentEnrollments = CourseEnrollment::where('course_id', $this->course->id)->count();
            if ($currentEnrollments >= $this->course->max_students) {
                session()->flash('error', 'This course has reached maximum enrollment capacity.');
                return;
            }
        }

        // Open enrollment - proceed
        CourseEnrollment::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        $this->enrolled = true;
        $this->enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        // Ensure UserPoints exists
        $user = Auth::user();
        if (!$user->points) {
            \App\Models\UserPoint::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);
            $user->refresh();
        }

        // Award points for enrollment
        $user->points->increment('total_points', 50);

        // Create user progress entry
        \App\Models\UserProgress::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'type' => 'course_enrolled',
            'points_earned' => 50,
        ]);

        session()->flash('message', 'Successfully enrolled in course!');
        $this->dispatch('course-enrolled');
    }

    public function render()
    {
        $modules = $this->course->modules()
            ->with(['lessons' => function ($q) {
                $q->orderBy('order_index');
            }])
            ->orderBy('order_index')
            ->get();

        $reviews = []; // Can add reviews later
        $similarCourses = Course::where('category', $this->course->category)
            ->where('id', '!=', $this->course->id)
            ->where('is_published', true)
            ->where('approval_status', 'approved')
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(4)
            ->get();

        return view('livewire.courses.show', [
            'modules' => $modules,
            'reviews' => $reviews,
            'similarCourses' => $similarCourses,
        ]);
    }
}
