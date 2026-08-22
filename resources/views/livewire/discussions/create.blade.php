<div class="mx-auto max-w-3xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New discussion</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Follow the guidelines below so teachers and classmates can help you quickly.</p>
        </div>
        <flux:button href="{{ route('discussions.index') }}" icon="arrow-left" variant="ghost" wire:navigate>Back</flux:button>
    </div>

    @include('livewire.discussions.partials.guidelines', ['compact' => true])

    @if(isset($forumChallenges) && $forumChallenges->isNotEmpty())
        @include('livewire.discussions.partials.challenge-hint', [
            'challenges' => $forumChallenges,
            'progress' => $forumChallengeProgress ?? collect(),
        ])
    @endif

    <form wire:submit="save" class="rounded-lg border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        <div class="space-y-5">
            {{-- Category + course --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label for="category" value="Post type" required />
                    <flux:select id="category" wire:model.live="category">
                        <option value="question">Question — I need an answer</option>
                        <option value="help">Help — I'm stuck on something</option>
                        <option value="project">Project — sharing my work</option>
                        <option value="feedback">Feedback — about the course or platform</option>
                        @if($isStaff)
                            <option value="announcement">Announcement — staff only</option>
                        @endif
                        <option value="general">General — course-related chat</option>
                    </flux:select>
                    <flux:error name="category" />
                </div>

                <div>
                    <flux:label for="courseId" value="Course" @if(!$isStaff) required @endif />
                    <flux:select id="courseId" wire:model.live="courseId">
                        @if($isStaff)
                            <option value="">No specific course (staff)</option>
                        @else
                            <option value="">Select your course...</option>
                        @endif
                        @forelse($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @empty
                            <option value="" disabled>No courses available</option>
                        @endforelse
                    </flux:select>
                    <flux:error name="courseId" />
                    @unless($isStaff)
                        <p class="mt-1 text-xs text-slate-500">Required — pick the course this post is about.</p>
                    @endunless
                </div>
            </div>

            @if($courseId && $lessons->count() > 0)
                <div>
                    <flux:label for="lessonId" value="Lesson (optional)" />
                    <flux:select id="lessonId" wire:model="lessonId">
                        <option value="">Whole course / not sure</option>
                        @foreach($lessons as $lesson)
                            <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            {{-- Title --}}
            <div>
                <flux:label for="title" value="Title" required />
                <flux:input
                    id="title"
                    wire:model="title"
                    placeholder="@if(in_array($category, ['question', 'help']))e.g. My Python loop only runs once @else e.g. Week 3 website project submission @endif"
                />
                <flux:error name="title" />
                <p class="mt-1 text-xs text-slate-500">Describe the problem or topic in one line — not "help" or "urgent".</p>
            </div>

            {{-- Content --}}
            <div>
                <flux:label for="content" value="Details" required />
                <flux:textarea
                    id="content"
                    wire:model="content"
                    rows="10"
                    placeholder="@if(in_array($category, ['question', 'help']))
1. What are you trying to do?
2. What did you try?
3. What happened instead (error message, screenshot, etc.)?
@else
Share your thoughts clearly so others can respond...
@endif"
                />
                <flux:error name="content" />
                @unless($isStaff)
                    <p class="mt-1 text-xs text-slate-500">Minimum 40 characters. More detail = faster help.</p>
                @endunless
            </div>

            {{-- Optional extras --}}
            <details class="rounded-lg border border-slate-200 dark:border-slate-700">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    Add code, Scratch project, or images (optional)
                </summary>
                <div class="space-y-4 border-t border-slate-200 px-4 py-4 dark:border-slate-700">
                    <div>
                        <flux:label for="subjectTag" value="Subject" />
                        <flux:select id="subjectTag" wire:model="subjectTag">
                            <option value="">Not specific</option>
                            <option value="scratch">Scratch</option>
                            <option value="python">Python</option>
                            <option value="web">Web development</option>
                            <option value="javascript">JavaScript</option>
                        </flux:select>
                    </div>

                    <div>
                        <flux:label for="scratchProjectId" value="Scratch project ID" />
                        <flux:input id="scratchProjectId" wire:model="scratchProjectId" placeholder="From scratch.mit.edu/projects/123456789" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:label for="codeLanguage" value="Code language" />
                            <flux:select id="codeLanguage" wire:model="codeLanguage">
                                <option value="">None</option>
                                <option value="python">Python</option>
                                <option value="javascript">JavaScript</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                                <option value="php">PHP</option>
                                <option value="sql">SQL</option>
                            </flux:select>
                        </div>
                        <div>
                            <flux:label for="codeTitle" value="Code snippet title" />
                            <flux:input id="codeTitle" wire:model="codeTitle" placeholder="Optional label" />
                        </div>
                    </div>
                    <flux:textarea wire:model="codeContent" rows="6" placeholder="Paste code here..." class="font-mono text-sm" />

                    <div>
                        <flux:label value="Images" />
                        <x-image-uploader wireModel="images" :maxFiles="5" :maxSize="5120" />
                    </div>
                </div>
            </details>

            @if($isStaff)
                <label class="flex items-center gap-2 text-sm">
                    <flux:checkbox id="isPinned" wire:model="isPinned" />
                    <span>Pin this post to the top</span>
                </label>
            @endif

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <flux:button type="button" href="{{ route('discussions.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" icon="paper-airplane" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Publish post</span>
                    <span wire:loading wire:target="save">Publishing...</span>
                </flux:button>
            </div>
        </div>
    </form>

    @if (session()->has('error'))
        <div class="rounded-lg bg-red-600 px-4 py-3 text-white">{{ session('error') }}</div>
    @endif
</div>
