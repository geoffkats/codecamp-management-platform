<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $school->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">School code: {{ $school->code ?? '—' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.schools.students', $school->id) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">View Students</a>
                <a href="{{ route('admin.schools') }}" wire:navigate class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Back</a>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session()->has('message'))
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        <div class="flex gap-2">
            <button wire:click="setTab('details')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'details' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">Details</button>
            <button wire:click="setTab('teachers')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'teachers' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">ICT Teachers</button>
            <button wire:click="setTab('courses')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'courses' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">ICT Courses</button>
        </div>

        @if($activeTab === 'details')
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Name</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $school->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Code</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $school->code ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Address</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $school->address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assessment Performance</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Total Attempts</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($assessmentSummary['total_attempts'] ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Passed</span>
                            <span class="font-semibold text-emerald-600">{{ number_format($assessmentSummary['passed_attempts'] ?? 0) }}</span>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Pass Rate</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($assessmentSummary['pass_rate'] ?? 0, 1) }}%</span>
                            </div>
                            <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $assessmentSummary['pass_rate'] ?? 0)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Assessment Results</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Latest submissions</span>
                    </div>
                    @if($recentAssessmentResults->isEmpty())
                        <p class="text-sm text-gray-600 dark:text-gray-400">No assessment results recorded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($recentAssessmentResults as $attempt)
                                @php
                                    $assessment = $attempt->assessment;
                                    $maxScore = ($assessment?->questions && $assessment->questions->count() > 0)
                                        ? $assessment->questions->sum('points')
                                        : 100;
                                    $attemptScore = $attempt->score ?? 0;
                                    $percentage = $maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0;
                                @endphp
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $attempt->user?->name ?? 'Student' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $assessment?->title ?? 'Assessment' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $attempt->completed_at?->format('M j, Y') ?? '—' }}</p>
                                    </div>
                                    <div class="text-sm flex items-center gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($percentage, 1) }}%</span>
                                        <span class="px-2 py-1 rounded-full text-xs {{ $attempt->is_passed ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                            {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                                        </span>
                                        <a href="{{ route('assessments.results', ['assessment' => $assessment?->id, 'attempt' => $attempt->id]) }}" wire:navigate class="text-indigo-600 dark:text-indigo-300">View</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($activeTab === 'teachers')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assign ICT Teacher</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teacher *</label>
                            <select wire:model="teacherUserId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select ICT teacher</option>
                                @foreach($availableTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                                @endforeach
                            </select>
                            @error('teacherUserId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select wire:model="teacherStatus" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button wire:click="assignTeacher" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Assign Teacher</button>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned ICT Teachers</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Teacher</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($teacherAssignments as $assignment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $assignment->teacher->name }}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->teacher->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $assignment->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <button wire:click="toggleTeacherStatus({{ $assignment->id }})" class="text-blue-600 hover:text-blue-800">Toggle</button>
                                            <button wire:click="removeTeacher({{ $assignment->id }})" class="text-red-600 hover:text-red-800 ml-3">Remove</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No ICT teachers assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($teacherAssignments->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $teacherAssignments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($activeTab === 'courses')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assign ICT Course</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course *</label>
                            <select wire:model="courseId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select course</option>
                                @foreach($availableCourses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                            @error('courseId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="courseActive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            Enabled
                        </label>
                        <button wire:click="assignCourse" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Assign Course</button>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned ICT Courses</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($courseAssignments as $assignment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $assignment->course->title }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $assignment->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                                {{ $assignment->is_active ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <button wire:click="toggleCourseStatus({{ $assignment->id }})" class="text-blue-600 hover:text-blue-800">Toggle</button>
                                            <button wire:click="removeCourse({{ $assignment->id }})" class="text-red-600 hover:text-red-800 ml-3">Remove</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No ICT courses assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($courseAssignments->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $courseAssignments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
