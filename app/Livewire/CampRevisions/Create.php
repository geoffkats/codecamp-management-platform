<?php

namespace App\Livewire\CampRevisions;

use App\Models\CampContentRevision;
use App\Models\CodeCamp;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public ?int $campId = null;

    public ?int $courseId = null;

    public string $title = '';

    public string $notes = '';

    public array $uploads = [];

    public array $picked = [];

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || $user->isAdmin(), 403);

        $this->campId = CodeCamp::whereIn('status', ['active', 'completed'])->orderByDesc('end_date')->value('id');
    }

    public function updatedPicked(): void
    {
        $this->validate(['picked.*' => CampContentRevision::uploadRules()], CampContentRevision::uploadMessages('picked'));

        $this->uploads = array_slice([...$this->uploads, ...$this->picked], 0, 15);
        $this->picked = [];
    }

    public function removeUpload(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function save()
    {
        $this->validate([
            'campId' => 'required|exists:code_camps,id',
            'courseId' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:150',
            'notes' => 'required|string|min:20|max:5000',
            'uploads' => 'required|array|min:1|max:15',
            'uploads.*' => CampContentRevision::uploadRules(),
        ], [
            'uploads.required' => 'Attach at least one file with your revised content.',
            'notes.min' => 'Tell your supervisor a little more about what you changed and why.',
            ...CampContentRevision::uploadMessages('uploads'),
        ]);

        $user = Auth::user();

        if ($this->courseId) {
            abort_unless(Course::accessibleBy($user)->whereKey($this->courseId)->exists(), 403);
        }

        $revision = CampContentRevision::create([
            'camp_id' => $this->campId,
            'course_id' => $this->courseId,
            'trainer_id' => $user->id,
            'title' => $this->title,
            'notes' => $this->notes,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $revision->storeUploads($this->uploads);

        $revision->notifyReviewers(
            'Revised content to review',
            $user->name.' sent revised content "'.$revision->title.'" from '.($revision->camp?->name ?? 'camp').'.'
        );

        session()->flash('message', 'Sent to your supervisor. You will be notified when they review it or comment.');

        return $this->redirectRoute('camp-revisions.show', $revision, navigate: true);
    }

    public function render()
    {
        return view('livewire.camp-revisions.create', [
            'camps' => CodeCamp::whereIn('status', ['active', 'completed', 'upcoming'])->orderByDesc('start_date')->get(['id', 'name', 'status']),
            'courses' => Course::accessibleBy(Auth::user())->orderBy('title')->get(['id', 'title']),
        ]);
    }
}
