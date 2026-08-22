<?php

namespace App\Livewire\Students;

use App\Models\CampEnrollment;
use App\Models\PeerKudo;
use App\Models\StudentProfile as StudentProfileModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProfile extends Component
{
    use AuthorizesRequests;

    public $student;

    public function mount($student)
    {
        $this->student = StudentProfileModel::with(['user.enrollments.course', 'gadgets', 'attendance', 'school'])
            ->findOrFail($student);

        $this->authorize('view', $this->student);
    }

    public function exportPDF()
    {
        session()->flash('message', 'PDF export feature coming soon!');
    }

    public function downloadData()
    {
        session()->flash('message', 'Data download feature coming soon!');
    }

    public function render()
    {
        $campHistory = CampEnrollment::where('student_id', $this->student->user_id)
            ->with(['camp', 'previousCamp'])
            ->orderByDesc('enrolled_at')
            ->get();

        $campCourses = \App\Models\CourseEnrollment::where('user_id', $this->student->user_id)
            ->whereNotNull('camp_id')
            ->with('course:id,title', 'camp:id,name')
            ->get()
            ->groupBy('camp_id');

        $transferDestinations = CampEnrollment::findTransferDestinations(
            $this->student->user_id,
            $campHistory->pluck('camp_id')->filter()->all()
        );

        // Gamification data for profile
        $badges = $this->student->user
            ? $this->student->user->badges()
                ->orderByPivot('earned_at', 'desc')
                ->get()
            : collect();

        $kudosCount = $this->student->user_id
            ? PeerKudo::where('to_user_id', $this->student->user_id)->count()
            : 0;

        $recentKudos = $this->student->user_id
            ? PeerKudo::where('to_user_id', $this->student->user_id)
                ->with('sender:id,name')
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $view = auth()->user()?->isIctTeacher()
            ? 'livewire.students.student-profile-ict'
            : 'livewire.students.student-profile';

        return view($view, compact(
            'campHistory', 'campCourses', 'transferDestinations',
            'badges', 'kudosCount', 'recentKudos'
        ))->with('canViewLearningAccounts', $this->canViewLearningAccounts());
    }

    private function canViewLearningAccounts(): bool
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('student')) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSupervisor()
            || $user->isOperationsManager()
            || $user->isClubFacilitator()
            || $user->isCodecampTrainer()
            || $user->isIctTeacher()
            || $user->hasRole('teacher');
    }
}
