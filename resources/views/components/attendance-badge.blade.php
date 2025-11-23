@props([
    'checkedIn' => false,
    'checkInTime' => null,
    'size' => 'md',
])

@php
$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base',
];
@endphp

@if($checkedIn)
    <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full font-semibold {{ $sizes[$size] }}">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>Checked In</span>
        @if($checkInTime)
            <span class="text-xs opacity-75">{{ $checkInTime->format('g:i A') }}</span>
        @endif
    </span>
@else
    <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full font-semibold {{ $sizes[$size] }}">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>Not Checked In</span>
    </span>
@endif
