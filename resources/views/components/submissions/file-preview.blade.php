@props(['path', 'name' => null, 'questionText' => null])

@php
    $raw = $path;
    $path = is_array($raw) ? ($raw['path'] ?? '') : (string) $raw;
    $name = $name
        ?? (is_array($raw) ? ($raw['name'] ?? null) : null)
        ?? basename($path);
    $downloadUrl = $path !== '' ? \App\Support\SubmissionFile::downloadUrl($path, $name) : '';
    $previewUrl = $path !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : '';
    $ext = strtolower(pathinfo($name !== '' ? $name : $path, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
@endphp

@if($path !== '')
    <div class="rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50 overflow-hidden">
        @if($questionText)
            <p class="px-3 pt-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $questionText }}</p>
        @endif

        @if($isImage)
            <a href="{{ $previewUrl }}" target="_blank" class="block p-3">
                <img src="{{ $previewUrl }}" alt="{{ $name }}"
                     class="max-h-64 w-full rounded-lg object-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <p class="mt-2 text-xs text-blue-600 dark:text-blue-400 truncate">{{ $name }} · open full size</p>
            </a>
        @else
            <a href="{{ $downloadUrl }}"
               class="flex items-center gap-3 p-4 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Download</p>
                </div>
            </a>
        @endif
    </div>
@endif
