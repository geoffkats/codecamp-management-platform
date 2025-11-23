<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Hero Header --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 text-white">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h1 class="text-4xl font-bold">Start a Discussion</h1>
                    </div>
                    <p class="text-blue-100 text-lg">Share your thoughts, ask questions, or start a conversation</p>
                </div>
                <flux:button href="{{ route('discussions.index') }}" icon="arrow-left" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30" wire:navigate>
                    Back
                </flux:button>
            </div>
        </div>

        {{-- Main Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form wire:submit="save" class="p-8">
                <div class="space-y-6">
                    {{-- Title Section --}}
                    <div>
                        <flux:label for="title" value="Discussion Title" required />
                        <flux:input 
                            id="title" 
                            wire:model="title" 
                            placeholder="e.g., How to implement authentication in Laravel?" 
                            class="text-lg"
                        />
                        <flux:error name="title" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Write a clear, descriptive title that summarizes your discussion</p>
                    </div>

                    {{-- Category and Course Selection --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label for="category" value="Category" required />
                            <flux:select id="category" wire:model="category">
                                <option value="general">General Discussion</option>
                                <option value="question">Question</option>
                                <option value="help">Help Request</option>
                                <option value="announcement">Announcement</option>
                                <option value="project">Project Share</option>
                                <option value="feedback">Feedback</option>
                            </flux:select>
                            <flux:error name="category" />
                        </div>

                        <div>
                            <flux:label for="courseId" value="Related Course">
                                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </flux:label>
                            <flux:select id="courseId" wire:model.live="courseId">
                                <option value="">@if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))General Conversation (No Course)@elseSelect a course...@endif</option>
                                @forelse($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @empty
                                    <option value="" disabled>No courses available</option>
                                @endforelse
                            </flux:select>
                            <flux:error name="courseId" />
                            @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">💬 Staff members can create conversations without selecting a course to start discussions with students</p>
                            @else
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select the course this discussion relates to</p>
                            @endif
                        </div>
                    </div>

                    {{-- Lesson Selection (if course is selected) --}}
                    @if($courseId && $lessons->count() > 0)
                        <div>
                            <flux:label for="lessonId" value="Related Lesson (Optional)" />
                            <flux:select id="lessonId" wire:model="lessonId">
                                <option value="">No specific lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="lessonId" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select a specific lesson if this discussion relates to one</p>
                        </div>
                    @elseif($courseId)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p class="text-sm text-blue-700 dark:text-blue-300">ℹ️ No lessons available for this course yet.</p>
                        </div>
                    @endif

                    {{-- Tags Section --}}
                    <div>
                        <flux:label value="Tags (Optional)" />
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <flux:input 
                                    wire:model="tagInput" 
                                    placeholder="Add a tag and press Enter..."
                                    wire:keydown.enter.prevent="addTag"
                                    maxlength="20"
                                />
                                <flux:button type="button" wire:click="addTag" icon="plus" :disabled="empty($tagInput) || count($tags) >= 5">
                                    Add
                                </flux:button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Add up to 5 tags to help others find your discussion. Press Enter to add.</p>
                            
                            @if(count($tags) > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($tags as $index => $tag)
                                        <flux:badge color="blue" class="cursor-pointer group">
                                            <span>{{ $tag }}</span>
                                            <button 
                                                type="button" 
                                                wire:click="removeTag({{ $index }})" 
                                                class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <flux:error name="tags" />
                    </div>

                    {{-- Content Editor --}}
<div>
                        <flux:label for="content" value="Discussion Content" required />
                        <flux:textarea 
                            id="content" 
                            wire:model="content" 
                            placeholder="Write your discussion here... Be clear and detailed so others can help or participate effectively..."
                            rows="15"
                            class="font-mono text-sm"
                        />
                        <flux:error name="content" />
                        <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Markdown is supported. Use **bold**, *italic*, `code`, and more.</span>
                            <span wire:ignore>{{ strlen($content ?? '') }} characters</span>
                        </div>
                    </div>

                    {{-- Rich Content Options --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Add Rich Content (Optional)
                        </h3>

                        {{-- Subject Tag --}}
                        <div>
                            <flux:label for="subjectTag" value="Subject Tag" />
                            <flux:select id="subjectTag" wire:model="subjectTag">
                                <option value="">No specific subject</option>
                                <option value="scratch">🟦 Scratch</option>
                                <option value="python">🐍 Python</option>
                                <option value="web">🌐 Web Development</option>
                                <option value="javascript">⚡ JavaScript</option>
                            </flux:select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Help others find your discussion by subject</p>
                        </div>

                        {{-- Scratch Project ID --}}
                        <div>
                            <flux:label for="scratchProjectId" value="Scratch Project ID" />
                            <flux:input 
                                id="scratchProjectId" 
                                wire:model="scratchProjectId"
                                placeholder="e.g., 987654321"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Get the ID from your Scratch project URL: scratch.mit.edu/projects/<strong>[ID]</strong>
                            </p>
                        </div>

                        {{-- Code Snippet --}}
                        <div class="space-y-3">
                            <flux:label value="Add Code Snippet" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <flux:label for="codeLanguage" value="Language" />
                                    <flux:select id="codeLanguage" wire:model="codeLanguage">
                                        <option value="">Select Language</option>
                                        <option value="python">Python</option>
                                        <option value="javascript">JavaScript</option>
                                        <option value="html">HTML</option>
                                        <option value="css">CSS</option>
                                        <option value="php">PHP</option>
                                        <option value="sql">SQL</option>
                                    </flux:select>
                                </div>
                                <div>
                                    <flux:label for="codeTitle" value="Title (Optional)" />
                                    <flux:input 
                                        id="codeTitle" 
                                        wire:model="codeTitle"
                                        placeholder="e.g., My Loop Code"
                                    />
                                </div>
                            </div>
                            <flux:textarea 
                                wire:model="codeContent"
                                rows="8"
                                placeholder="Paste your code here..."
                                class="font-mono text-sm"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400">Share code to get help or show your work</p>
                        </div>

                        {{-- Image Upload --}}
                        <div>
                            <flux:label value="Upload Images" />
                            <x-image-uploader 
                                wireModel="images"
                                :maxFiles="5"
                                :maxSize="5120"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload up to 5 images (max 5MB each)</p>
                        </div>
                    </div>

                    {{-- Advanced Options --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Discussion Settings
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <flux:checkbox id="allowReplies" wire:model="allowReplies" />
                                <div class="flex-1">
                                    <flux:label for="allowReplies" value="Allow Replies" class="font-semibold" />
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Others can reply to this discussion</p>
                                </div>
                            </div>

                            @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <flux:checkbox id="isPinned" wire:model="isPinned" />
                                    <div class="flex-1">
                                        <flux:label for="isPinned" value="Pin Discussion" class="font-semibold" />
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Pin this discussion to the top</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <flux:checkbox id="notifySubscribers" wire:model="notifySubscribers" />
                                <div class="flex-1">
                                    <flux:label for="notifySubscribers" value="Notify Subscribers" class="font-semibold" />
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Send notifications to course participants</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Section (Optional) --}}
                    @if(strlen($title) > 0 && strlen($content) > 10)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview</h3>
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $title }}</h4>
                                <div class="flex items-center gap-2 mb-3">
                                    <flux:badge size="sm" color="purple">{{ ucfirst($category) }}</flux:badge>
                                    @if(count($tags) > 0)
                                        @foreach(array_slice($tags, 0, 3) as $tag)
                                            <flux:badge size="sm" color="blue">{{ $tag }}</flux:badge>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                    <p>{{ Str::limit($content, 200) }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold">💡 Tip:</span> Be specific and clear in your discussion to get better responses!
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:button type="button" href="{{ route('discussions.index') }}" variant="ghost" wire:navigate>
                                Cancel
                            </flux:button>
                            <flux:button type="submit" icon="paper-airplane" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">Create Discussion</span>
                                <span wire:loading wire:target="save">Creating...</span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if (session()->has('message'))
            <div class="fixed bottom-4 right-4 z-50 animate-fade-in">
                <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 max-w-md">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Success</p>
                        <p class="text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="fixed bottom-4 right-4 z-50 animate-fade-in">
                <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 max-w-md">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Error</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
