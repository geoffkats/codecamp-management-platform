{{-- Lesson Editor — clean, minimal, teacher-first --}}
<div class="min-h-full bg-gray-50 dark:bg-gray-950">

    @if(!$course)
        <div class="p-8">
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <p class="text-red-800 dark:text-red-200 text-sm">Course not loaded. Please go back and select a course.</p>
            </div>
        </div>
    @else

    <form wire:submit.prevent="saveLesson" class="flex flex-col h-full">

        {{-- ── Top bar ─────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
            <button type="button" wire:click="closeForm"
                    class="flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Course
            </button>
            <span class="text-gray-300 dark:text-gray-700">/</span>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 truncate">
                {{ $selectedId ? ($formData['title'] ?: 'Edit Lesson') : 'New Lesson' }}
            </span>

            <div class="ml-auto flex items-center gap-2">
                {{-- Approval badge --}}
                @if($selectedId && isset($formData['approval_status']))
                    @php $status = $formData['approval_status'] ?? 'draft'; @endphp
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ $status === 'approved' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' :
                           ($status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' :
                           ($status === 'rejected' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' :
                           'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400')) }}">
                        {{ ucfirst($status) }}
                    </span>
                @endif

                {{-- Save button --}}
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-5 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white rounded-xl transition-colors shadow-sm disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveLesson">
                        {{ $selectedId ? 'Save Changes' : 'Create Lesson' }}
                    </span>
                    <span wire:loading wire:target="saveLesson">Saving…</span>
                </button>
            </div>
        </div>

        {{-- ── Main content ────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-3xl mx-auto px-6 py-8 space-y-6">

                {{-- Rejection notice --}}
                @if(($formData['approval_status'] ?? '') === 'rejected' && $lesson && $lesson->rejection_reason)
                    <div class="flex gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 dark:text-red-200">This lesson was rejected</p>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-0.5">{{ $lesson->rejection_reason }}</p>
                        </div>
                    </div>
                @endif

                {{-- Title --}}
                <div>
                    <input type="text"
                           wire:model="formData.title"
                           placeholder="Lesson title…"
                           class="w-full text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-gray-200 dark:border-gray-700 focus:border-orange-500 focus:outline-none focus:ring-0 pb-2 placeholder-gray-300 dark:placeholder-gray-600 transition-colors">
                    @error('formData.title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Module + Type row --}}
                <div class="flex flex-wrap gap-4">
                    {{-- Module --}}
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Module</label>
                        <select wire:model="formData.module_id"
                                class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                            <option value="">Select module…</option>
                            @foreach($course->modules->filter(fn($m) => !$m->trashed()) as $module)
                                <option value="{{ $module->id }}">{{ $module->title }}</option>
                            @endforeach
                        </select>
                        @error('formData.module_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Lesson Type</label>
                        <div class="flex gap-2">
                            @foreach(['text' => ['📝','Text'], 'video' => ['🎥','Video'], 'interactive' => ['💻','Interactive'], 'quiz' => ['✅','Quiz']] as $type => [$icon, $label])
                                <button type="button"
                                        wire:click="$set('formData.lesson_type', '{{ $type }}')"
                                        class="flex-1 flex flex-col items-center justify-center py-2 px-1 rounded-xl border-2 text-xs font-semibold transition-all
                                            {{ ($formData['lesson_type'] ?? 'text') === $type
                                                ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400'
                                                : 'border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                    <span class="text-base leading-none mb-0.5">{{ $icon }}</span>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Video URL (only for video type) --}}
                @if(($formData['lesson_type'] ?? 'text') === 'video')
                <div class="p-5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <label class="block text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">
                        🎥 Video URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url"
                           wire:model="formData.video_url"
                           placeholder="https://youtube.com/watch?v=... or direct video URL"
                           class="w-full px-4 py-2.5 text-sm border border-blue-200 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('formData.video_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                {{-- Interactive extra fields --}}
                @if(($formData['lesson_type'] ?? 'text') === 'interactive')
                <div class="p-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl space-y-4">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">💻 Interactive Lesson</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Scratch Project ID (optional)</label>
                        <input type="text"
                               wire:model="formData.scratch_project_id"
                               placeholder="e.g., 1234567890"
                               class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Step-by-Step Instructions (optional)</label>
                        <textarea wire:model="formData.lesson_steps_text"
                                  rows="4"
                                  placeholder="Step 1: Open your code editor&#10;Step 2: Create a new file&#10;Step 3: Write your code"
                                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors font-mono"></textarea>
                    </div>
                </div>
                @endif

                {{-- ── HTML Lesson Page ────────────────────────────────────────── --}}
                <div x-data="{
                        htmlFileName: null,
                        importHtml(event) {
                            const file = event.target.files[0];
                            if (!file) return;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.htmlFileName = file.name;
                                $wire.set('formData.html_content', e.target.result);
                                this.$nextTick(() => {
                                    if (this.$refs.newHtmlPreview) {
                                        this.$refs.newHtmlPreview.srcdoc = e.target.result;
                                    }
                                });
                            };
                            reader.readAsText(file);
                            event.target.value = '';
                        },
                        removeHtml() {
                            this.htmlFileName = null;
                            $wire.set('formData.html_content', null);
                        }
                    }" class="space-y-4">

                    {{-- Upload card --}}
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">HTML Lesson Page</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Upload an HTML file — displays with full styling for students</p>
                            </div>
                            @if(!empty($formData['html_content']))
                                <span class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold rounded-full">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    HTML saved
                                </span>
                            @endif
                        </div>

                        <div class="px-5 py-4 space-y-3">
                            {{-- Existing file row --}}
                            @if(!empty($formData['html_content']))
                                <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300 font-medium">HTML lesson page saved</span>
                                    <button type="button"
                                            x-on:click="removeHtml()"
                                            class="flex-shrink-0 text-xs text-red-500 hover:text-red-700 font-semibold transition-colors">
                                        Remove
                                    </button>
                                </div>
                            @endif

                            {{-- File input --}}
                            <div>
                                <label class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/40 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    {{ !empty($formData['html_content']) ? 'Replace HTML File' : 'Upload HTML File' }}
                                    <input type="file" accept=".html,.htm" class="sr-only" x-on:change="importHtml($event)">
                                </label>
                                <span x-show="htmlFileName" x-text="'— ' + htmlFileName" class="ml-2 text-sm text-gray-600 dark:text-gray-400"></span>
                                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">HTML only · Exports from Word, Google Docs, or PowerPoint as "Web Page" work well</p>
                            </div>
                        </div>
                    </div>

                    {{-- Saved HTML preview --}}
                    @if(!empty($formData['html_content']))
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">HTML Preview</span>
                                </div>
                            </div>
                            <iframe srcdoc="{{ htmlspecialchars($formData['html_content'] ?? '') }}"
                                    sandbox=""
                                    class="w-full border-0 block"
                                    style="height: 700px;"
                                    title="HTML lesson preview"></iframe>
                        </div>
                    @endif

                    {{-- New upload preview (before saving) --}}
                    <div x-show="htmlFileName" x-cloak
                         class="bg-white dark:bg-gray-900 rounded-xl border border-blue-200 dark:border-blue-800 overflow-hidden">
                        <div class="flex items-center gap-2 px-5 py-3 border-b border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">New file preview</span>
                            <span class="text-xs text-blue-600 dark:text-blue-400" x-text="'— ' + htmlFileName"></span>
                            <span class="ml-auto text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 px-2 py-0.5 rounded-full">Unsaved</span>
                        </div>
                        <iframe x-ref="newHtmlPreview"
                                sandbox=""
                                class="w-full border-0 block"
                                style="height: 700px;"
                                title="New HTML preview"></iframe>
                    </div>

                </div>{{-- /HTML upload --}}

                {{-- ── Lesson content editor (always visible) ──────────────────── --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Lesson Content</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Write notes, instructions, and lesson text students will see</p>
                        </div>
                    </div>

                    <div class="lesson-tiptap-host bg-white dark:bg-gray-800" wire:ignore>
                        <div class="lesson-tiptap-mount min-h-[420px]"></div>
                        <textarea class="lesson-content-editor hidden" aria-hidden="true">{{ $formData['content'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- Quizzes --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Quizzes & Assessments</h3>
                        </div>
                        @if($selectedId && $canManageCourse)
                            {{-- Browser event hits NewBuilder directly so we don't upload the full lesson formData --}}
                            <button type="button"
                                    x-data
                                    x-on:click="$dispatch('select-item', { type: 'assessment', id: null, parentId: {{ (int) $selectedId }} })"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Quiz
                            </button>
                        @endif
                    </div>

                    <div class="px-5 py-3">
                        @if(!$selectedId)
                            <p class="text-sm text-gray-400 dark:text-gray-500 py-2">Save the lesson first, then you can add quizzes.</p>
                        @elseif($selectedLessonAssessments->isEmpty())
                            <p class="text-sm text-gray-400 dark:text-gray-500 py-2">No quizzes yet.
                                @if($canManageCourse)
                                    <button type="button"
                                            x-data
                                            x-on:click="$dispatch('select-item', { type: 'assessment', id: null, parentId: {{ (int) $selectedId }} })"
                                            class="font-semibold text-orange-600 dark:text-orange-400 hover:underline ml-1">Add one →</button>
                                @endif
                            </p>
                        @else
                            <div class="space-y-2 py-1">
                                @foreach($selectedLessonAssessments as $assessment)
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 hover:border-orange-200 dark:hover:border-orange-800 transition-colors bg-gray-50 dark:bg-gray-800/50">
                                        <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="flex-1 text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $assessment->title }}</span>
                                        <button type="button"
                                                x-data
                                                x-on:click="$dispatch('select-item', { type: 'assessment', id: {{ (int) $assessment->id }}, parentId: null })"
                                                class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-200 px-2.5 py-1 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                            Edit
                                        </button>
                                        @if($canManageCourse)
                                            <button type="button"
                                                    wire:click="deleteAssessment({{ $assessment->id }})"
                                                    wire:confirm="Remove this quiz?"
                                                    class="text-xs font-semibold text-red-500 dark:text-red-400 px-2.5 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Advanced Settings (collapsed) ──────────────────── --}}
                <div x-data="{ open: false }" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <button type="button" @click="open = !open"
                            class="flex items-center justify-between w-full px-5 py-4 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Advanced Settings</span>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="px-5 pb-5 space-y-5 border-t border-gray-100 dark:border-gray-800 pt-5">

                        {{-- Summary --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Short Summary</label>
                            <textarea wire:model="formData.summary" rows="2"
                                      placeholder="Brief description (shown in lesson previews)"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"></textarea>
                        </div>

                        {{-- Learning Objectives --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Learning Objectives</label>
                            <textarea wire:model="formData.objectives" rows="4"
                                      placeholder="• Students will be able to…&#10;• Students will understand…"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors font-mono"></textarea>
                        </div>

                        {{-- Duration + Difficulty --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Duration (minutes)</label>
                                <input type="number" wire:model="formData.duration_minutes" min="1"
                                       placeholder="30"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Difficulty</label>
                                <select wire:model="formData.difficulty_level"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>

                        {{-- Visibility toggles --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Visibility</label>
                            <div class="space-y-3">
                                @foreach(['is_published' => 'Published (visible to students)', 'is_free_preview' => 'Free Preview (visible without enrolment)', 'is_locked' => 'Locked (requires prerequisite)'] as $field => $label)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <div class="relative flex-shrink-0">
                                            <input type="checkbox" wire:model="formData.{{ $field }}" class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer-checked:bg-orange-500 transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-4 shadow-sm"></div>
                                        </div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Approval actions (admin only) --}}
                        @if($isApprover && $selectedId && isset($formData['approval_status']))
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Approval</label>
                            @if(($formData['approval_status'] ?? '') !== 'approved')
                                <div class="flex gap-2">
                                    <button type="button" wire:click="approveLesson"
                                            class="flex-1 py-2 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors">
                                        Approve
                                    </button>
                                    <button type="button" wire:click="openRejectModal"
                                            class="flex-1 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors">
                                        Reject
                                    </button>
                                </div>
                            @else
                                <p class="text-sm text-green-600 dark:text-green-400 font-medium">✓ Approved</p>
                                <button type="button" wire:click="openRejectModal"
                                        class="mt-2 text-xs text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                                    Revoke approval
                                </button>
                            @endif
                        </div>
                        @elseif(!$isApprover && $selectedId)
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                            @if(($formData['approval_status'] ?? 'draft') === 'draft')
                                <button type="button" wire:click="submitForApproval"
                                        class="w-full py-2.5 text-sm font-semibold border border-orange-600 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-xl transition-colors">
                                    Submit for Approval
                                </button>
                            @elseif(($formData['approval_status'] ?? '') === 'pending')
                                <p class="text-sm text-amber-600 dark:text-amber-400 font-medium">⏳ Waiting for approval</p>
                            @endif
                        </div>
                        @endif

                        {{-- Archive --}}
                        @if($selectedId && $canManageCourse)
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                            <button type="button"
                                    wire:click="deleteLesson({{ $selectedId }})"
                                    wire:confirm="Archive this lesson? It will be hidden from students but can be restored from the Archived tab."
                                    class="w-full py-2.5 text-sm font-semibold border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                Archive lesson
                            </button>
                        </div>
                        @endif
                    </div>
                </div>{{-- /advanced settings --}}

                {{-- Bottom save button (for long pages) --}}
                <div class="flex items-center justify-end gap-3 py-2">
                    <button type="button" wire:click="closeForm"
                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 text-sm font-bold bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white rounded-xl transition-colors shadow-sm">
                        <span wire:loading.remove wire:target="saveLesson">{{ $selectedId ? 'Save Changes' : 'Create Lesson' }}</span>
                        <span wire:loading wire:target="saveLesson">Saving…</span>
                    </button>
                </div>

            </div>
        </div>

    </form>

    {{-- Rejection modal --}}
    @if($showRejectModal ?? false)
    <div class="fixed inset-0 bg-black/60 flex items-end sm:items-center justify-center z-50 p-4" wire:click.self="closeRejectModal">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-7">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                {{ isset($formData['approval_status']) && $formData['approval_status'] === 'approved' ? 'Revoke Approval' : 'Reject Lesson' }}
            </h3>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Reason <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="rejectionReason" rows="4"
                          placeholder="Explain what needs to be fixed…"
                          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm transition-colors"></textarea>
                @error('rejectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="closeRejectModal"
                        class="flex-1 py-2.5 text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="button" wire:click="disapproveLesson"
                        class="flex-1 py-2.5 text-sm font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
    @endif

    @endif
</div>
