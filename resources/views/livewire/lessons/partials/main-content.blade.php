<x-lesson-media
    :lesson="$lesson"
    :video-progress="$videoProgress"
    :video-watched-seconds="$videoWatchedSeconds"
    :is-video-completed="$isVideoCompleted"
    :is-lesson-completed="$isLessonCompleted"
/>

<x-lesson-steps :lesson="$lesson" />

{{-- Scratch Blocks Reference (only for Scratch lessons) --}}
@if(!empty($lesson->scratch_blocks) && is_array($lesson->scratch_blocks) && $lesson->scratch_project_id)
    <x-lazy-section
        placeholder-title="Loading Scratch blocks..."
        placeholder-tone="orange"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 border-orange-200 dark:border-orange-800 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                Scratch Blocks Reference
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Blocks you'll use in this Scratch project:
            </p>
            <div class="flex flex-wrap gap-3">
                @foreach($lesson->scratch_blocks as $block)
                    <x-scratch-block 
                        :type="$block['category'] ?? 'motion'"
                        :text="$block['text'] ?? $block['name'] ?? 'Block'"
                    />
                @endforeach
            </div>
        </div>
    </x-lazy-section>
@endif

{{-- Slide Viewer (primary content when a file has been uploaded) --}}
@if($lesson->slide_file_path)
    @php
        $slidePublicUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->slide_file_path);
        $slideExt = strtolower(pathinfo($lesson->slide_file_path, PATHINFO_EXTENSION));
        $isPdf = $slideExt === 'pdf';
        $viewerSrc = $isPdf
            ? $slidePublicUrl
            : 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode(url('storage/' . $lesson->slide_file_path));
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h2>
            </div>
            <a href="{{ $slidePublicUrl }}" target="_blank" download
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
        </div>
        <div class="relative" style="padding-bottom: 62.5%; min-height: 480px;">
            <iframe src="{{ $viewerSrc }}"
                    class="absolute inset-0 w-full h-full border-0"
                    allowfullscreen
                    loading="lazy"
                    title="Lesson slides for {{ $lesson->title }}">
            </iframe>
        </div>
    </div>
@endif

{{-- HTML Lesson Page --}}
@if($lesson->html_content)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h2>
            </div>
        </div>
        <iframe srcdoc="{{ $lesson->html_content }}"
                sandbox="allow-scripts allow-forms"
                class="w-full border-0 block"
                style="height: 700px;"
                title="Lesson content for {{ $lesson->title }}"
                loading="lazy"></iframe>
    </div>
@endif

{{-- Text / Notes Content --}}
@if($lesson->content)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ $lesson->slide_file_path ? 'Notes' : 'Lesson Content' }}</h2>
        @php
            $hasHtml = strip_tags($lesson->content) !== $lesson->content;
        @endphp
        <div class="lesson-content prose prose-lg dark:prose-invert max-w-none {{ $hasHtml ? '' : 'whitespace-pre-wrap' }} break-words">
            {!! \App\Support\RichContent::render($lesson->content) !!}
        </div>
    </div>
@endif

{{-- Python Code Editor (for Python lessons) --}}
@if(stripos($lesson->title, 'python') !== false || stripos($lesson->content ?? '', 'python') !== false || $lesson->lesson_type === 'code')
    @php
        // Extract code from lesson content or use default
        $pythonCode = $lesson->code_example ?? "# Python Code Editor\nprint('Hello, World!')\n\n# Try writing your own code:\nname = 'Student'\nprint(f'Welcome, {name}!')";
    @endphp
    <x-lesson-code-editor
        type="single"
        language="python"
        :code="$pythonCode"
        title="Python Practice"
    />
@endif

{{-- Web Development Editor (for HTML/CSS/JS lessons) --}}
@if(stripos($lesson->title, 'web') !== false || stripos($lesson->title, 'html') !== false || stripos($lesson->title, 'css') !== false || stripos($lesson->title, 'javascript') !== false)
    @php
        // Normalize escaped newlines that may come from JSON/text storage
        $normalizeCode = fn($code) => str_replace(["\\r\\n", "\\n", "\r\n"], "\n", $code ?? '');

        $htmlCode = $normalizeCode($lesson->html_example) ?: "<h1>Hello World!</h1>\n<p>Welcome to web development!</p>\n<button onclick=\"alert('Hello!')\">Click Me</button>";
        $cssCode = $normalizeCode($lesson->css_example) ?: "body {\n  font-family: Arial, sans-serif;\n  padding: 20px;\n  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n  color: white;\n}\n\nh1 {\n  text-align: center;\n  font-size: 3em;\n}\n\nbutton {\n  background: white;\n  color: #667eea;\n  padding: 10px 20px;\n  border: none;\n  border-radius: 5px;\n  cursor: pointer;\n  font-size: 1.2em;\n}";
        $jsCode = $normalizeCode($lesson->js_example) ?: "// JavaScript code\nconsole.log('Page loaded!');\n\n// Add interactivity\ndocument.addEventListener('DOMContentLoaded', function() {\n  console.log('Ready to code!');\n});";
    @endphp
    <x-lesson-code-editor
        type="web"
        :html="$htmlCode"
        :css="$cssCode"
        :javascript="$jsCode"
        title="Web Development Playground"
    />
