<?php

namespace App\Livewire\Users\Concerns;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

trait ManagesStaffCourseAccess
{
    public array $selectedCourseIds = [];

    public string $courseSearch = '';

    protected function staffRoleIds(): array
    {
        return Role::query()
            ->whereIn('name', ['teacher', 'ict_teacher', 'codecamp_trainer', 'admin', 'supervisor'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function showsCourseAccessPicker(): bool
    {
        $staffRoleIds = $this->staffRoleIds();

        return count(array_intersect(
            array_map('intval', $this->selectedRoles),
            $staffRoleIds
        )) > 0;
    }

    protected function loadStaffCourseIds(User $user): void
    {
        $this->selectedCourseIds = $user->enrollments()
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function syncStaffCourseAccess(User $user): void
    {
        if (!$this->showsCourseAccessPicker()) {
            return;
        }

        $selected = collect($this->selectedCourseIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter()
            ->values();

        $existing = $user->enrollments()
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id);

        $toRemove = $existing->diff($selected);
        if ($toRemove->isNotEmpty()) {
            CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->whereIn('course_id', $toRemove->all())
                ->delete();
        }

        foreach ($selected->diff($existing) as $courseId) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $courseId],
                ['enrolled_at' => now(), 'progress_percentage' => 0]
            );
        }
    }

    protected function availableCoursesForPicker(): Collection
    {
        return Course::query()
            ->when(trim($this->courseSearch) !== '', function ($query) {
                $term = trim($this->courseSearch);
                $query->where('title', 'like', '%' . $term . '%');
            })
            ->orderBy('title')
            ->limit(50)
            ->get(['id', 'title', 'is_published']);
    }

}
