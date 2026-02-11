<div class="p-6">
    @php
        $isIct = auth()->user()->isIctTeacher();
    @endphp
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $isIct ? 'ICT Students' : 'Students' }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $isIct ? 'ICT student list for your school' : 'Manage student information' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="openAssignModal" @disabled(count($selected) === 0) class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                {{ $isIct ? 'Enroll in Module' : 'Assign to Course' }}
            </button>
            @if($isIct)
                <button type="button" wire:click="printSelectedCredentials" @disabled(count($selected) === 0) class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    Print Selected (PDF)
                </button>
                <button type="button" wire:click="exportSelectedCsv" @disabled(count($selected) === 0) class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    Export Selected (Excel)
                </button>
            @endif
            <a href="{{ $isIct ? route('students.create-ict') : route('students.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                + Add Student
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-200">
            {{ session('message') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 {{ $isIct ? 'lg:grid-cols-4' : 'lg:grid-cols-5' }} gap-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, ID, or contact..." 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        
        <select wire:model.live="filterClass" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Classes</option>
            @foreach($classes as $class)
                <option value="{{ $class }}">{{ $class }}</option>
            @endforeach
        </select>

        @if($isIct)
            <select wire:model.live="filterModuleId" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Modules</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterReadiness" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Readiness</option>
                <option value="not_ready">Not Ready</option>
                <option value="student_requested">Requested Exam</option>
                <option value="teacher_approved">Exam Ready</option>
                <option value="needs_practice">Needs Practice</option>
                <option value="exam_completed">Exam Completed</option>
            </select>
        @else
            <select wire:model.live="filterEnrollment" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Enrollment Status</option>
                <option value="enrolled">Enrolled</option>
                <option value="not_enrolled">Not Enrolled</option>
            </select>

            <select wire:model.live="filterEnrollmentCourseId" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Any Course</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterCategory" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Categories</option>
                <option value="codecamp">Codecamp Student</option>
                <option value="school_club">School Club Student</option>
                <option value="ict_school">ICT School Student</option>
            </select>
        @endif
    </div>

    {{-- Students Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-visible">
        <div class="overflow-x-auto">
        <table class="w-full overflow-visible">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                    @if($isIct)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Modules Enrolled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Readiness</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Request</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Payment</th>
                    @else
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parent Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gadgets</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Uniform</th>
                    @endif
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 overflow-visible">
                @forelse($students as $student)
                @php
                    $enrollments = $student->user?->enrollments ?? collect();
                    $enrollmentCount = $enrollments->count();
                    $isEnrolled = $enrollmentCount > 0;
                    $avgProgress = $enrollmentCount > 0 ? round($enrollments->avg('progress_percentage'), 1) : 0;
                    $moduleTitles = $enrollments->pluck('course.title')->filter()->values();
                    $allModulesPassed = $enrollmentCount > 0 && $enrollments->every(fn($enrollment) => (float) $enrollment->progress_percentage >= 100);
                    $readinessLabel = match($student->exam_readiness_status ?? 'not_ready') {
                        'student_requested' => 'Requested Exam',
                        'teacher_approved' => 'Exam Ready',
                        'needs_practice' => 'Needs Practice',
                        'exam_completed' => 'Exam Completed',
                        default => 'Not Ready',
                    };
                    $readinessClass = match($student->exam_readiness_status ?? 'not_ready') {
                        'teacher_approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
                        'needs_practice' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200',
                        'student_requested' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200',
                        'exam_completed' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200',
                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200',
                    };
                @endphp
                <tr class="{{ $isEnrolled && !$isIct ? 'bg-green-50 dark:bg-green-900/10' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700 overflow-visible">
                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                        <input type="checkbox" value="{{ $student->id }}" wire:model.live="selected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $student->student_id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            {{ $student->full_name }}
                            @if(!$isIct && $isEnrolled)
                                <span class="px-2 py-0.5 text-xs font-semibold bg-green-600 text-white rounded-full">Enrolled ({{ $enrollmentCount }})</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->class_grade ?? 'N/A' }}</td>
                    @if($isIct)
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            @if($moduleTitles->isNotEmpty())
                                @php
                                    $firstModule = $moduleTitles->first();
                                    $extraCount = $moduleTitles->count() - 1;
                                    $moduleList = $moduleTitles->implode(', ');
                                @endphp
                                <div class="flex items-center gap-2" title="{{ $moduleList }}">
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 truncate max-w-[160px]">
                                        {{ $firstModule }}
                                    </span>
                                    @if($extraCount > 0)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">+{{ $extraCount }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">Not enrolled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $avgProgress }}%</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded {{ $readinessClass }}">
                                {{ $readinessLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ str_replace('_', ' ', $student->exam_request_status ?? 'not_requested') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ str_replace('_', ' ', $student->exam_payment_status ?? 'not_submitted') }}
                        </td>
                    @else
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            @php
                                $categoryLabel = match($student->student_category ?? 'codecamp') {
                                    'school_club' => 'School Club',
                                    'ict_school' => 'ICT School',
                                    default => 'Codecamp',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                {{ $categoryLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->parent_guardian_contact }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->gadgets->count() }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $student->uniform_paid ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200' }}">
                                {{ $student->uniform_paid ? 'Paid' : 'Pending' }}
                            </span>
                        </td>
                    @endif
                    <td class="px-6 py-4 text-right text-sm overflow-visible relative">
                        @if($isIct)
                            <div x-data="{ open: false }" class="inline-block">
                                <button type="button" @click="open = true" class="inline-flex items-center gap-2 px-2.5 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs">
                                    Actions
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak class="fixed inset-0 z-50">
                                    <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
                                    <div class="absolute inset-0 flex items-center justify-center p-4">
                                        <div class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Student Actions</h3>
                                                <button type="button" @click="open = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">✕</button>
                                            </div>
                                            <div class="p-4 space-y-2">
                                                <a href="{{ route('students.show', $student->id) }}" wire:navigate class="block w-full text-left px-3 py-2 text-sm rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">View</a>
                                                <a href="{{ route('students.print-credentials', $student->id) }}" target="_blank" class="block w-full text-left px-3 py-2 text-sm rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-900/50">Print Credentials</a>
                                                <a href="{{ route('students.edit-ict', $student->id) }}" wire:navigate class="block w-full text-left px-3 py-2 text-sm rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Edit</a>
                                                <button type="button" wire:click="markExamReady({{ $student->id }})" @click="open = false" onclick="return confirm('Mark student as ICDL Test Ready? Ensure internal tests are passed.')" class="w-full text-left px-3 py-2 text-sm rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-200 hover:bg-indigo-100 dark:hover:bg-indigo-900/50">Mark Ready</button>
                                                <button type="button" wire:click="markNeedsPractice({{ $student->id }})" @click="open = false" class="w-full text-left px-3 py-2 text-sm rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-200 hover:bg-orange-100 dark:hover:bg-orange-900/50">Needs Practice</button>
                                                <button type="button" wire:click="requestExamSession({{ $student->id }})" @click="open = false" onclick="return confirm('Request exam session for this student? Make sure they are marked ICDL Test Ready.')" class="w-full text-left px-3 py-2 text-sm rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-200 hover:bg-teal-100 dark:hover:bg-teal-900/50">Request Exam</button>
                                                <button type="button" wire:click="submitExamPayment({{ $student->id }})" @click="open = false" onclick="return confirm('Submit exam payment? Ensure admin has approved the exam request.')" class="w-full text-left px-3 py-2 text-sm rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-200 hover:bg-emerald-100 dark:hover:bg-emerald-900/50">Submit Exam Payment</button>
                                                <button type="button" wire:click="removeStudent({{ $student->id }})" @click="open = false" onclick="return confirm('Remove this student from active list?')" class="w-full text-left px-3 py-2 text-sm rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-200 hover:bg-red-100 dark:hover:bg-red-900/50">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-green-600 hover:text-green-700 dark:text-green-400">View</a>
                            <a href="{{ route('students.edit', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400">Edit</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isIct ? 10 : 9 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No students found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>

    @if($showAssignModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ $isIct ? 'Enroll in Module' : 'Assign to Course' }}</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $isIct ? 'Module' : 'Course' }}</label>
                    <select wire:model.live="selectedCourseId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select {{ $isIct ? 'a module' : 'a course' }}</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    @error('selectedCourseId')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="notifyStudents" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    Send notification to students
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" wire:click="closeAssignModal" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200">Cancel</button>
                <button type="button" wire:click="assignSelectedToCourse" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Assign</button>
            </div>
        </div>
    </div>
    @endif
</div>