@endif

{{-- JavaScript Code Editor (for standalone JS lessons) --}}
@if(stripos($lesson->title, 'javascript') !== false && stripos($lesson->title, 'web') === false && stripos($lesson->title, 'html') === false)
    @php
        $jsCode = $lesson->code_example ?? "// JavaScript Code Editor\nconsole.log('Hello, JavaScript!');\n\n// Try your own code:\nconst numbers = [1, 2, 3, 4, 5];\nconst sum = numbers.reduce((a, b) => a + b, 0);\nconsole.log('Sum:', sum);";
    @endphp
    <x-lesson-code-editor
        type="single"
        language="javascript"
        :code="$jsCode"
        title="JavaScript Practice"
    />
@endif

{{-- Learning Objectives --}}
@if($lesson->objectives)
    <x-lazy-section
        placeholder-title="Loading objectives..."
        placeholder-tone="blue"
    >
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-lg border-2 border-blue-200 dark:border-blue-800 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                What You'll Learn
            </h2>
            <div class="prose dark:prose-invert max-w-none">
                {!! nl2br(e($lesson->objectives)) !!}
            </div>
        </div>
    </x-lazy-section>
@endif

{{-- Attachments --}}
@if($lesson->attachments && is_array($lesson->attachments) && count($lesson->attachments) > 0)
    <x-lazy-section
        placeholder-title="Loading attachments..."
        placeholder-tone="gray"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Attachments</h2>
            <div class="space-y-2">
                @foreach($lesson->attachments as $attachment)
                    <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" 
                       class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-gray-900 dark:text-white">{{ $attachment['name'] ?? 'Download' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </x-lazy-section>
@endif

@if(!$isLessonCompleted && $canComplete)
    <form method="POST" action="{{ route('lessons.complete', $lesson->id) }}" class="flex">
        @csrf
        <button
            type="submit"
            class="flex-1 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-lg transition-colors flex items-center justify-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Mark Lesson Complete
        </button>
    </form>
@endif

{{-- Post-Lesson Feedback CTA --}}
@if($isLessonCompleted)
    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl shadow-lg border-2 border-emerald-200 dark:border-emerald-800 p-6">
        @php
            $hasLessonFeedback = \App\Models\TeacherFeedback::where('student_id', auth()->id())
                ->where('course_id', $course->id)
                ->where('feedback', 'like', 'Lesson ID: ' . $lesson->id . "%")
                ->exists();
        @endphp

        @if(session('feedback_submitted') || $hasLessonFeedback)
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Thanks for your feedback!</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Your feedback was sent to the teacher.</p>
                    <div class="mt-3">
                        <a href="{{ route('feedback.teacher') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-emerald-600 text-emerald-700 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                            Open full feedback page
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="flex items-start gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-md">
                    
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Rate this lesson</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Share a quick rating and send feedback to your teacher to improve the next lesson.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('feedback.teacher.submit') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @csrf
                <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="lesson_title" value="{{ $lesson->title }}">
                <input type="hidden" name="source" value="lesson_view">
                <input type="hidden" name="category" value="general">

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Rating</label>
                    <select name="rating" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="" disabled selected>Choose rating</option>
                        <option value="5">Excellent (5)</option>
                        <option value="4">Good (4)</option>
                        <option value="3">Okay (3)</option>
                        <option value="2">Needs work (2)</option>
                        <option value="1">Poor (1)</option>
                    </select>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Quick feedback</label>
                    <textarea name="note" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="What worked well? What could improve?" required></textarea>
                </div>

                <div class="flex flex-wrap gap-2 md:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Send feedback
                    </button>
                    <a href="{{ route('feedback.teacher') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-emerald-600 text-emerald-700 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                        Open full feedback page
                    </a>
                </div>
            </form>
        @endif
    </div>
@endif
