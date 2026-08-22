@props([
    'accept' => '.csv,.txt,.xlsx',
    'maxLabel' => '5 MB',
    'file' => null,
])

@php
    $inputId = 'bulk-import-file-' . uniqid();
    $fileName = $file && method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : null;
@endphp

<div x-data="{ expanded: {{ $file ? 'true' : 'false' }} }" class="relative space-y-2">
    {{-- Compact row (always visible) --}}
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-amber-300/70 bg-white px-3 py-2.5 dark:border-amber-700 dark:bg-zinc-900/60">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        </div>

        <div class="min-w-0 flex-1">
            @if($fileName)
                <p class="truncate text-sm font-semibold text-emerald-800 dark:text-emerald-200">{{ $fileName }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400">Ready to import</p>
            @else
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">No file chosen</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">.xlsx or .csv · max {{ $maxLabel }}</p>
            @endif
        </div>

        <label for="{{ $inputId }}"
            class="inline-flex shrink-0 cursor-pointer items-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700">
            Browse
        </label>

        <button type="button" @click="expanded = !expanded"
            class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-amber-300 px-2.5 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-200 dark:hover:bg-amber-950/40">
            <span x-text="expanded ? 'Collapse' : 'Expand'"></span>
            <svg :class="expanded ? 'rotate-180' : ''" class="h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <input
            id="{{ $inputId }}"
            type="file"
            accept="{{ $accept }}"
            {{ $attributes->whereStartsWith('wire:model') }}
            class="sr-only"
        />
    </div>

    {{-- Expanded drop zone --}}
    <div x-show="expanded" x-collapse class="relative">
        <label
            for="{{ $inputId }}"
            class="group flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-amber-400/70 bg-amber-50/30 px-4 py-6 text-center transition hover:border-amber-500 hover:bg-amber-50/60 dark:border-amber-600/50 dark:bg-amber-950/10 dark:hover:bg-amber-950/25"
        >
            <p class="text-sm font-bold text-slate-900 dark:text-white">Drop file here or click to browse</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Excel .xlsx or CSV .csv</p>
            <span class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white group-hover:bg-amber-700">
                Choose file
            </span>
        </label>
    </div>

    <div wire:loading.flex wire:target="importCsv" class="absolute inset-0 z-10 hidden items-center justify-center rounded-xl bg-white/90 dark:bg-zinc-900/90">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 animate-spin text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Uploading…</p>
        </div>
    </div>
</div>
