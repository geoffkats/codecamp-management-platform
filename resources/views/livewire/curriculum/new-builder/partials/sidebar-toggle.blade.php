{{-- Client-side only: do not round-trip through Livewire or a full-page loader. --}}
<div id="curriculum-outline-toggle" class="absolute top-4 left-72 z-30">
    <button type="button"
            onclick="window.__toggleCurriculumOutline()"
            class="group relative rounded-r-full border border-gray-200 bg-white px-3 py-2 shadow-md transition-all duration-150 hover:bg-gray-50 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 -translate-x-1/2 border-l-0"
            aria-label="Toggle course outline"
            title="Toggle course outline (Ctrl+B)">
        <span class="sr-only">Toggle course outline</span>
        <svg class="h-5 w-5 text-gray-500 group-hover:text-orange-600 dark:text-gray-400 dark:group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M19 19l-7-7 7-7" />
        </svg>
    </button>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const KEY = 'curriculum-outline-collapsed';

                function apply() {
                    const shell = document.getElementById('curriculum-builder-shell');
                    if (!shell) return;
                    shell.classList.toggle('cb-collapsed', localStorage.getItem(KEY) === '1');
                }

                window.__toggleCurriculumOutline = function () {
                    localStorage.setItem(KEY, localStorage.getItem(KEY) === '1' ? '0' : '1');
                    apply();
                };

                document.addEventListener('keydown', function (e) {
                    if (!(e.ctrlKey || e.metaKey) || (e.key !== 'b' && e.key !== 'B') || e.repeat) return;
                    const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                    if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable) return;
                    e.preventDefault();
                    window.__toggleCurriculumOutline();
                });

                document.addEventListener('livewire:navigated', apply);
                document.addEventListener('livewire:init', apply);
                if (window.Livewire) {
                    apply();
                }
                requestAnimationFrame(apply);
            })();
        </script>
    @endpush
@endonce
