<?php

namespace App\Livewire\Gamification;

use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Component;

class BadgeForm extends Component
{
    public $badge;
    public $name;
    public $description;
    public $icon;
    public $color = '#3B82F6';
    public $criteria = [];
    public $points_reward = 0;
    public $is_active = true;

    public function mount(?Badge $badge = null)
    {
        $this->badge = $badge;
        if ($badge) {
            $this->name = $badge->name;
            $this->description = $badge->description;
            $this->icon = $badge->icon;
            $this->color = $badge->color;
            $this->criteria = $badge->criteria ?? [];
            $this->points_reward = $badge->points_reward;
            $this->is_active = $badge->is_active;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'color' => 'required|string',
            'criteria' => 'required|array',
            'points_reward' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'criteria' => $this->criteria,
            'points_reward' => $this->points_reward,
            'is_active' => $this->is_active,
        ];

        if ($this->badge) {
            $this->badge->update($data);
            session()->flash('message', 'Badge updated successfully!');
        } else {
            Badge::create($data);
            session()->flash('message', 'Badge created successfully!');
        }

        return redirect()->route('badges.index');
    }

    public function render()
    {
        return view('livewire.gamification.badge-form');
    }
}

