<div wire:key="lesson-view-{{ $lesson->id }}">
@php
    $user = auth()->user();
    $hasLearnerEnrollment = (bool) $enrollment;
    $isInstructor = !$hasLearnerEnrollment && (
        $course->instructor_id === $user->id
        || $user->hasRole('admin')
        || $user->hasRole('supervisor')
        || $user->hasRole('teacher')
    );
    $canAccessDiscussions = $user->canAccessDiscussions() && !$user->isIctTeacher() && ($hasLearnerEnrollment || $isInstructor);
@endphp

@include('livewire.lessons.partials.flash-messages')
@if(!$isInstructor)
    @include('livewire.lessons.partials.completion-modal')
@endif

{{-- Instructor preview banner --}}
@if($isInstructor)
<div class="flex items-center justify-between gap-3 bg-blue-600 px-4 py-2 text-sm text-white">
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span class="font-semibold">Instructor Preview</span>
        <span class="hidden sm:inline text-blue-200">— viewing as students see this lesson. Progress is not tracked.</span>
    </div>
    <a href="{{ route('curriculum.builder', ['course' => $course->id]) }}" wire:navigate
       class="flex-shrink-0 inline-flex items-center gap-1 rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold text-white hover:bg-white/30 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edit in Builder
    </a>
</div>
@endif

