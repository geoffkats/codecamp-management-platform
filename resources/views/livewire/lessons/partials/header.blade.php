{{-- Lesson Header - Sticky with Breadcrumbs --}}
<div id="lesson-header" class="sticky top-0 z-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden" data-collapsed="false">
    <div class="hidden" wire:poll.60s="checkCompletionStatus"></div>
    <div class="flex items-center justify-end gap-2 px-4 py-2 border-b border-gray-100 dark:border-gray-700">
        <button
            type="button"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            aria-label="Toggle header"
            data-header-toggle
        >
            <svg class="w-4 h-4 transition-transform" data-header-toggle-icon fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <span class="text-xs font-semibold" data-header-toggle-label>Collapse</span>
        </button>
        <button
            type="button"
            class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            aria-label="Scroll to top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
        </button>
    </div>
    <div class="px-6 py-4 space-y-4 lesson-header-collapsible">
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <a href="{{ auth()->user()->canAccessCourseCatalog() ? route('courses.index') : route('enrollments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors" wire:navigate>
                {{ auth()->user()->canAccessCourseCatalog() ? 'Courses' : 'My Courses' }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('courses.show', $course) }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors" wire:navigate>
                {{ $course->title }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-700 dark:text-gray-300">{{ $lesson->title }}</span>
        </nav>

        {{-- Title Section --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <div class="flex-1">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">
                    {{ $lesson->title }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        Module {{ $lesson->module->order_index }}
                    </span>
                    @if($lesson->duration_minutes)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $lesson->duration_minutes }} min
                        </span>
                    @endif
                </p>
                @if($lesson->description)
                    <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-2xl">
                        {{ $lesson->description }}
                    </p>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 flex-wrap md:flex-nowrap">
                @unless(auth()->user()->isIctTeacher())
                    {{-- Discussion Button --}}
                    @if($lesson->id && auth()->user()->canAccessDiscussions())
                        <flux:button 
                            href="{{ route('discussions.index', ['lesson' => $lesson->id]) }}"
                            variant="ghost"
                            wire:navigate
                            class="rounded-full px-4 py-2 flex items-center gap-2 hover:shadow-md transition-all duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="hidden sm:inline">Discuss</span>
                        </flux:button>
                    @endif
                    
                    @if($isLessonCompleted)
                        <flux:badge variant="success" class="rounded-full px-4 py-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Completed
                        </flux:badge>
                    @else
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if($canComplete)
                                Auto-completing lesson...
                            @else
                                Pass the required assessment to complete this lesson.
                            @endif
                        </div>
                    @endif
                @endunless
            </div>
        </div>

        {{-- Video Progress for Video Lessons --}}
        @if($lesson->content_type === 'video' && $videoProgress > 0)
            <div class="pt-2">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Video Progress
                    </div>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ round($videoProgress) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500 ease-out shadow-sm" 
                         style="width: {{ $videoProgress }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Missing Requirements Warning --}}
    @if(!$isLessonCompleted && !empty($completionStatus['missing'] ?? []))
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-t border-amber-200 dark:border-amber-800 px-6 py-4 lesson-header-warning">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-100 mb-2">
                        Complete These Requirements First
                    </h4>
                    <div class="space-y-2">
                        @foreach($completionStatus['missing'] ?? [] as $missing)
                            <div class="flex items-center gap-2 text-sm text-amber-900 dark:text-amber-100">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span class="flex-1">{{ $missing['title'] ?? $missing['message'] ?? 'Complete requirement' }}</span>
                                @if(isset($missing['type_label']))
                                    <span class="px-2 py-1 bg-amber-100 dark:bg-amber-800 text-amber-800 dark:text-amber-100 text-xs rounded-full flex-shrink-0">
                                        {{ $missing['type_label'] }}
                                    </span>
                                @endif
                                @if(isset($missing['route']) && isset($missing['id']))
                                    <a href="{{ route($missing['route'], $missing['id']) }}" wire:navigate class="ml-2 text-xs text-amber-700 dark:text-amber-300 underline hover:text-amber-900 dark:hover:text-amber-100 transition-colors flex-shrink-0">
                                        Go →
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const header = document.getElementById('lesson-header');
                if (!header) return;

                const toggle = header.querySelector('[data-header-toggle]');
                const toggleIcon = header.querySelector('[data-header-toggle-icon]');
                const toggleLabel = header.querySelector('[data-header-toggle-label]');
                const collapsible = header.querySelector('.lesson-header-collapsible');
                const warning = header.querySelector('.lesson-header-warning');

                const applyCollapsed = (collapsed) => {
                    header.dataset.collapsed = collapsed ? 'true' : 'false';
                    if (collapsible) {
                        collapsible.classList.toggle('hidden', collapsed);
                    }
                    if (warning) {
                        warning.classList.toggle('hidden', collapsed);
                    }
                    if (toggle) {
                        toggle.setAttribute('aria-label', collapsed ? 'Expand header' : 'Collapse header');
                    }
                    if (toggleIcon) {
                        toggleIcon.classList.toggle('rotate-180', collapsed);
                    }
                    if (toggleLabel) {
                        toggleLabel.textContent = collapsed ? 'Expand' : 'Collapse';
                    }
                };

                const stored = localStorage.getItem('lessonHeaderCollapsed');
                if (stored !== null) {
                    applyCollapsed(stored === 'true');
                }

                if (toggle) {
                    toggle.addEventListener('click', function() {
                        const next = header.dataset.collapsed !== 'true';
                        applyCollapsed(next);
                        localStorage.setItem('lessonHeaderCollapsed', next ? 'true' : 'false');
                    });
                }
            });
        </script>
    @endpush
@endonce
