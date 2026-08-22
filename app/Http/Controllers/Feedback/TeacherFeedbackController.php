<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeacherFeedback;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherFeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user?->studentProfile) {
            abort(403, 'Only students can submit teacher feedback.');
        }

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|integer',
            'lesson_title' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'note' => 'required|string|min:3|max:1000',
            'category' => 'nullable|in:teaching_quality,communication,support,professionalism,general',
            'source' => 'nullable|string|max:50',
        ]);

        $course = Course::with('instructor')->findOrFail($data['course_id']);
        $teacher = $course->instructor;

        if (!$teacher) {
            return back()->with('error', 'Selected course does not have a valid teacher.');
        }

        $feedbackNote = $data['note'];
        if (!empty($data['lesson_id'])) {
            $feedbackNote = "Lesson ID: {$data['lesson_id']}\n" . $feedbackNote;
        }
        if (!empty($data['lesson_title'])) {
            $feedbackNote = "Lesson: {$data['lesson_title']}\n" . $feedbackNote;
        }

        TeacherFeedback::create([
            'student_id' => $user->id,
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'category' => $data['category'] ?? 'general',
            'rating' => $data['rating'] ?? null,
            'feedback' => $feedbackNote,
            'is_anonymous' => false,
            'status' => 'pending',
        ]);

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Teacher Feedback',
                'message' => $user->name . ' submitted feedback about ' . $teacher->name,
                'type' => 'info',
            ]);
        }

        return back()
            ->with('message', 'Thank you! Your feedback has been submitted.')
            ->with('feedback_submitted', true);
    }
}