{{-- ───────────────────────────────────────────────────────────────────────── --}}
{{-- Two-panel LMS layout: course outline sidebar + scrollable content         --}}
{{-- ───────────────────────────────────────────────────────────────────────── --}}
<div
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    class="relative flex min-h-screen bg-gray-50 dark:bg-gray-950"
>
    {{-- Hidden poll to keep completion status fresh --}}
    <div class="hidden" wire:poll.60s="checkCompletionStatus"></div>

    {{-- ── Mobile overlay backdrop ──────────────────────────────────────── --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-30 lg:hidden"
    ></div>

    {{-- ── Course Outline Sidebar ───────────────────────────────────────── --}}
    <aside
        x-cloak
        class="fixed lg:sticky top-0 left-0 z-40 lg:z-auto h-screen w-72 flex-shrink-0
               bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800
               flex flex-col overflow-hidden
               transform transition-transform duration-300 ease-in-out lg:transition-none"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        {{-- Sidebar header --}}
        <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-800 flex items-start justify-between gap-2 flex-shrink-0">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Course Content</p>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white leading-tight line-clamp-2">{{ $course->title }}</h2>
            </div>
            <button
                @click="sidebarOpen = false"
                class="lg:hidden flex-shrink-0 mt-0.5 p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Course progress bar --}}
        @if($enrollment)
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Your progress</span>
                <span class="text-xs font-bold text-orange-600 dark:text-orange-400">{{ $courseProgress }}%</span>
            </div>
            <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500 rounded-full transition-all duration-500"
                     style="width: {{ $courseProgress }}%"></div>
            </div>
            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                {{ count($completedLessonIds) }} of {{ $modules->pluck('lessons')->flatten()->count() }} lessons done
            </p>
        </div>
        @endif

        {{-- Module / lesson list --}}
        <div class="flex-1 overflow-y-auto">
            @forelse($modules as $moduleIndex => $module)
            @php $moduleHasCurrent = $module->lessons->contains('id', $lesson->id); @endphp
            <div x-data="{ open: {{ $moduleHasCurrent ? 'true' : 'false' }} }" class="border-b border-gray-100 dark:border-gray-800">

                {{-- Module toggle row --}}
                <button
                    @click="open = !open"
                    class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors group"
                >
                    <span class="flex-shrink-0 mt-0.5 w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-900/30 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                        {{ $moduleIndex + 1 }}
                    </span>
                    <span class="flex-1 text-[13px] font-semibold text-gray-700 dark:text-gray-200 leading-snug">{{ $module->title }}</span>
                    <svg
                        :class="open ? 'rotate-180' : ''"
                        class="flex-shrink-0 mt-0.5 w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Lesson rows inside module --}}
                <div x-show="open" x-transition:enter="transition-all duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @foreach($module->lessons as $lessonItem)
                    @php
                        $isCurrent   = $lessonItem->id === $lesson->id;
                        $isDone      = in_array($lessonItem->id, $completedLessonIds);
                    @endphp
                    <a
                        href="{{ route('lessons.view', $lessonItem) }}"
                        wire:navigate
                        class="flex items-center gap-3 pl-10 pr-4 py-2.5 text-[13px] border-l-2 transition-colors
                               {{ $isCurrent
                                  ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300'
                                  : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100 hover:border-gray-300 dark:hover:border-gray-600' }}"
                    >
                        {{-- Status icon --}}
                        <span class="flex-shrink-0 w-4 h-4">
                            @if($isDone)
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @elseif($isCurrent)
                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" stroke-width="1.5"/>
                                </svg>
                            @endif
                        </span>
                        <span class="flex-1 leading-snug {{ $isCurrent ? 'font-semibold' : '' }}">{{ $lessonItem->title }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">No lessons yet</div>
            @endforelse
        </div>
    </aside>

    {{-- ── Main Content Column ──────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0">

        {{-- Sticky lesson top bar --}}
        <div class="sticky top-0 z-20 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm flex-shrink-0">
            <div class="flex items-center gap-3 px-4 py-3">

                {{-- Sidebar toggle button --}}
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="flex-shrink-0 p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    title="Toggle course outline"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </button>

                {{-- Breadcrumb + title --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 mb-0.5 truncate">
                        <a href="{{ route('courses.show', $course) }}" wire:navigate
                           class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors truncate max-w-[140px]">
                            {{ $course->title }}
                        </a>
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="truncate max-w-[160px]">{{ $lesson->module->title ?? '' }}</span>
                    </div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate leading-tight">{{ $lesson->title }}</p>
                </div>

                {{-- Completion badge or discuss button --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($canAccessDiscussions)
                        <a href="#discussion"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Discuss
                        </a>
                    @endif
                    @unless($user->isIctTeacher())
                        @if($isLessonCompleted)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/40 rounded-lg">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">Completed</span>
                            </span>
                        @endif
                    @endunless
                    @if($isInstructor)
                        <a href="{{ route('lessons.edit', $lesson) }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Video progress bar --}}
            @if($lesson->content_type === 'video' && $videoProgress > 0)
            <div class="px-4 pb-2.5">
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                             style="width: {{ $videoProgress }}%"></div>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 flex-shrink-0">{{ round($videoProgress) }}% watched</span>
                </div>
            </div>
            @endif

            {{-- Section quick-nav tabs (only shown if lesson has assessments/quizzes/assignments) --}}
            @php
                $hasAssessments = $lesson->assessments && $lesson->assessments->count() > 0;
                $hasQuizzes     = $lesson->quizzes && $lesson->quizzes->count() > 0;
                $hasAssignments = $lesson->assignments && $lesson->assignments->count() > 0;
                $hasSections    = $hasAssessments || $hasQuizzes || $hasAssignments || $canAccessDiscussions;
            @endphp
            @if($hasSections)
            <div class="border-t border-gray-100 dark:border-gray-800 flex items-center gap-0 overflow-x-auto scrollbar-hide px-2">
                <button onclick="document.getElementById('lesson-content-top')?.scrollIntoView({behavior:'smooth',block:'start'})"
                        class="flex-shrink-0 px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 border-b-2 border-transparent hover:border-gray-400 transition-colors whitespace-nowrap">
                    Content
                </button>
                @if($hasAssessments)
                <button onclick="document.getElementById('lesson-assessments')?.scrollIntoView({behavior:'smooth',block:'start'})"
                        class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-orange-600 dark:text-orange-400 border-b-2 border-orange-500 whitespace-nowrap">
                    Assessments
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400">{{ $lesson->assessments->count() }}</span>
                </button>
                @endif
                @if($hasQuizzes)
                <button onclick="document.getElementById('lesson-quizzes')?.scrollIntoView({behavior:'smooth',block:'start'})"
                        class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-blue-600 dark:text-blue-400 border-b-2 border-transparent hover:border-blue-400 transition-colors whitespace-nowrap">
                    Quizzes
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">{{ $lesson->quizzes->count() }}</span>
                </button>
                @endif
                @if($hasAssignments)
                <button onclick="document.getElementById('lesson-assignments')?.scrollIntoView({behavior:'smooth',block:'start'})"
                        class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-purple-600 dark:text-purple-400 border-b-2 border-transparent hover:border-purple-400 transition-colors whitespace-nowrap">
                    Assignments
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400">{{ $lesson->assignments->count() }}</span>
                </button>
                @endif
                @if($canAccessDiscussions)
                <button onclick="document.getElementById('discussion')?.scrollIntoView({behavior:'smooth',block:'start'})"
                        class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 border-b-2 border-transparent hover:border-emerald-400 transition-colors whitespace-nowrap">
                    Discussion
                </button>
                @endif
            </div>
            @endif
        </div>

        {{-- Requirements warning (shown below top bar if missing requirements) --}}
        @if(!$isLessonCompleted && !empty($completionStatus['missing'] ?? []))
        <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/60 px-4 sm:px-6 py-3 flex-shrink-0">
            <div class="flex items-start gap-3 max-w-5xl mx-auto">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-amber-900 dark:text-amber-100 mb-1">Complete these to finish the lesson:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($completionStatus['missing'] as $missing)
                        <span class="inline-flex items-center gap-1.5 text-xs bg-amber-100 dark:bg-amber-800/50 text-amber-800 dark:text-amber-200 px-2.5 py-1 rounded-full">
                            {{ $missing['title'] ?? $missing['message'] ?? 'Complete requirement' }}
                            @if(isset($missing['route'], $missing['id']))
                                <a href="{{ route($missing['route'], $missing['id']) }}" wire:navigate class="underline hover:text-amber-900 dark:hover:text-amber-100">Go →</a>
                            @endif
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Lesson body ──────────────────────────────────────── --}}
        <div class="pb-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

                {{-- Scroll target for "Content" tab --}}
                <div id="lesson-content-top" class="scroll-mt-36"></div>

                {{-- ── Lesson title & description (clean, no card) ── --}}
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                        @if($lesson->module)
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium">
                                Module {{ $lesson->module->order_index }}
                            </span>
                        @endif
                        @if($lesson->duration_minutes)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $lesson->duration_minutes }} min
                            </span>
                        @endif
                        @if($lesson->points_reward)
                            <span class="flex items-center gap-1 text-yellow-600 dark:text-yellow-400 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                +{{ $lesson->points_reward }} XP
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight mb-3">
                        {{ $lesson->title }}
                    </h1>
                    @if($lesson->description)
                        <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed max-w-2xl">
                            {{ $lesson->description }}
                        </p>
                    @endif
                </div>

                {{-- ── Main lesson content (video, text, code editors, etc.) ── --}}
                @include('livewire.lessons.partials.main-content')

                {{-- ── Assessments (inline, prominent) ── --}}
                @if($lesson->assessments && $lesson->assessments->count() > 0)
                <div id="lesson-assessments" class="scroll-mt-36">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        Assessments
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($lesson->assessments as $assessment)
                            <x-lesson.assessment-card :assessment="$assessment" />
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ── Quizzes (inline) ── --}}
                @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                <div id="lesson-quizzes" class="scroll-mt-36">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Quizzes
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($lesson->quizzes as $quiz)
                        <a href="{{ route('quizzes.take', $quiz) }}" wire:navigate
                           class="group flex items-center gap-4 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors truncate">{{ $quiz->title }}</p>
                                @if($quiz->time_limit)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $quiz->time_limit }} min</p>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-blue-500 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ── Assignments (inline) ── --}}
                @if($lesson->assignments && $lesson->assignments->count() > 0)
                <div id="lesson-assignments" class="scroll-mt-36">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Assignments
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($lesson->assignments as $assignment)
                            <x-lesson.assignment-card :assignment="$assignment" />
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ── Lesson discussion forum (embedded) ── --}}
                @if($canAccessDiscussions)
                <div id="discussion" class="scroll-mt-36">
                    @livewire('discussions.discussion-list', [
                        'courseId' => $course->id,
                        'lessonId' => $lesson->id,
                        'compact' => true,
                    ], key('lesson-discussion-' . $lesson->id))
                </div>
                @endif

            </div>
        </div>
    </div>{{-- end main column --}}

    {{-- ── Fixed bottom navigation bar ────────────────────────────────────── --}}
    <nav class="fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shadow-[0_-2px_12px_rgba(0,0,0,0.06)] dark:shadow-[0_-2px_12px_rgba(0,0,0,0.3)]">
        <div class="px-4 sm:px-6 py-3 flex items-center gap-3 max-w-5xl mx-auto">

            {{-- Previous lesson --}}
            @if($previousLesson)
                <a href="{{ route('lessons.view', $previousLesson) }}" wire:navigate
                   class="group flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold text-sm transition-colors flex-shrink-0">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline truncate max-w-[120px]">{{ $previousLesson->title }}</span>
                    <span class="sm:hidden">Prev</span>
                </a>
            @else
                <div class="w-10 flex-shrink-0"></div>
            @endif

            {{-- Center: completion action --}}
            <div class="flex-1 flex justify-center">
                @if($isInstructor)
                    {{-- Instructors previewing — no completion tracking --}}
                    <span class="text-xs text-blue-500 dark:text-blue-400 font-medium italic">Preview mode — completion not tracked</span>
                @else
                    @unless($user->isIctTeacher())
                        @if($isLessonCompleted)
                            <div class="flex items-center gap-2 text-green-700 dark:text-green-300 text-sm font-bold">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Lesson Completed
                            </div>
                        @elseif($canComplete)
                            <button wire:click="openCompletionModal"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-95 text-white font-bold rounded-xl transition-all shadow-sm text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Mark Complete
                            </button>
                        @else
                            <span class="text-xs text-gray-500 dark:text-gray-400 text-center leading-tight px-2">
                                @if(!empty($completionStatus['missing'] ?? []))
                                    Complete required tasks above to finish
                                @else
                                    Lesson in progress
                                @endif
                            </span>
                        @endif
                    @endunless
                @endif
            </div>

            {{-- Next lesson --}}
            @if($nextLesson)
                <a href="{{ route('lessons.view', $nextLesson) }}" wire:navigate
                   class="group flex items-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-95 text-white rounded-xl font-bold text-sm transition-all shadow-sm flex-shrink-0">
                    <span class="hidden sm:inline truncate max-w-[120px]">{{ $nextLesson->title }}</span>
                    <span class="sm:hidden">Next</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @elseif($isLessonCompleted)
                <a href="{{ route('courses.show', $course) }}" wire:navigate
                   class="flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 active:scale-95 text-white rounded-xl font-bold text-sm transition-all shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="hidden sm:inline">Back to Course</span>
                    <span class="sm:hidden">Course</span>
                </a>
            @else
                <div class="w-10 flex-shrink-0"></div>
            @endif

        </div>
    </nav>

</div>{{-- end two-panel wrapper --}}

</div>
