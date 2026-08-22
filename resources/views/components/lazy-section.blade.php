@props([
    'rootMargin' => '200px 0px',
    'placeholderTitle' => 'Loading...',
    'placeholderBody' => 'This will appear as you scroll.',
    'placeholderTone' => 'emerald',
])

@php
    $tone = in_array($placeholderTone, ['blue', 'emerald', 'orange', 'gray', 'purple', 'pink', 'teal'])
        ? $placeholderTone
        : 'emerald';
@endphp

<div {{ $attributes }} x-data="{ ready: false }"
    x-init="initLazySection($el, () => { ready = true; }, '{{ $rootMargin }}')">
    <div x-show="!ready" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-{{ $tone }}-100 dark:bg-{{ $tone }}-900/40 flex items-center justify-center">
                <svg class="w-5 h-5 text-{{ $tone }}-600 dark:text-{{ $tone }}-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $placeholderTitle }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $placeholderBody }}</p>
            </div>
        </div>
        @isset($placeholder)
            <div class="mt-4">
                {{ $placeholder }}
            </div>
        @endisset
    </div>
    <template x-if="ready">
        <div>
            {{ $slot }}
        </div>
    </template>
</div>

@once
    @push('scripts')
        <script>
            window.initLazySection = window.initLazySection || function(target, onLoad, rootMargin) {
                if (!target || typeof onLoad !== 'function') {
                    return;
                }
                if (!('IntersectionObserver' in window)) {
                    onLoad();
                    return;
                }
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            observer.disconnect();
                            onLoad();
                        }
                    });
                }, { rootMargin: rootMargin || '200px 0px' });
                observer.observe(target);
            };
        </script>
    @endpush
@endonce
