<?php

namespace App\Http\Controllers\Lessons;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Services\LessonCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompletionController extends Controller
{
    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = Auth::user();
        $course = $lesson->module->course;

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $isInstructor = $course->instructor_id === $user->id
            || $user->hasRole('admin')
            || $user->hasRole('supervisor');

        $isIctTeacherWithAccess = false;
        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            $isIctTeacherWithAccess = $schoolId
                && $course->schools
                    ->where('id', (int) $schoolId)
                    ->where('pivot.is_active', true)
                    ->isNotEmpty();
        }

        if (! $enrollment && ! $isInstructor && ! $isIctTeacherWithAccess) {
            abort(403, 'You must be enrolled in this course to complete lessons.');
        }

        $service = app(LessonCompletionService::class);

        if (! $isInstructor && ! $isIctTeacherWithAccess) {
            $check = $service->canCompleteLesson($lesson, $user);
            if (! $check['can_complete']) {
                return back()->with('error', 'Submit the lesson assignment (and any required quizzes) before marking this lesson complete.');
            }
        }

        $result = $service->completeIfEligible($user, $lesson);

        if (! ($result['completed'] ?? false) && ($isInstructor || $isIctTeacherWithAccess)) {
            // Staff can force-complete for QA even if learner requirements are unmet.
            $result = $service->forceComplete($user, $lesson);
        }

        if (! ($result['completed'] ?? false)) {
            return back()->with('error', 'Submit the lesson assignment (and any required quizzes) before marking this lesson complete.');
        }

        return back()->with('message', 'Lesson marked as complete.');
    }
}
