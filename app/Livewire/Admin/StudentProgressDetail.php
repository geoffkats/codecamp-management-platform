<?php

namespace App\Livewire\Admin;

use App\Models\CourseEnrollment;
use App\Models\StudentProfile;
use App\Services\PointsService;
use App\Services\Reports\StudentProgressReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProgressDetail extends Component
{
    public int $studentProfileId;

    public function mount(StudentProfile $student): void
    {
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }

        $this->studentProfileId = (int) $student->id;
    }

    public function refreshReport(StudentProgressReportService $reportService): void
    {
        $reportService->invalidateReportCache();
        session()->flash('message', 'Course filter list refreshed.');
    }

    public function markCourseCompletedHalfXp(
        int $enrollmentId,
        StudentProgressReportService $reportService,
        PointsService $pointsService
    ): void {
        $profile = StudentProfile::findOrFail($this->studentProfileId);

        if (!$profile->user_id) {
            session()->flash('message', 'Student has no linked user account.');
            return;
        }

        $enrollment = CourseEnrollment::query()
            ->where('id', $enrollmentId)
            ->where('user_id', $profile->user_id)
            ->firstOrFail();

        $enrollment->update([
            'progress_percentage' => 100,
            'completed_at' => $enrollment->completed_at ?: now(),
        ]);

        $awarded = $pointsService->awardCourseCompletionPointsHalf(
            (int) $enrollment->user_id,
            (int) $enrollment->course_id
        );

        $reportService->invalidateReportCache();

        if ($awarded) {
            session()->flash('message', 'Course marked completed and 50 XP awarded (50% completion XP).');
            return;
        }

        session()->flash('message', 'Course marked completed. Completion XP was already awarded before, so no extra XP was added.');
    }

    public function render(StudentProgressReportService $reportService)
    {
        $detail = $reportService->getStudentDetail($this->studentProfileId);

        if (!$detail) {
            abort(404, 'Student progress detail not found.');
        }

        return view('livewire.admin.student-progress-detail', [
            'detail' => $detail,
        ]);
    }
}
