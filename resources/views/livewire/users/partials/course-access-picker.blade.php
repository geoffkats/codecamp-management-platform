@if($showsCourseAccess)
    <div class="rounded-lg border border-blue-200 bg-white dark:border-blue-900/40 dark:bg-zinc-900">
        <div class="border-b border-blue-100 bg-blue-50 px-4 py-3 dark:border-blue-900/30 dark:bg-blue-950/20">
            <h2 class="text-xs font-bold uppercase tracking-wide text-blue-800 dark:text-blue-300">Course access</h2>
            <p class="mt-0.5 text-xs text-blue-700/80 dark:text-blue-400/80">
                Assign courses so this user can open lessons with the same learner interface students use.
            </p>
        </div>
        <div class="space-y-3 p-4">
            <flux:input wire:model.live.debounce.300ms="courseSearch" placeholder="Search courses…" />

            @if($availableCourses->isEmpty())
                <p class="text-xs text-slate-500">No courses match your search.</p>
            @else
                <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-slate-100 p-2 dark:border-zinc-800">
                    @foreach($availableCourses as $course)
                        <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-slate-50 dark:hover:bg-zinc-800/50
                            {{ in_array($course->id, $selectedCourseIds) ? 'bg-blue-50 dark:bg-blue-950/20' : '' }}">
                            <input type="checkbox" wire:model="selectedCourseIds" value="{{ $course->id }}"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $course->title }}</p>
                            </div>
                            <span class="flex-shrink-0 rounded px-1.5 py-0.5 text-xs font-medium
                                {{ $course->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $course->is_published ? 'Live' : 'Draft' }}
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif

            <p class="text-xs text-slate-500">
                {{ count($selectedCourseIds) }} course{{ count($selectedCourseIds) === 1 ? '' : 's' }} selected.
                They can browse these from <span class="font-medium text-blue-600">My Enrollments</span> and open any lesson.
            </p>
        </div>
    </div>
@endif
