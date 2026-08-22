@props([
    'questionId',
    'allowedTypes' => 'html,htm,css,pdf,doc,docx,txt,jpg,jpeg,png,gif,zip',
    'maxFiles' => 1,
    'maxSize' => 10,
])

@php
    $accept = collect(explode(',', (string) $allowedTypes))
        ->map(fn ($type) => '.' . ltrim(trim($type), '.'))
        ->filter()
        ->implode(',');
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="rounded-xl border-2 border-dashed border-orange-300 dark:border-orange-600 bg-orange-50/60 dark:bg-orange-950/20 p-6">
        <label for="file-upload-{{ $questionId }}" class="block cursor-pointer">
            <div class="text-center space-y-3 pointer-events-none">
                <div class="mx-auto w-14 h-14 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
                    <svg class="w-7 h-7 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-bold text-gray-900 dark:text-white">Click to upload your file</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $maxFiles > 1 ? "Up to {$maxFiles} files" : 'One file' }} · max {{ $maxSize }}MB each
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ strtoupper(str_replace(',', ', ', $allowedTypes)) }}</p>
                </div>
            </div>
            <input
                id="file-upload-{{ $questionId }}"
                type="file"
                class="sr-only"
                wire:model="tempFiles.{{ $questionId }}"
                @if((int) $maxFiles > 1) multiple @endif
                accept="{{ $accept }}"
            />
        </label>

        <div wire:loading wire:target="tempFiles.{{ $questionId }}" class="mt-4 text-center text-sm font-medium text-orange-700 dark:text-orange-300">
            Uploading file…
        </div>
    </div>

    <flux:error name="tempFiles.{{ $questionId }}" />
    <flux:error name="tempFiles.{{ $questionId }}.*" />
</div>
