<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseModule;
use Livewire\Component;

class ModuleForm extends Component
{
    public $courseId;
    public $module;
    public $title;
    public $description;
    public $overview;
    public $order_index = 0;
    public $estimated_duration_hours;
    public $is_active = true;

    public function mount($courseId, ?CourseModule $module = null)
    {
        $this->courseId = $courseId;
        $this->module = $module;

        if ($module) {
            $this->title = $module->title;
            $this->description = $module->description;
            $this->overview = $module->overview;
            $this->order_index = $module->order_index;
            $this->estimated_duration_hours = $module->estimated_duration_hours;
            $this->is_active = $module->is_active;
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'order_index' => 'required|integer|min:0',
        ]);

        $data = [
            'course_id' => $this->courseId,
            'title' => $this->title,
            'description' => $this->description,
            'overview' => $this->overview,
            'order_index' => $this->order_index,
            'estimated_duration_hours' => $this->estimated_duration_hours,
            'is_active' => $this->is_active,
        ];

        if ($this->module) {
            $this->module->update($data);
            session()->flash('message', 'Module updated successfully!');
        } else {
            CourseModule::create($data);
            session()->flash('message', 'Module created successfully!');
        }

        return redirect()->route('modules.index', ['course' => $this->courseId]);
    }

    public function render()
    {
        return view('livewire.courses.module-form', [
            'course' => Course::findOrFail($this->courseId),
        ]);
    }
}

