<?php

namespace App\Livewire\Gamification;

use App\Models\DailyChallenge;
use Livewire\Component;

class DailyChallengeForm extends Component
{
    public $challenge;
    public $title;
    public $description;
    public $type = 'lesson_completion';
    public $requirements = [];
    public $reward_points = 100;
    public $date;
    public $is_active = true;
    public $difficulty_level = 'medium';
    public $category;

    public function mount(?DailyChallenge $challenge = null)
    {
        $this->challenge = $challenge;
        if ($challenge) {
            $this->title = $challenge->title;
            $this->description = $challenge->description;
            $this->type = $challenge->type;
            $this->requirements = $challenge->requirements ?? [];
            $this->reward_points = $challenge->reward_points;
            $this->date = $challenge->date;
            $this->is_active = $challenge->is_active;
            $this->difficulty_level = $challenge->difficulty_level;
            $this->category = $challenge->category;
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:lesson_completion,quiz_score,study_time,course_progress,forum_participation,assignment_submission',
            'requirements' => 'required|array',
            'reward_points' => 'required|integer|min:0',
            'date' => 'required|date',
            'difficulty_level' => 'required|in:easy,medium,hard',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'requirements' => $this->requirements,
            'reward_points' => $this->reward_points,
            'date' => $this->date,
            'is_active' => $this->is_active,
            'difficulty_level' => $this->difficulty_level,
            'category' => $this->category,
        ];

        if ($this->challenge) {
            $this->challenge->update($data);
            session()->flash('message', 'Daily challenge updated successfully!');
        } else {
            DailyChallenge::create($data);
            session()->flash('message', 'Daily challenge created successfully!');
        }

        return redirect()->route('daily-challenges.index');
    }

    public function render()
    {
        return view('livewire.gamification.daily-challenge-form');
    }
}

