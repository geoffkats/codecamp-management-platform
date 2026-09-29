@props(['projectId', 'autostart' => false, 'title' => 'Scratch Project'])

@php
    $projectId = preg_replace('/\D+/', '', (string) $projectId);
    $isPlaceholder = $projectId === '' || in_array($projectId, ['1234567890', '123456789', '12345'], true);
    $scratchUrl = 'https://scratch.mit.edu/projects/'.$projectId;
    $embedUrl = $scratchUrl.'/embed'.($autostart ? '?autostart=true' : '');
    $thumbUrl = 'https://cdn2.scratch.mit.edu/get_image/project/'.$projectId.'_480x360.png';
@endphp

@if($isPlaceholder)
    <div class="rounded-xl border-2 border-orange-300 bg-orange-50 p-6 dark:border-orange-700 dark:bg-orange-900/20">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Scratch Project Not Configured</h3>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
            Add a real Scratch project ID in the curriculum builder (from scratch.mit.edu/projects/<strong>ID</strong>).
        </p>
    </div>
@else
    {{-- Simple click-to-play: do not load Scratch assets until the student clicks. --}}
    <div
        class="overflow-hidden rounded-xl border-2 border-orange-400 bg-white shadow-lg dark:bg-gray-800"
        x-data="{ playing: {{ $autostart ? 'true' : 'false' }} }"
    >
        <div class="flex items-center justify-between bg-orange-500 px-4 py-3 text-white">
            <span class="truncate font-bold">{{ $title }}</span>
            <a href="{{ $scratchUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="shrink-0 text-sm font-semibold underline-offset-2 hover:underline">
                Open in Scratch
            </a>
        </div>

        <div class="relative aspect-[4/3] bg-gray-900">
            <template x-if="!playing">
                <button
                    type="button"
                    class="group absolute inset-0 flex w-full flex-col items-center justify-center gap-4 bg-gray-900"
                    @click="playing = true"
                    aria-label="Play Scratch project"
                >
                    <img
                        src="{{ $thumbUrl }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover opacity-60"
                        loading="lazy"
                        onerror="this.style.display='none'"
                    >
                    <span class="relative z-10 flex h-16 w-16 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition group-hover:scale-105 group-hover:bg-green-600">
                        <svg class="ml-1 h-8 w-8" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="relative z-10 text-sm font-semibold text-white">Click to play</span>
                </button>
            </template>

            <template x-if="playing">
                <iframe
                    src="{{ $embedUrl }}"
                    class="absolute inset-0 h-full w-full border-0"
                    allowtransparency="true"
                    allowfullscreen
                    scrolling="no"
                    title="{{ $title }}"
                ></iframe>
            </template>
        </div>
    </div>
@endif
