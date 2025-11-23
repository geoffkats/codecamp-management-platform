<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Update Student Learning Profile</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $student->full_name }} ({{ $student->student_id }})</p>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Learning Accounts --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Learning Accounts</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Scratch Account</label>
                        <input type="text" wire:model="scratch_account" placeholder="Username" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        @error('scratch_account') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Scratch Password</label>
                        <input type="text" wire:model="scratch_password" placeholder="Password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        @error('scratch_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">GitHub Account</label>
                        <input type="text" wire:model="github_account" placeholder="GitHub username" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        @error('github_account') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Class Assignment --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Class Assignment</h2>
                </div>
                <div class="p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Class/Grade</label>
                        <input type="text" wire:model="class_grade" placeholder="e.g., Grade 5, Beginner Class" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        @error('class_grade') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Course Enrollment --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Course Enrollment</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Select courses to enroll this student</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($courses as $course)
                        @php
                            $isEnrolled = $student->user->enrollments->contains('course_id', $course->id);
                            $isPending = $student->user->invitations()->where('course_id', $course->id)->where('status', 'pending')->exists();
                        @endphp
                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <input type="checkbox" wire:model="selectedCourses" value="{{ $course->id }}" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $course->title }}</span>
                                    @if($isEnrolled)
                                        <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">Enrolled</span>
                                    @elseif($isPending)
                                        <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-full">Invitation Pending</span>
                                    @endif
                                </div>
                                @if($course->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($course->description, 100) }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('students.show', $student->id) }}" wire:navigate class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-lg hover:shadow-xl">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
