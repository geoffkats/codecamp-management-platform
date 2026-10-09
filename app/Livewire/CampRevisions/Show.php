<?php

namespace App\Livewire\CampRevisions;

use App\Models\CampContentRevision;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public CampContentRevision $revision;

    public string $reviewNote = '';

    public array $picked = [];

    public function mount(CampContentRevision $revision): void
    {
        abort_unless($revision->canComment(Auth::user()), 403);

        $this->revision = $revision;
    }

    private function isOwner(): bool
    {
        return (int) $this->revision->trainer_id === (int) Auth::id();
    }

    public function approve(): void
    {
        $this->revision->approve(Auth::user(), trim($this->reviewNote) ?: null);
        $this->reset('reviewNote');
        $this->dispatch('comments-updated');
        session()->flash('message', 'Approved. The trainer has been told.');
    }

    public function requestChanges(): void
    {
        $this->validate(['reviewNote' => 'required|string|min:5|max:2000'], [
            'reviewNote.required' => 'Tell the trainer what to change.',
        ]);

        $this->revision->requestChanges(Auth::user(), trim($this->reviewNote));
        $this->reset('reviewNote');
        $this->dispatch('comments-updated');
        session()->flash('message', 'Sent back to the trainer with your note.');
    }

    public function updatedPicked(): void
    {
        abort_unless($this->isOwner() && $this->revision->status !== 'approved', 403);

        $this->validate(['picked.*' => CampContentRevision::uploadRules()], CampContentRevision::uploadMessages('picked'));
        abort_if($this->revision->files()->count() + count($this->picked) > 30, 422, 'Too many files.');

        $this->revision->storeUploads($this->picked);
        $this->picked = [];
    }

    public function deleteFile(int $fileId): void
    {
        abort_unless($this->isOwner() && $this->revision->status !== 'approved', 403);

        $this->revision->files()->whereKey($fileId)->firstOrFail()->delete();
    }

    public function resubmit(): void
    {
        abort_unless($this->isOwner() && $this->revision->status === 'changes_requested', 403);

        $this->revision->update(['status' => 'pending', 'submitted_at' => now()]);
        $this->revision->notifyReviewers(
            'Revised content updated',
            Auth::user()->name.' updated "'.$this->revision->title.'" and sent it back for review.'
        );

        session()->flash('message', 'Sent back to your supervisor for another look.');
    }

    public function render()
    {
        $this->revision->load(['camp', 'course:id,title', 'trainer', 'reviewer', 'files']);

        return view('livewire.camp-revisions.show', [
            'isReviewer' => CampContentRevision::canReview(Auth::user()),
            'isOwner' => $this->isOwner(),
        ]);
    }
}
