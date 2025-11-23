{{-- 
    Example Student Lesson View
    This shows how to display the HTML content created with Summernote
    
    Usage: Create a route and controller/Livewire component to use this view
--}}

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Lesson Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-8 py-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 text-sm font-medium rounded-lg bg-white/20 text-white capitalize">
                        {{ $lesson->lesson_type }}
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-lg bg-white/20 text-white">
                        {{ $lesson->duration_minutes }} minutes
                    </span>
                    @if($lesson->difficulty_level)
                        <span class="px-3 py-1 text-sm font-medium rounded-lg bg-white/20 text-white capitalize">
                            {{ $lesson->difficulty_level }}
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    {{ $lesson->title }}
                </h1>
                @if($lesson->summary)
                    <p class="text-white/90 text-lg">
                        {{ $lesson->summary }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Learning Objectives --}}
        @if($lesson->objectives)
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 mb-6 border border-blue-200 dark:border-blue-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Learning Objectives
                </h2>
                <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ $lesson->objectives }}
                </div>
            </div>
        @endif

        {{-- Video Content (if video lesson) --}}
        @if($lesson->lesson_type === 'video' && $lesson->video_url)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
                <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden">
                    {{-- Add your video player here --}}
                    <iframe 
                        src="{{ $lesson->video_url }}" 
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        @endif

        {{-- Main Lesson Content (HTML from Summernote) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Lesson Content
            </h2>
            
            {{-- 
                IMPORTANT: Use {!! !!} to render HTML (not {{ }})
                This displays the formatted content created with Summernote
            --}}
            <div class="prose prose-lg dark:prose-invert max-w-none">
                {!! $lesson->content !!}
            </div>
        </div>

        {{-- Implementation Guidance --}}
        @if($lesson->implementation_guidance)
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6 mb-6 border border-green-200 dark:border-green-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Implementation Tips
                </h2>
                <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ $lesson->implementation_guidance }}
                </div>
            </div>
        @endif

        {{-- Attachments --}}
        @if(!empty($lesson->attachments))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Downloadable Resources
                </h2>
                <div class="space-y-2">
                    @foreach($lesson->attachments as $attachment)
                        <a href="{{ Storage::url($attachment['path']) }}" 
                           download="{{ $attachment['name'] }}"
                           class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $attachment['name'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($attachment['size'] / 1024, 2) }} KB
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="flex justify-between items-center">
            <a href="{{ route('lessons.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Lessons
            </a>
            
            @if($nextLesson)
                <a href="{{ route('lessons.show', $nextLesson->id) }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-colors">
                    Next Lesson
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>

<style>
    /* Tailwind Typography (Prose) styles for the content */
    .prose {
        color: #374151;
    }
    .dark .prose {
        color: #d1d5db;
    }
    .prose h1, .prose h2, .prose h3, .prose h4 {
        color: #111827;
        font-weight: 700;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
    }
    .dark .prose h1, .dark .prose h2, .dark .prose h3, .dark .prose h4 {
        color: #f9fafb;
    }
    .prose p {
        margin-bottom: 1em;
        line-height: 1.75;
    }
    .prose img {
        border-radius: 0.5rem;
        margin: 1.5em 0;
    }
    .prose a {
        color: #2563eb;
        text-decoration: underline;
    }
    .dark .prose a {
        color: #60a5fa;
    }
    .prose ul, .prose ol {
        margin: 1em 0;
        padding-left: 1.5em;
    }
    .prose li {
        margin: 0.5em 0;
    }
    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5em 0;
    }
    .prose th, .prose td {
        border: 1px solid #e5e7eb;
        padding: 0.75em;
    }
    .dark .prose th, .dark .prose td {
        border-color: #374151;
    }
    .prose th {
        background-color: #f3f4f6;
        font-weight: 600;
    }
    .dark .prose th {
        background-color: #1f2937;
    }
</style>
