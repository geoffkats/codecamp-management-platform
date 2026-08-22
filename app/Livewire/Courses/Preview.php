<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Preview extends Component
{
    public Course $course;

    public function mount(Course $course): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($this->canPreviewCourse($user, $course)) {
            $this->course = $course->load([
                'instructor',
                'modules' => function ($query) {
                    $query->with(['lessons' => function ($lessons) {
                        $lessons->orderBy('order_index');
                    }])->orderBy('order_index');
                },
            ]);

            return;
        }

        $enrollment = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            $this->redirect(route('courses.learn', $course), navigate: true);

            return;
        }

        abort(403);
    }

    protected function canPreviewCourse($user, Course $course): bool
    {
        if ($course->instructor_id === $user->id) {
            return true;
        }

        if ($user->hasAnyRole(['admin', 'supervisor', 'teacher', 'codecamp_trainer'])) {
            return true;
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            return $schoolId
                && $course->schools()
                    ->where('schools.id', (int) $schoolId)
                    ->where('school_courses.is_active', true)
                    ->exists();
        }

        return false;
    }

    public function render()
    {
        $modules = $this->course->modules;
        $totalLessons = $modules->sum(fn ($module) => $module->lessons->count());
        $firstLesson = $modules->flatMap(fn ($module) => $module->lessons)->first();

        return view('livewire.courses.preview', [
            'modules' => $modules,
            'totalLessons' => $totalLessons,
            'firstLesson' => $firstLesson,
        ]);
    }
}
