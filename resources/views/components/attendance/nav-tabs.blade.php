@props(['context' => null])

@php
    use App\Support\ProgramScope;

    $current = request()->route()?->getName() ?? '';
    $user = auth()->user();

    $clubContext = $context === 'club'
        || ($context === null && (
            $current === 'attendance.club'
            || (ProgramScope::isClubFacilitatorContext() && in_array($current, ['attendance.club', 'attendance.code'], true))
        ));

    if ($clubContext && config('features.code_club', false) && $user?->hasCodeClubAccess()) {
        $tabs = [
            ['label' => 'Club Attendance', 'route' => 'attendance.club'],
            ['label' => 'Daily Code', 'route' => 'attendance.code'],
        ];
    } else {
        $tabs = [
            ['label' => 'Dashboard', 'route' => 'attendance.dashboard'],
            ['label' => 'Mark Attendance', 'route' => 'attendance.student'],
            ['label' => 'Records', 'route' => 'attendance.records'],
            ['label' => 'Daily Code', 'route' => 'attendance.code'],
        ];

        if (config('features.code_club', false) && $user?->hasCodeClubAccess()) {
            $tabs[] = ['label' => 'Club Attendance', 'route' => 'attendance.club'];
        }
    }
@endphp

<nav class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-4">
    @foreach($tabs as $tab)
        @if(Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}"
               class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors
                   {{ $current === $tab['route'] ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-gray-600' }}">
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</nav>
