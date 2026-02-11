<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public $title = '';
    public $description = '';
    public $type = 'general';
    public $requirements = '';
    public $reward_points = 100;
    public $date = '';
    public $is_active = true;
    public $difficulty_level = 'Easy';
    public $category = '';
    public $course_id = null;
    public $courses = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'type' => 'required|string|in:general,coding,quiz,project,reading',
        'requirements' => 'nullable|string',
        'reward_points' => 'required|integer|min:1|max:1000',
        'date' => 'nullable|date',
        'is_active' => 'boolean',
        'difficulty_level' => 'required|in:Easy,Medium,Hard',
        'category' => 'nullable|string|max:50',
        'course_id' => 'nullable|exists:courses,id',
    ];

    public function mount()
    {
        Gate::authorize('manage_badges');
        
        // Set default date to today
        $this->date = now()->toDateString();

        $this->courses = Course::where('is_published', true)
            ->where('approval_status', 'approved')
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    public function save()
    {
        $this->validate();

        // Parse requirements as JSON array
        $requirementsArray = [];
        if ($this->requirements) {
            $lines = array_filter(array_map('trim', explode("\n", $this->requirements)));
            foreach ($lines as $line) {
                $requirementsArray[] = $line;
            }
        }

        DailyChallenge::create([
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'requirements' => $requirementsArray,
            'reward_points' => $this->reward_points,
            'date' => $this->date ?: null,
            'is_active' => $this->is_active,
            'difficulty_level' => $this->difficulty_level,
            'category' => $this->category ?: null,
            'course_id' => $this->course_id,
        ]);

        session()->flash('message', 'Daily challenge created successfully!');
        
        return $this->redirect(route('daily-challenges.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.daily-challenges.create');
    }
}
