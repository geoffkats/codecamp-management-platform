<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageStudents extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';
    public $filterClass = '';
    public $selected = [];
    public $selectAll = false;
    public $showAssignModal = false;
    public $selectedCourseId = '';
    public $notifyStudents = true;
    public $filterEnrollment = '';
    public $filterEnrollmentCourseId = '';
    public $filterCategory = '';
    public $filterReadiness = '';
    public $filterModuleId = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterClass()
    {
        $this->resetPage();
    }

    public function updatingFilterEnrollment()
    {
        $this->resetPage();
    }

    public function updatingFilterEnrollmentCourseId()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterReadiness()
    {
        $this->resetPage();
    }

    public function updatingFilterModuleId()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->currentStudentIds();
        } else {
            $this->selected = [];
        }
    }

    public function openAssignModal()
    {
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
    }

    public function assignSelectedToCourse()
    {
        $this->validate([
            'selected' => 'required|array|min:1',
            'selectedCourseId' => 'required|exists:courses,id',
        ], [
            'selected.required' => 'Select at least one student.',
            'selectedCourseId.required' => 'Choose a course to assign.',
        ]);

        if (auth()->user()->isIctTeacher()) {
            $allowedCourseIds = $this->availableIctCourses()->pluck('id')->all();
            if (!in_array((int) $this->selectedCourseId, $allowedCourseIds, true)) {
                abort(403, 'Unauthorized course selection.');
            }
        }

        $courseId = $this->selectedCourseId;
        $enrolledCount = 0;

        $profiles = StudentProfile::with('user')
            ->whereIn('id', $this->selected)
            ->get();

        foreach ($profiles as $profile) {
            if (!$profile->user) {
                continue; // skip profiles without linked user accounts
            }

            $enrollment = CourseEnrollment::firstOrCreate(
                [
                    'user_id' => $profile->user_id,
                    'course_id' => $courseId,
                ],
                [
                    'enrolled_at' => now(),
                    'progress_percentage' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $enrolledCount++;

                if ($this->notifyStudents) {
                    Notification::create([
                        'user_id' => $profile->user_id,
                        'title' => 'New Course Enrollment',
                        'message' => 'You have been enrolled in a new course.',
                        'type' => 'info',
                        'data' => [
                            'course_id' => $courseId,
                            'student_profile_id' => $profile->id,
                        ],
                        'is_read' => false,
                    ]);
                }
            }
        }

        $this->showAssignModal = false;
        $this->selected = [];
        $this->selectAll = false;
        $this->selectedCourseId = '';

        session()->flash('message', "Assigned {$enrolledCount} new enrollment(s).");
    }

    public function markExamReady(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId)->load('user.enrollments');

        $student->exam_readiness_status = 'teacher_approved';
        $student->save();

        session()->flash('message', 'Student marked ICDL Test Ready.');
    }

    public function markNeedsPractice(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);
        $student->exam_readiness_status = 'needs_practice';
        $student->save();

        session()->flash('message', 'Student marked as Needs Practice.');
    }

    public function removeStudent(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);
        $student->is_active = false;
        $student->save();

        session()->flash('message', 'Student removed from active list.');
    }

    public function requestExamSession(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);

        if ($student->exam_readiness_status !== 'teacher_approved') {
            session()->flash('message', 'Student must be marked ICDL Test Ready before requesting an exam session.');
            return;
        }

        $student->exam_request_status = 'requested';
        $student->exam_requested_at = now();
        $student->save();

        session()->flash('message', 'Exam request submitted for admin approval.');

        $this->notifyAdmins(
            'ICDL Exam Request',
            "Exam session requested for {$student->full_name} ({$student->student_id}).",
            [
                'student_profile_id' => $student->id,
                'school_id' => $student->school_id,
            ]
        );
    }

    public function submitExamPayment(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);

        $student->exam_payment_status = 'submitted';
        $student->exam_payment_submitted_at = now();
        $student->save();

        session()->flash('message', 'Exam payment submitted for verification.');

        $this->notifyAdmins(
            'Exam Payment Submitted',
            "Exam payment submitted for {$student->full_name} ({$student->student_id}).",
            [
                'student_profile_id' => $student->id,
                'school_id' => $student->school_id,
            ]
        );
    }

    public function exportSelectedCsv()
    {
        if (count($this->selected) === 0) {
            session()->flash('message', 'Select at least one student to export.');
            return null;
        }

        $students = $this->getSelectedStudents();

        if ($students->isEmpty()) {
            session()->flash('message', 'No students available for export.');
            return null;
        }

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student ID',
                'Full Name',
                'Class',
                'Gender',
                'Program',
                'School',
                'Login Username',
                'Initial Password',
            ]);

            foreach ($students as $student) {
                $user = $student->user;
                fputcsv($handle, [
                    $student->student_id,
                    $student->full_name,
                    $student->class_grade,
                    $student->gender,
                    $student->program_type,
                    $student->school?->name,
                    $user?->email ?: $student->student_id,
                    $user?->initial_password ?: '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function printSelectedCredentials()
    {
        if (count($this->selected) === 0) {
            session()->flash('message', 'Select at least one student to print.');
            return null;
        }

        $ids = implode(',', $this->selected);

        return redirect()->route('students.bulk-print-credentials', [
            'ids' => $ids,
        ]);
    }

    private function currentStudentIds(): array
    {
        $isIct = auth()->user()->isIctTeacher();

        return $this->applyStudentScope(StudentProfile::query())
            ->when($this->search, function($q) use ($isIct) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%')
                  ->when($isIct, function ($sub) {
                      $sub->orWhere('icdl_number', 'like', '%' . $this->search . '%');
                  }, function ($sub) {
                      $sub->orWhere('parent_guardian_contact', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->filterClass, function($q) {
                $q->where('class_grade', $this->filterClass);
            })
            ->when($isIct, function ($q) {
                $q->where('is_active', true);
            })
            ->when(!$isIct && $this->filterEnrollment === 'enrolled', function($q) {
                $q->whereHas('user.enrollments', function($sub) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                });
            })
            ->when(!$isIct && $this->filterEnrollment === 'not_enrolled', function($q) {
                $q->whereDoesntHave('user.enrollments', function($sub) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                });
            })
            ->when(!$isIct && $this->filterCategory, function($q) {
                $q->where('student_category', $this->filterCategory);
            })
            ->when($isIct && $this->filterReadiness, function ($q) {
                $q->where('exam_readiness_status', $this->filterReadiness);
            })
            ->when($isIct && $this->filterModuleId, function ($q) {
                $q->whereHas('user.enrollments', function ($sub) {
                    $sub->where('course_id', $this->filterModuleId);
                });
            })
            ->latest()
            ->paginate(15)
            ->pluck('id')
            ->all();
    }

    private function getSelectedStudents()
    {
        return $this->applyStudentScope(StudentProfile::query())
            ->with(['user', 'school'])
            ->whereIn('id', $this->selected)
            ->orderBy('full_name')
            ->get();
    }

    public function render()
    {
        $user = auth()->user();
        $isIct = $user->isIctTeacher();
        $availableIctCourses = $isIct ? $this->availableIctCourses() : collect();
        $ictCourseIds = $availableIctCourses->pluck('id')->all();

        $studentsQuery = StudentProfile::query()
            ->with([
                'user.enrollments' => function ($query) use ($isIct, $ictCourseIds) {
                    if ($isIct) {
                        if (empty($ictCourseIds)) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('course_id', $ictCourseIds);
                        }
                    }
                    $query->with('course');
                },
                'gadgets',
            ]);

        $students = $this->applyStudentScope($studentsQuery)
            ->when($this->search, function($q) use ($isIct) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%')
                  ->when($isIct, function ($sub) {
                      $sub->orWhere('icdl_number', 'like', '%' . $this->search . '%');
                  }, function ($sub) {
                      $sub->orWhere('parent_guardian_contact', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->filterClass, function($q) {
                $q->where('class_grade', $this->filterClass);
            })
            ->when($isIct, function ($q) {
                $q->where('is_active', true);
            })
            ->when(!$isIct && $this->filterEnrollment === 'enrolled', function($q) {
                $q->whereHas('user.enrollments', function($sub) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                });
            })
            ->when(!$isIct && $this->filterEnrollment === 'not_enrolled', function($q) {
                $q->whereDoesntHave('user.enrollments', function($sub) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                });
            })
            ->when(!$isIct && $this->filterCategory, function($q) {
                $q->where('student_category', $this->filterCategory);
            })
            ->when($isIct && $this->filterReadiness, function ($q) {
                $q->where('exam_readiness_status', $this->filterReadiness);
            })
            ->when($isIct && $this->filterModuleId, function ($q) {
                $q->whereHas('user.enrollments', function ($sub) {
                    $sub->where('course_id', $this->filterModuleId);
                });
            })
            ->latest()
            ->paginate(15);

        $classes = $this->applyStudentScope(StudentProfile::query())
            ->distinct()
            ->pluck('class_grade')
            ->filter();
        $courses = $isIct
            ? $availableIctCourses
            : Course::query()->orderBy('title')->get();

        return view('livewire.students.manage-students', [
            'students' => $students,
            'classes' => $classes,
            'courses' => $courses,
        ]);
    }

    private function availableIctCourses()
    {
        $schoolId = auth()->user()->ictSchoolId();

        if (!$schoolId) {
            return collect();
        }

        return Course::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)->where('is_active', true);
        })
            ->orderBy('title')
            ->get();
    }

    private function findScopedStudent(int $studentId): StudentProfile
    {
        $student = $this->applyStudentScope(StudentProfile::query())
            ->where('id', $studentId)
            ->firstOrFail();

        $this->authorize('update', $student);

        return $student;
    }

    private function applyStudentScope(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            if (!$schoolId) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('program_type', 'ict')
                ->where('school_id', $schoolId);
        }

        if ($user->isCodecampTrainer()) {
            return $query->where('program_type', 'codecamp');
        }

        return $query;
    }


    private function notifyAdmins(string $title, string $message, array $data = []): void
    {
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'supervisor']);
        })->get(['id']);

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'data' => $data,
                'is_read' => false,
            ]);
        }
    }
}
