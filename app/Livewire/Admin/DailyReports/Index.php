<?php

namespace App\Livewire\Admin\DailyReports;

use App\Models\Course;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $date;
    public $courseId;
    public $instructorId;
    public $status;

    public function mount(): void
    {
        // Ensure only admin/supervisor enter
        $user = Auth::user();
        if (!(method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('supervisor')))) {
            abort(403);
        }
    }

    public function updating($field): void
    {
        if (in_array($field, ['date', 'courseId', 'instructorId', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = DailyReport::with(['course:id,title', 'instructor:id,name'])
            ->orderByDesc('report_date')
            ->orderByDesc('submitted_at');

        if ($this->date) {
            $query->whereDate('report_date', $this->date);
        }
        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        }
        if ($this->instructorId) {
            $query->where('instructor_id', $this->instructorId);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return view('livewire.admin.daily-reports.index', [
            'reports' => $query->paginate(15),
            'courses' => Course::orderBy('title')->get(['id', 'title']),
            'instructors' => User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['teacher', 'ict_teacher'])->orWhere('name', 'instructor');
            })->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
