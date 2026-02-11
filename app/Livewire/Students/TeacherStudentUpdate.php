<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TeacherStudentUpdate extends Component
{
    use AuthorizesRequests;

    public $student;
    public $scratch_account = '';
    public $scratch_password = '';
    public $github_account = '';
    public $class_grade = '';
    public $selectedCourses = [];

    protected $rules = [
        'scratch_account' => 'nullable|string|max:255',
        'scratch_password' => 'nullable|string|max:255',
        'github_account' => 'nullable|string|max:255',
        'class_grade' => 'nullable|string|max:255',
        'selectedCourses' => 'array',
    ];

    public function mount($student)
    {
        // Check authorization - only teachers, admins, and supervisors
        $user = auth()->user();
        if (!$user->isTeacher() && !$user->hasRole('admin') && !$user->hasRole('supervisor')) {
            abort(403, 'Only teachers, admins, and supervisors can update student learning profiles.');
        }

        $this->student = StudentProfile::with(['user', 'user.enrollments', 'user.invitations'])->findOrFail($student);
        $this->authorize('view', $this->student);
        
        $this->scratch_account = $this->student->scratch_account;
        $this->scratch_password = $this->student->scratch_password;
        $this->github_account = $this->student->github_account;
        $this->class_grade = $this->student->class_grade;
        
        // Load enrolled courses and pending invitations
        $enrolledCourses = $this->student->user->enrollments->pluck('course_id')->toArray();
        $pendingInvitations = $this->student->user->invitations()
            ->where('status', 'pending')
            ->pluck('course_id')
            ->toArray();
            
        $this->selectedCourses = array_unique(array_merge($enrolledCourses, $pendingInvitations));
    }

    public function save()
    {
        $this->validate();

        // Update student profile
        $this->student->update([
            'scratch_account' => $this->scratch_account,
            'scratch_password' => $this->scratch_password,
            'github_account' => $this->github_account,
            'class_grade' => $this->class_grade,
        ]);

        // Handle course invitations
        // Get currently selected courses
        $currentEnrollments = $this->student->user->enrollments->pluck('course_id')->toArray();
        
        // Find new courses to invite to
        $newCourses = array_diff($this->selectedCourses, $currentEnrollments);
        
        // Find courses to remove invitations from
        $removedCourses = array_diff($currentEnrollments, $this->selectedCourses);
        
        // Remove invitations for unchecked courses
        foreach ($removedCourses as $courseId) {
            \App\Models\CourseInvitation::where('user_id', $this->student->user_id)
                ->where('course_id', $courseId)
                ->where('status', 'pending')
                ->delete();
                
            // Also remove active enrollments
            $this->student->user->enrollments()
                ->where('course_id', $courseId)
                ->delete();
        }
        
        // Create invitations for new courses
        foreach ($newCourses as $courseId) {
            // Check if invitation already exists
            $existingInvitation = \App\Models\CourseInvitation::where('user_id', $this->student->user_id)
                ->where('course_id', $courseId)
                ->first();
                
            if (!$existingInvitation) {
                \App\Models\CourseInvitation::create([
                    'user_id' => $this->student->user_id,
                    'course_id' => $courseId,
                    'invited_by' => auth()->id(),
                    'status' => 'pending',
                    'invited_at' => now(),
                    'message' => 'You have been invited to enroll in this course.',
                ]);
            }
        }

        $invitationCount = count($newCourses);
        $message = 'Student information updated successfully!';
        if ($invitationCount > 0) {
            $message .= " {$invitationCount} course invitation(s) sent to the student.";
        }
        
        session()->flash('message', $message);
        return redirect()->route('students.show', $this->student->id);
    }

    public function render()
    {
        $courses = Course::query()
            ->when(auth()->user()->isIctTeacher(), function ($query) {
                $schoolId = auth()->user()->ictSchoolId();

                if ($schoolId) {
                    $query->whereHas('schools', function ($q) use ($schoolId) {
                        $q->where('school_id', $schoolId)->where('is_active', true);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('title')
            ->get();
        
        return view('livewire.students.teacher-student-update', [
            'courses' => $courses,
        ]);
    }
}
