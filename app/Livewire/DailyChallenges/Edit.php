<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public DailyChallenge $dailyChallenge;

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
    public bool $isAdmin = false;

    protected $rules = [
        'title'           => 'required|string|max:255',
        'description'     => 'required|string',
        'type'            => 'required|string|max:50',
        'requirements'    => 'nullable|string',
        'reward_points'   => 'required|integer|min:1|max:1000',
        'date'            => 'nullable|date',
        'is_active'       => 'boolean',
        'difficulty_level'=> 'required|in:easy,medium,hard',
        'category'        => 'nullable|string|max:50',
        'course_id'       => 'nullable|exists:courses,id',
    ];

    public function mount(DailyChallenge $dailyChallenge)
    {
        Gate::authorize('manage_challenges');

        $user = Auth::user();
        $this->isAdmin = $user->isAdmin() || $user->isSupervisor();

        // Non-admins can only edit challenges they created
        if (! $dailyChallenge->canBeEditedBy($user)) {
            abort(403, 'You can only edit challenges you created.');
        }

        $this->dailyChallenge  = $dailyChallenge;
        $this->title           = $dailyChallenge->title;
        $this->description     = $dailyChallenge->description;
        $this->type            = $dailyChallenge->type;
        $this->reward_points   = $dailyChallenge->reward_points ?? 100;
        $this->date            = $dailyChallenge->date ? $dailyChallenge->date->format('Y-m-d') : '';
        $this->is_active       = $dailyChallenge->is_active;
        $this->difficulty_level= strtolower($dailyChallenge->difficulty_level ?? 'medium');
        $this->category        = $dailyChallenge->category ?? '';
        $this->course_id       = $dailyChallenge->course_id;

        $this->requirements = is_array($dailyChallenge->requirements)
            ? implode("\n", $dailyChallenge->requirements)
            : ($dailyChallenge->requirements ?? '');

        // Scope courses: admins see all; teachers see only their own
        $query = Course::where('is_published', true)
            ->where('approval_status', 'approved')
            ->orderBy('title');

        if (! $this->isAdmin) {
            $query->where('instructor_id', $user->id);
        }

        $this->courses = $query->get(['id', 'title']);
    }

    public function update()
    {
        Gate::authorize('manage_challenges');

        if (! $this->dailyChallenge->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $this->validate();

        $requirementsArray = [];
        if ($this->requirements) {
            $requirementsArray = array_values(
                array_filter(array_map('trim', explode("\n", $this->requirements)))
            );
        }

        $this->dailyChallenge->update([
            'title'           => $this->title,
            'description'     => $this->description,
            'type'            => $this->type,
            'requirements'    => $requirementsArray,
            'reward_points'   => $this->reward_points,
            'date'            => $this->date ?: null,
            'is_active'       => $this->is_active,
            'difficulty_level'=> $this->difficulty_level,
            'category'        => $this->category ?: null,
            'course_id'       => $this->course_id,
        ]);

        session()->flash('message', 'Daily challenge updated successfully!');

        return $this->redirect(route('daily-challenges.index'), navigate: true);
    }

    public function delete()
    {
        Gate::authorize('manage_challenges');

        if (! $this->dailyChallenge->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $this->dailyChallenge->delete();

        session()->flash('message', 'Daily challenge deleted successfully!');

        return $this->redirect(route('daily-challenges.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.daily-challenges.edit');
    }
}
