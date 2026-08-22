<?php

namespace App\Livewire\Students;

use App\Models\PeerKudo;
use App\Services\BadgeAwardingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GiveKudos extends Component
{
    public int $toUserId;
    public string $toUserName = '';
    public bool $alreadyGivenToday = false;
    public bool $justGiven = false;
    public int $totalKudos = 0;
    public string $message = '';

    public function mount(int $toUserId, string $toUserName = ''): void
    {
        $this->toUserId   = $toUserId;
        $this->toUserName = $toUserName;
        $this->refresh();
    }

    public function give(): void
    {
        if (! Auth::check() || Auth::id() === $this->toUserId) {
            return;
        }

        if ($this->alreadyGivenToday) {
            return;
        }

        PeerKudo::create([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $this->toUserId,
            'message'      => trim($this->message) ?: null,
            'given_on'     => today(),
        ]);

        // Check kudos badges for both sender and recipient
        $badgeService = app(BadgeAwardingService::class);
        $badgeService->checkKudosBadges(Auth::user());
        $badgeService->checkKudosBadges(\App\Models\User::find($this->toUserId));

        $this->justGiven = true;
        $this->message   = '';
        $this->refresh();
    }

    private function refresh(): void
    {
        if (Auth::check()) {
            $this->alreadyGivenToday = PeerKudo::hasGivenTodayTo(Auth::id(), $this->toUserId);
        }
        $this->totalKudos = PeerKudo::where('to_user_id', $this->toUserId)->count();
    }

    public function render()
    {
        return view('livewire.students.give-kudos');
    }
}
