<?php

namespace App\Livewire\Quizzes;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Quiz $quiz;
    public $title;
    public $description;
    public $instructions;
    public $time_limit;
    public $max_attempts = 3;
    public $passing_score = 70.00;
    public $is_randomized = false;
    public $is_published = false;
    public $show_correct_answers = true;
    public $allow_review = true;
    public $lesson_id;

    public $questions = [];
    public $showQuestionForm = false;
    public $editingQuestionId = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'instructions' => 'nullable|string|max:2000',
        'time_limit' => 'nullable|integer|min:1',
        'max_attempts' => 'required|integer|min:1|max:10',
        'passing_score' => 'required|numeric|min:0|max:100',
        'is_randomized' => 'boolean',
        'is_published' => 'boolean',
        'show_correct_answers' => 'boolean',
        'allow_review' => 'boolean',
        'lesson_id' => 'required|exists:lessons,id',
    ];

    public function mount(Quiz $quiz)
    {
        // Authorization check
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Check if user can edit this quiz
        if ($user->isTeacher()) {
            // Teachers can only edit quizzes from their own courses
            $course = $quiz->lesson->course ?? null;
            if (!$course || $course->instructor_id !== $user->id) {
                abort(403, 'You can only edit quizzes from your own courses.');
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'You do not have permission to edit quizzes.');
        }
        
        $this->quiz = $quiz->load('lesson.course');
        $this->title = $quiz->title;
        $this->description = $quiz->description;
        $this->instructions = $quiz->instructions;
        $this->time_limit = $quiz->time_limit;
        $this->max_attempts = $quiz->max_attempts ?? 3;
        $this->passing_score = $quiz->passing_score ?? 70.00;
        $this->is_randomized = $quiz->is_randomized ?? false;
        $this->is_published = $quiz->is_published ?? false;
        $this->show_correct_answers = $quiz->show_correct_answers ?? true;
        $this->allow_review = $quiz->allow_review ?? true;
        $this->lesson_id = $quiz->lesson_id;
        
        $this->loadQuestions();
    }

    public function loadQuestions()
    {
        $this->questions = $this->quiz->questions()->with('options')->orderBy('order')->get()->toArray();
    }

    public function save()
    {
        $this->validate();

        $this->quiz->update([
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'time_limit' => $this->time_limit,
            'max_attempts' => $this->max_attempts,
            'passing_score' => $this->passing_score,
            'is_randomized' => $this->is_randomized,
            'is_published' => $this->is_published,
            'show_correct_answers' => $this->show_correct_answers,
            'allow_review' => $this->allow_review,
            'lesson_id' => $this->lesson_id,
        ]);

        session()->flash('message', 'Quiz updated successfully!');
        return $this->redirect(route('quizzes.show', $this->quiz), navigate: true);
    }

    public function deleteQuestion($questionId)
    {
        Question::findOrFail($questionId)->delete();
        $this->loadQuestions();
        session()->flash('message', 'Question deleted successfully!');
    }

    public function render()
    {
        $lessons = Lesson::with('course')->get();
        $totalPoints = collect($this->questions)->sum('points');
        
        return view('livewire.quizzes.edit', [
            'lessons' => $lessons,
            'totalPoints' => $totalPoints,
        ]);
    }
}
