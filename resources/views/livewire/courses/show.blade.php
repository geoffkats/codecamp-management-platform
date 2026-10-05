@php
    $readable = fn (?string $text) => $text !== null && $text === mb_strtoupper($text) && preg_match('/\p{L}{4,}/u', $text)
        ? \Illuminate\Support\Str::title(mb_strtolower($text))
        : $text;
@endphp
<div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6">
    @if(session()->has('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
            <flux:icon.exclamation-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <header class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <a href="{{ route('courses.index') }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-orange-600 dark:text-zinc-400">
            <flux:icon.arrow-left class="size-3.5" /> All courses
        </a>

        <div class="mt-3 flex flex-col gap-5 lg:flex-row lg:items-start">
            @if($course->featured_image)
                <img src="{{ asset('storage/' . $course->featured_image) }}" alt="" class="size-20 shrink-0 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-zinc-700">
            @else
                <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-3xl font-bold text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                    {{ strtoupper(mb_substr($course->title, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $readable($course->title) }}</h1>
                    @if($canManage || $isOversight)
                        @if(! $course->is_published)
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">Not published</span>
                        @endif
                        @if($course->approval_status === 'pending')
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Waiting for approval</span>
                        @elseif($course->approval_status === 'rejected')
                            <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Changes requested</span>
                        @elseif($course->is_published)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">Live</span>
                        @endif
                    @endif
                </div>

                @if($course->short_description)
                    <p class="mt-1.5 line-clamp-2 max-w-3xl text-sm text-gray-600 dark:text-zinc-400">{{ $course->short_description }}</p>
                @endif

                <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-600 dark:text-zinc-400">
                    @if($course->instructor)
                        <div class="flex items-center gap-1.5"><flux:icon.user-circle class="size-4 text-gray-400" /><dt class="sr-only">Instructor</dt><dd>{{ $course->instructor->name }}</dd></div>
                    @endif
                    @if($course->difficulty_level)
                        <div class="flex items-center gap-1.5"><flux:icon.signal class="size-4 text-gray-400" /><dt class="sr-only">Level</dt><dd>{{ ucfirst($course->difficulty_level) }}</dd></div>
                    @endif
                    <div class="flex items-center gap-1.5"><flux:icon.squares-2x2 class="size-4 text-gray-400" /><dd>{{ $modules->count() }} {{ \Illuminate\Support\Str::plural('module', $modules->count()) }} · {{ $lessonCount }} {{ \Illuminate\Support\Str::plural('lesson', $lessonCount) }}</dd></div>
                    @if($quizCount)
                        <div class="flex items-center gap-1.5"><flux:icon.clipboard-document-check class="size-4 text-gray-400" /><dd>{{ $quizCount }} {{ \Illuminate\Support\Str::plural('quiz', $quizCount) }}</dd></div>
                    @endif
                    @if($course->estimated_duration)
                        <div class="flex items-center gap-1.5"><flux:icon.clock class="size-4 text-gray-400" /><dd>{{ $course->estimated_duration }} hours</dd></div>
                    @endif
                    @if($canManage || $isOversight)
                        <div class="flex items-center gap-1.5"><flux:icon.users class="size-4 text-gray-400" /><dd>{{ $studentCount }} {{ \Illuminate\Support\Str::plural('student', $studentCount) }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Actions --}}
            <div class="flex shrink-0 flex-wrap items-center gap-2 lg:max-w-xs lg:justify-end">
                @if($canManage)
                    <a href="{{ route('curriculum.builder', $course) }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                        <flux:icon.wrench-screwdriver class="size-4" /> Open builder
                    </a>
                    <a href="{{ route('courses.preview', $course) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <flux:icon.eye class="size-4" /> Preview
                    </a>
                    <a href="{{ route('courses.edit', $course) }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <flux:icon.pencil-square class="size-4" /> Settings
                    </a>
                    <a href="{{ route('courses.enrollments', $course) }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <flux:icon.users class="size-4" /> Students
                    </a>
                @elseif($enrolled)
                    <a href="{{ route('courses.learn', $course) }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                        <flux:icon.play class="size-4" /> Continue learning
                    </a>
                @elseif(! auth()->check())
                    <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-600">Sign in to enroll</a>
                @elseif($enrollmentType === 'invite_only' && ! $hasInvitation)
                    <p class="max-w-xs rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <span class="font-semibold text-gray-900 dark:text-white">Invitation only.</span> Ask the instructor to invite you.
                    </p>
                @elseif($enrollmentType === 'approval_required' && $hasPendingRequest)
                    <p class="max-w-xs rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                        <span class="font-semibold">Request sent.</span> Waiting for the instructor to approve it.
                    </p>
                @else
                    <button type="button" wire:click="enroll" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 disabled:opacity-60">
                        {{ match (true) {
                            $enrollmentType === 'invite_only' => 'Accept invitation',
                            $enrollmentType === 'approval_required' => 'Request to enroll',
                            default => 'Enroll',
                        } }}
                    </button>
                @endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Course content --}}
        <section class="lg:col-span-2" x-data>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Course content</h2>
                @if($modules->count() > 1)
                    <div class="flex gap-3 text-xs font-medium text-gray-500 dark:text-zinc-400">
                        <button type="button" class="hover:text-orange-600" @click="$dispatch('course-modules', true)">Expand all</button>
                        <button type="button" class="hover:text-orange-600" @click="$dispatch('course-modules', false)">Collapse all</button>
                    </div>
                @endif
            </div>

            @if($modules->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-zinc-700 dark:text-zinc-400">
                    No lessons yet.
                    @if($canManage)
                        <a href="{{ route('curriculum.builder', $course) }}" wire:navigate class="font-medium text-orange-600 hover:underline">Add some in the builder</a>.
                    @endif
                </div>
            @else
                <ol class="space-y-3">
                    @foreach($modules as $module)
                        @php
                            $visibleLessons = $showAllLessons ? $module->lessons : $module->lessons->take(2);
                            $moduleQuizCount = $module->lessons->sum(fn ($l) => ($quizzesByLesson[$l->id] ?? collect())->count());
                        @endphp
                        <li wire:key="module-{{ $module->id }}"
                            x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                            @course-modules.window="open = $event.detail"
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                            <button type="button" @click="open = ! open" :aria-expanded="open"
                                    class="flex w-full items-center gap-4 px-4 py-3.5 text-left hover:bg-gray-50 dark:hover:bg-zinc-800/60">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $loop->iteration }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold text-gray-900 dark:text-white">{{ $readable($module->title) }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-zinc-400">
                                        {{ $module->lessons->count() }} {{ \Illuminate\Support\Str::plural('lesson', $module->lessons->count()) }}@if($moduleQuizCount) · {{ $moduleQuizCount }} {{ \Illuminate\Support\Str::plural('quiz', $moduleQuizCount) }}@endif
                                    </span>
                                </span>
                                <flux:icon.chevron-down class="size-4 shrink-0 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
                            </button>

                            <div x-show="open" x-collapse x-cloak class="border-t border-gray-100 dark:border-zinc-800">
                                @if($module->description)
                                    <p class="px-4 pt-3 text-sm text-gray-600 dark:text-zinc-400">{{ $module->description }}</p>
                                @endif

                                @if($module->lessons->isEmpty())
                                    <p class="px-4 py-3 text-sm text-gray-500 dark:text-zinc-400">No lessons in this module yet.</p>
                                @else
                                    <ul class="divide-y divide-gray-100 py-1 dark:divide-zinc-800">
                                        @foreach($visibleLessons as $lesson)
                                            @php
                                                $lessonUrl = $canManage || auth()->user()?->isTeacher()
                                                    ? route('lessons.view', $lesson->id)
                                                    : ($enrolled ? route('courses.learn', $course) : null);
                                                $icon = match ($lesson->lesson_type) {
                                                    'video' => 'play-circle',
                                                    'interactive' => 'cursor-arrow-rays',
                                                    'scratch' => 'puzzle-piece',
                                                    'quiz' => 'clipboard-document-check',
                                                    default => 'document-text',
                                                };
                                            @endphp
                                            <li class="px-4">
                                                <div class="flex items-center gap-3 py-2.5">
                                                    <flux:icon :name="$icon" class="size-4 shrink-0 text-gray-400" />
                                                    @if($lessonUrl)
                                                        <a href="{{ $lessonUrl }}" wire:navigate class="min-w-0 flex-1 truncate text-sm text-gray-800 hover:text-orange-600 dark:text-zinc-200">{{ $lesson->title }}</a>
                                                    @else
                                                        <span class="min-w-0 flex-1 truncate text-sm text-gray-800 dark:text-zinc-200">{{ $lesson->title }}</span>
                                                    @endif
                                                    @if($canManage && $lesson->is_locked)
                                                        <flux:icon.lock-closed class="size-3.5 shrink-0 text-amber-500" title="Locked for students" />
                                                    @endif
                                                    @if($lesson->duration_minutes)
                                                        <span class="shrink-0 text-xs text-gray-400">{{ $lesson->duration_minutes }} min</span>
                                                    @endif
                                                </div>
                                                @foreach(($quizzesByLesson[$lesson->id] ?? collect()) as $quiz)
                                                    <div class="mb-2 ml-7 flex items-center gap-2 rounded-lg bg-orange-50/70 px-3 py-1.5 text-xs text-orange-800 dark:bg-orange-900/15 dark:text-orange-300">
                                                        <flux:icon.clipboard-document-check class="size-3.5 shrink-0" />
                                                        @if($canManage)
                                                            <a href="{{ route('assessments.show', $quiz->id) }}" wire:navigate class="truncate hover:underline">{{ $quiz->title }}</a>
                                                        @else
                                                            <span class="truncate">{{ $quiz->title }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if(! $showAllLessons && $module->lessons->count() > 2)
                                        <p class="flex items-center gap-2 border-t border-gray-100 px-4 py-2.5 text-xs text-gray-500 dark:border-zinc-800 dark:text-zinc-400">
                                            <flux:icon.lock-closed class="size-3.5" /> {{ $module->lessons->count() - 2 }} more {{ \Illuminate\Support\Str::plural('lesson', $module->lessons->count() - 2) }} after you enroll
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if(($quizzesByLesson[0] ?? collect())->isNotEmpty())
                <div class="mt-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Course quizzes</h3>
                    <ul class="mt-2 space-y-1.5">
                        @foreach($quizzesByLesson[0] as $quiz)
                            <li class="flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                                <flux:icon.clipboard-document-check class="size-4 shrink-0 text-orange-500" />
                                @if($canManage)
                                    <a href="{{ route('assessments.show', $quiz->id) }}" wire:navigate class="truncate hover:text-orange-600">{{ $quiz->title }}</a>
                                @else
                                    <span class="truncate">{{ $quiz->title }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- About --}}
        <aside class="space-y-4 lg:pt-10">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">About this course</h2>
                @if($course->description)
                    <div x-data="{ more: false }" class="mt-2">
                        <p class="whitespace-pre-line text-sm leading-relaxed text-gray-600 dark:text-zinc-400" :class="more ? '' : 'line-clamp-5'">{{ $course->description }}</p>
                        @if(mb_strlen($course->description) > 280)
                            <button type="button" @click="more = ! more" class="mt-1 text-xs font-medium text-orange-600 hover:underline" x-text="more ? 'Show less' : 'Read more'">Read more</button>
                        @endif
                    </div>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400">No description yet.</p>
                @endif

                @if(count($course->what_you_learn ?? []) > 0)
                    <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">You will learn</h3>
                    <ul class="mt-2 space-y-1.5">
                        @foreach($course->what_you_learn as $outcome)
                            <li class="flex gap-2 text-sm text-gray-700 dark:text-zinc-300">
                                <flux:icon.check class="mt-0.5 size-4 shrink-0 text-emerald-500" /> {{ $outcome }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(count($course->requirements ?? []) > 0)
                    <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">You will need</h3>
                    <ul class="mt-2 space-y-1.5">
                        @foreach($course->requirements as $requirement)
                            <li class="flex gap-2 text-sm text-gray-700 dark:text-zinc-300">
                                <span class="mt-2 size-1.5 shrink-0 rounded-full bg-gray-400"></span> {{ $requirement }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(count($course->tags ?? []) > 0)
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach($course->tags as $tag)
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">#{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
