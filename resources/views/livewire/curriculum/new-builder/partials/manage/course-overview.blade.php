{{-- Course Overview --}}
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Course Settings</h2>
            <div class="flex flex-wrap items-center gap-3">
                @if($course->trashed())
                    <span class="text-sm px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                        Archived {{ optional($course->deleted_at)->diffForHumans() }}
                    </span>
                    @if($this->courseRestoreBy)
                        <span class="text-xs text-gray-600 dark:text-gray-300">Restore by {{ $this->courseRestoreBy->format('M d, Y') }}</span>
                    @endif
                    <button wire:click="restoreCourse"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow">
                        Restore Course
                    </button>
                @else
                    <button wire:click="deleteCourse"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow"
                            title="Archive course (soft delete, restorable within {{ $this->restoreWindowDays }} days)">
                        Archive Course
                    </button>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $this->courseStats['modules'] ?? 0 }}</div>
                <div class="text-gray-600 dark:text-gray-400">Modules (including archived)</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $this->courseStats['lessons'] ?? 0 }}</div>
                <div class="text-gray-600 dark:text-gray-400">Lessons (including archived)</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $this->courseStats['assessments'] ?? 0 }}</div>
                <div class="text-gray-600 dark:text-gray-400">Assessments</div>
            </div>
        </div>

        {{-- Collaborators Section --}}
        @if($this->canManageCollaborators)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                <livewire:course.manage-collaborators :course="$course" :key="'collaborators-'.$course->id" />
            </div>
        @endif

        {{-- Approval Actions (Admin/Supervisor Only) --}}
        @if($this->isApprover && $course->approval_status !== 'approved')
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Course Approval</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    This course is currently in <span class="font-semibold">{{ ucfirst($course->approval_status) }}</span> status.
                </p>
                <div class="flex gap-3">
                    <button wire:click="approveCourse"
                            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        ✓ Approve Course
                    </button>
                    <button wire:click="rejectCourse"
                            class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">
                        ✗ Reject Course
                    </button>
                </div>
            </div>
        @elseif($this->isApprover && $course->approval_status === 'approved')
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold text-green-900 dark:text-green-100 mb-2">✓ Course Approved</h3>
                <p class="text-sm text-green-700 dark:text-green-300">
                    This course has been approved and is published.
                </p>
            </div>
        @endif

        {{-- Content Lock Management --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                Content Lock Management
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Control student access using lesson and quiz settings. Locking is managed in the lesson form and assessment editor.
            </p>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lessons</div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->contentLockStats['locked_lessons'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                            </svg>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->contentLockStats['unlocked_lessons'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quizzes</div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->contentLockStats['locked_assessments'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                            </svg>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->contentLockStats['unlocked_assessments'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Tip:</strong> Hover over any lesson or quiz in the left sidebar to see the lock/unlock button.
                </p>
            </div>
        </div>

        {{-- Archive & Restore --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Archive & Restore</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Manage modules and lessons here</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Active Items</h4>
                    @if($this->activeModules->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">No active modules.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($this->activeModules as $module)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-900">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $module->title }}</span>
                                        @if($canManageCourse)
                                            <button type="button"
                                                    wire:click="deleteModule({{ $module->id }})"
                                                    wire:confirm="Archive this module and all of its lessons?"
                                                    class="text-xs font-semibold text-red-600 hover:text-red-700">
                                                Archive Module
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                    class="text-xs font-semibold text-gray-400 cursor-not-allowed">
                                                Archive Module
                                            </button>
                                        @endif
                                    </div>
                                    <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                        @foreach(($this->activeLessonsByModule[$module->id] ?? collect()) as $lesson)
                                            <div class="flex items-center justify-between px-3 py-2 text-sm">
                                                <span class="text-gray-700 dark:text-gray-200">{{ $lesson->title }}</span>
                                                @if($canManageCourse)
                                                    <button type="button"
                                                            wire:click="deleteLesson({{ $lesson->id }})"
                                                            wire:confirm="Archive this lesson?"
                                                            class="text-xs font-semibold text-red-600 hover:text-red-700">
                                                        Archive Lesson
                                                    </button>
                                                @else
                                                    <button type="button" disabled
                                                            class="text-xs font-semibold text-gray-400 cursor-not-allowed">
                                                        Archive Lesson
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if(($this->activeLessonsByModule[$module->id] ?? collect())->isEmpty())
                                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No lessons in this module.</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Archived Items</h4>
                    @if($this->archivedStructureModules->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">No archived modules or lessons.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($this->archivedStructureModules as $module)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-900">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $module->title }}</span>
                                        @if($this->archivedModuleFlags[$module->id] ?? false)
                                            @if($canManageCourse)
                                                <button type="button"
                                                        wire:click="restoreModule({{ $module->id }})"
                                                        class="text-xs font-semibold text-green-600 hover:text-green-700">
                                                    Restore Module
                                                </button>
                                            @else
                                                <button type="button" disabled
                                                        class="text-xs font-semibold text-gray-400 cursor-not-allowed">
                                                    Restore Module
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                        @foreach(($this->archivedLessonsByModule[$module->id] ?? collect()) as $lesson)
                                            <div class="flex items-center justify-between px-3 py-2 text-sm">
                                                <span class="text-gray-700 dark:text-gray-200">{{ $lesson->title }}</span>
                                                @if($canManageCourse)
                                                    <button type="button"
                                                            wire:click="restoreLesson({{ $lesson->id }})"
                                                            class="text-xs font-semibold text-green-600 hover:text-green-700">
                                                        Restore Lesson
                                                    </button>
                                                @else
                                                    <button type="button" disabled
                                                            class="text-xs font-semibold text-gray-400 cursor-not-allowed">
                                                        Restore Lesson
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if(($this->archivedLessonsByModule[$module->id] ?? collect())->isEmpty())
                                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No archived lessons in this module.</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if(!$this->canManageCourse)
                <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">You do not have permission to archive or restore items.</p>
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('courses.edit', $course->id) }}" wire:navigate
                   class="block w-full px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
                    Edit Course Details
                </a>
            </div>
        </div>
    </div>
</div>
