<?php

namespace App\Livewire\Badges;

use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Badge $badge;
    public $name;
    public $description;
    public $icon = '🏆';
    public $color = 'yellow';
    public $points_reward = 100;
    public $is_active = true;
    public $criteria = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'icon' => 'nullable|string|max:10',
        'color' => 'required|string|in:blue,green,yellow,red,purple,orange,pink,indigo',
        'points_reward' => 'required|integer|min:0|max:10000',
        'is_active' => 'boolean',
        'criteria' => 'nullable|array',
    ];

    public function mount(Badge $badge)
    {
        $this->badge = $badge;
        $this->name = $this->badge->name;
        $this->description = $this->badge->description;
        $this->icon = $this->badge->icon ?? '🏆';
        $this->color = $this->badge->color ?? 'yellow';
        $this->points_reward = $this->badge->points_reward ?? 100;
        $this->is_active = $this->badge->is_active ?? true;
        $this->criteria = $this->badge->criteria ?? [];
    }

    public function save()
    {
        $this->validate();

        $this->badge->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'points_reward' => $this->points_reward,
            'is_active' => $this->is_active,
            'criteria' => $this->criteria,
        ]);

        session()->flash('message', 'Badge updated successfully!');
        return $this->redirect(route('badges.index'), navigate: true);
    }

    public function addCriteria()
    {
        $this->criteria[] = [
            'type' => 'complete_course',
            'value' => null,
        ];
    }

    public function removeCriteria($index)
    {
        unset($this->criteria[$index]);
        $this->criteria = array_values($this->criteria);
    }

    public function render()
    {
        return view('livewire.badges.edit');
    }
}
