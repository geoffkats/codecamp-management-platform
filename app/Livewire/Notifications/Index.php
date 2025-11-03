<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filter = 'all'; // 'all', 'unread', 'read'
    public $type = 'all';

    public function mount()
    {
        // Mark as read when viewing
        $this->markAllAsRead();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->dispatch('notification-read');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->dispatch('notifications-read');
    }

    public function delete($notificationId)
    {
        Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId)
            ->delete();

        $this->dispatch('notification-deleted');
    }

    public function filterByStatus($status)
    {
        $this->filter = $status;
        $this->resetPage();
    }

    public function render()
    {
        $query = Notification::where('user_id', Auth::id());

        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filter === 'read') {
            $query->where('is_read', true);
        }

        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        $notifications = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => Notification::where('user_id', Auth::id())->count(),
            'unread' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
            'read' => Notification::where('user_id', Auth::id())->where('is_read', true)->count(),
        ];

        return view('livewire.notifications.index', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }
}
