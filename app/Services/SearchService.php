<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Badge;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Global search across courses and lessons
     */
    public function globalSearch(string $query, array $filters = []): array
    {
        $results = [
            'courses' => $this->searchCourses($query, $filters),
            'lessons' => $this->searchLessons($query, $filters),
            'assessments' => $this->searchAssessments($query, $filters),
        ];

        return $results;
    }

    /**
     * Search courses
     */
    public function searchCourses(string $query, array $filters = []): Collection
    {
        $searchQuery = Course::query()
            ->where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('category', 'like', '%' . $query . '%');
            });

        // Apply filters
        if (isset($filters['difficulty'])) {
            $searchQuery->where('difficulty', $filters['difficulty']);
        }

        if (isset($filters['category'])) {
            $searchQuery->where('category', $filters['category']);
        }

        if (isset($filters['status']) && $filters['status'] === 'published') {
            $searchQuery->where('is_published', true);
        }

        return $searchQuery->limit(20)->get();
    }

    /**
     * Search lessons
     */
    public function searchLessons(string $query, array $filters = []): Collection
    {
        $searchQuery = Lesson::query()
            ->whereHas('module.course', function ($q) {
                $q->where('is_published', true);
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%');
            });

        if (isset($filters['course_id'])) {
            $searchQuery->whereHas('module', function ($q) use ($filters) {
                $q->where('course_id', $filters['course_id']);
            });
        }

        return $searchQuery->with('module.course')->limit(20)->get();
    }

    /**
     * Search assessments
     */
    public function searchAssessments(string $query, array $filters = []): Collection
    {
        $searchQuery = Assessment::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            });

        if (isset($filters['type'])) {
            $searchQuery->where('assessment_type', $filters['type']);
        }

        if (isset($filters['course_id'])) {
            $searchQuery->where('course_id', $filters['course_id']);
        }

        return $searchQuery->with('course')->limit(20)->get();
    }

    /**
     * Advanced filters for assessments
     */
    public function filterAssessments(array $filters): Collection
    {
        $query = Assessment::query();

        if (isset($filters['type'])) {
            $query->where('assessment_type', $filters['type']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['is_required'])) {
            $query->where('is_required', $filters['is_required']);
        }

        if (isset($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with('course')->get();
    }

    /**
     * Search badges by criteria
     */
    public function searchBadges(string $query, array $filters = []): Collection
    {
        $searchQuery = Badge::query()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            });

        if (isset($filters['type'])) {
            $searchQuery->whereJsonContains('criteria->type', $filters['type']);
        }

        if (isset($filters['color'])) {
            $searchQuery->where('color', $filters['color']);
        }

        return $searchQuery->get();
    }
}







