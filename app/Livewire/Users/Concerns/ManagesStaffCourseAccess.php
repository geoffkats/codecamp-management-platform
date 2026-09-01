<?php

namespace App\Livewire\Users\Concerns;

use App\Models\Course;
use App\Models\CourseCollaborator;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

    public function toggleCourse(int $courseId): void
    {
        $ids = collect($this->selectedCourseIds)->map(fn ($id) => (int) $id)->unique()->values();

        $this->selectedCourseIds = $ids->contains($courseId)
            ? $ids->reject(fn ($id) => $id === $courseId)->values()->all()
            : $ids->push($courseId)->all();
    }

    protected function loadStaffCourseIds(User $user): void
    {
        $fromEnrollments = $user->enrollments()->pluck('course_id');
        $fromCollaborators = CourseCollaborator::query()
            ->where('user_id', $user->id)
            ->pluck('course_id');

        $this->selectedCourseIds = $fromEnrollments
            ->merge($fromCollaborators)
            ->map(fn ($id) => (int) $id)
            ->unique()
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

        $this->syncStaffEnrollments($user, $selected);
        $this->syncStaffCollaborators($user, $selected);
    }

    protected function syncStaffEnrollments(User $user, Collection $selected): void
    {
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

    protected function syncStaffCollaborators(User $user, Collection $selected): void
    {
        $existing = CourseCollaborator::query()
            ->where('user_id', $user->id)
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id);

        $toRemove = $existing->diff($selected);
        if ($toRemove->isNotEmpty()) {
            CourseCollaborator::query()
                ->where('user_id', $user->id)
                ->whereIn('course_id', $toRemove->all())
                ->delete();
        }

        $invitedBy = Auth::id();

        foreach ($selected->diff($existing) as $courseId) {
            $isOwner = Course::query()
                ->where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->exists();

            if ($isOwner) {
                continue;
            }

            CourseCollaborator::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $courseId],
                [
                    'role' => 'editor',
                    'invited_at' => now(),
                    'invited_by' => $invitedBy,
                ]
            );
        }
    }

    protected function availableCoursesForPicker(): Collection
    {
        $selected = collect($this->selectedCourseIds)->map(fn ($id) => (int) $id)->filter();

        $matches = Course::query()
            ->when(trim($this->courseSearch) !== '', function ($query) {
                $term = trim($this->courseSearch);
                $query->where('title', 'like', '%' . $term . '%');
            })
            ->orderBy('title')
            ->limit(50)
            ->get(['id', 'title', 'is_published']);

        $missingIds = $selected->diff($matches->pluck('id')->map(fn ($id) => (int) $id));
        if ($missingIds->isEmpty()) {
            return $matches;
        }

        $selectedCourses = Course::query()
            ->whereIn('id', $missingIds->all())
            ->get(['id', 'title', 'is_published']);

        return $selectedCourses->concat($matches)->unique('id')->sortBy('title')->values();
    }

}
