{{-- Lesson Content --}}

{{-- ── Mobile quick-action bar (assessments + quizzes) ──────────────────── --}}
{{-- Shown above content on phones so students don't have to scroll past everything --}}
@php
    $hasQuickActions = ($lesson->assessments && $lesson->assessments->count() > 0)
                    || ($lesson->quizzes && $lesson->quizzes->count() > 0);
@endphp
@if($hasQuickActions)
    <div class="lg:hidden">
        <div class="flex gap-3 overflow-x-auto pb-1 scrollbar-hide snap-x">
            @foreach($lesson->assessments as $assessment)
                @php
                    $assessmentTypeColor = match($assessment->assessment_type) {
                        'quiz' => 'bg-blue-600 hover:bg-blue-700',
                        'assignment' => 'bg-purple-600 hover:bg-purple-700',
                        'unit_survey' => 'bg-green-600 hover:bg-green-700',
                        'self_assessment' => 'bg-indigo-600 hover:bg-indigo-700',
                        'peer_review' => 'bg-pink-600 hover:bg-pink-700',
                        default => 'bg-orange-600 hover:bg-orange-700',
                    };
                @endphp
                <a href="{{ route('assessments.take', $assessment) }}" wire:navigate
                   class="flex-shrink-0 snap-start inline-flex items-center gap-2 px-4 py-2.5 {{ $assessmentTypeColor }} text-white text-sm font-bold rounded-xl shadow-sm active:scale-95 transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ \Illuminate\Support\Str::limit($assessment->title, 22) }}
                    @if($assessment->is_required)
                        <span class="text-xs bg-white/20 px-1.5 py-0.5 rounded-full">Required</span>
                    @endif
                </a>
            @endforeach
            @foreach($lesson->quizzes as $quiz)
                <a href="{{ route('quizzes.take', $quiz) }}" wire:navigate
                   class="flex-shrink-0 snap-start inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm active:scale-95 transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ \Illuminate\Support\Str::limit($quiz->title, 22) }}
                </a>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Scroll right for more</p>
    </div>
@endif

{{-- ── Main grid layout ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    {{-- Main Content (always full width on mobile) --}}
    <div class="lg:col-span-2 xl:col-span-3 space-y-6">
        @include('livewire.lessons.partials.main-content')
    </div>

    {{-- Sidebar: NOT sticky — sticky was cropping content at bottom of viewport --}}
    <div class="space-y-4 lg:space-y-6">
        @include('livewire.lessons.partials.sidebar')
    </div>
</div>
