<x-layouts.app>
    @php
        $user = auth()->user();
    @endphp

    @if($user->isAdmin())
        <livewire:dashboard.admin-dashboard />
    @elseif($user->isTeacher())
        <livewire:dashboard.instructor-dashboard />
    @elseif($user->isSupervisor())
        <livewire:dashboard.supervisor-dashboard />
    @else
        <livewire:dashboard.student-dashboard />
    @endif
</x-layouts.app>

