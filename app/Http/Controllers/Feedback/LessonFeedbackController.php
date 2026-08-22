<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeacherFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonFeedbackController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'lesson_id'    => 'required|integer',
            'course_id'    => 'required|exists:courses,id',
            'lesson_title' => 'nullable|string|max:255',
            'rating'       => 'nullable|integer|min:1|max:5',
            'note'         => 'required|string|max:1000',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $lessonTitle = $validated['lesson_title'] ?? 'Lesson';
        $feedbackText = 'Lesson ID: ' . $validated['lesson_id'] . ' | ' . $lessonTitle . "\n" . $validated['note'];

        TeacherFeedback::create([
            'student_id'   => Auth::id(),
            'teacher_id'   => $course->instructor_id,
            'course_id'    => $validated['course_id'],
            'category'     => 'general',
            'rating'       => $validated['rating'] ?? null,
            'feedback'     => $feedbackText,
            'is_anonymous' => false,
            'status'       => 'pending',
        ]);

        return redirect()->back()->with('feedback_submitted', true);
    }
}
