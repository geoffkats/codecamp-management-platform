@php
    $size = $size ?? 12;
    $color = $color ?? '#1e3a5f';
    $icon = $icon ?? 'id';
@endphp
@switch($icon)
    @case('id')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="5" width="18" height="14" rx="2" stroke="{{ $color }}" stroke-width="1.8"/>
            <circle cx="9" cy="11" r="2" stroke="{{ $color }}" stroke-width="1.5"/>
            <path d="M5 16c0-2 1.8-3 4-3s4 1 4 3" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="14" y1="10" x2="18" y2="10" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="14" y1="13" x2="17" y2="13" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break
    @case('age')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="8" r="3.5" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" stroke="{{ $color }}" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break
    @case('club')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="6" width="16" height="12" rx="2" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M8 6V4.5A1.5 1.5 0 019.5 3h5A1.5 1.5 0 0116 4.5V6" stroke="{{ $color }}" stroke-width="1.8"/>
            <line x1="4" y1="11" x2="20" y2="11" stroke="{{ $color }}" stroke-width="1.5"/>
        </svg>
        @break
    @case('school')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 3L3 8.5V10h18V8.5L12 3z" stroke="{{ $color }}" stroke-width="1.8" stroke-linejoin="round"/>
            <rect x="5" y="10" width="14" height="10" stroke="{{ $color }}" stroke-width="1.8"/>
            <rect x="10" y="14" width="4" height="6" fill="{{ $color }}"/>
        </svg>
        @break
    @case('instructor')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="7" r="3" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M6 20v-1.5c0-2.5 2.7-4.5 6-4.5s6 2 6 4.5V20" stroke="{{ $color }}" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M16 8l2-1.5v4" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('institution')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="8" width="16" height="11" rx="1.5" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M12 4l8 4H4l8-4z" stroke="{{ $color }}" stroke-width="1.8" stroke-linejoin="round"/>
            <line x1="9" y1="13" x2="15" y2="13" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="9" y1="16" x2="13" y2="16" stroke="{{ $color }}" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break
    @case('operated')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="8" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M12 8v4l3 2" stroke="{{ $color }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('contact')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 5h14a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M5 7l7 5 7-5" stroke="{{ $color }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('chart')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <rect x="4" y="12" width="4" height="8" rx="1" fill="{{ $color }}"/>
            <rect x="10" y="8" width="4" height="12" rx="1" fill="{{ $color }}"/>
            <rect x="16" y="4" width="4" height="16" rx="1" fill="{{ $color }}"/>
        </svg>
        @break
    @case('book')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <path d="M5 4h8a3 3 0 013 3v14a2.5 2.5 0 00-2.5-2.5H5V4z" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M19 4h-8a3 3 0 00-3 3v14a2.5 2.5 0 012.5-2.5H19V4z" stroke="{{ $color }}" stroke-width="1.8"/>
        </svg>
        @break
    @case('briefcase')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <rect x="3" y="8" width="18" height="11" rx="2" stroke="{{ $color }}" stroke-width="1.8"/>
            <path d="M9 8V6a2 2 0 012-2h2a2 2 0 012 2v2" stroke="{{ $color }}" stroke-width="1.8"/>
            <line x1="3" y1="13" x2="21" y2="13" stroke="{{ $color }}" stroke-width="1.5"/>
        </svg>
        @break
    @case('comment')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <path d="M5 5h14a2 2 0 012 2v8a2 2 0 01-2 2H10l-4 3v-3H5a2 2 0 01-2-2V7a2 2 0 012-2z" stroke="{{ $color }}" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @break
    @case('star')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $color }}" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <path d="M12 2l2.9 6.5L22 9.5l-5 4.5 1.5 6.5L12 17.5 5.5 20.5 7 14 2 9.5l7.1-1L12 2z"/>
        </svg>
        @break
    @case('info')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <circle cx="12" cy="12" r="9" stroke="{{ $color }}" stroke-width="1.8"/>
            <line x1="12" y1="10" x2="12" y2="16" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="7.5" r="1" fill="{{ $color }}"/>
        </svg>
        @break
    @default
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="8" stroke="{{ $color }}" stroke-width="1.8"/>
        </svg>
@endswitch
