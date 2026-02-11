<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('students.index') }}" wire:navigate class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ICT Student Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $student->student_id }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('students.print-credentials', $student->id) }}" target="_blank" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Print Credentials
                </a>
                <a href="{{ route('students.edit-ict', $student->id) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Edit Profile
                </a>
                <a href="{{ route('students.teacher-update', $student->id) }}" wire:navigate class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Teacher Update
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="flex justify-end gap-3">
            <a href="{{ route('test-marks.index', ['student' => $student->id]) }}" wire:navigate class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                Enter Test Marks
            </a>
            <a href="{{ route('icdl-exam-marks.index', ['student' => $student->id]) }}" wire:navigate class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                ICDL Exam Marks
            </a>
        </div>
        @if (session()->has('message'))
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-blue-800 dark:text-blue-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Summary Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Student Summary</h2>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-6 mb-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($student->full_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h3>
                        <div class="flex flex-wrap gap-3 mt-2">
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-full">
                                {{ $student->class_grade ?? 'No Class' }}
                            </span>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm rounded-full">
                                {{ ucfirst($student->gender ?? 'N/A') }}
                            </span>
                            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm rounded-full">
                                {{ str_replace('_', ' ', $student->exam_readiness_status ?? 'not_ready') }}
                            </span>
                            <span class="px-3 py-1 {{ $student->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }} text-sm rounded-full">
                                {{ $student->is_active ? 'Active' : 'Removed' }}
                            </span>
                            @if($student->school)
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm rounded-full">
                                    {{ $student->school->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Date of Birth</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nationality</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->nationality ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->user?->email ?: $student->student_id }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrolled Modules --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Enrolled Modules</h2>
            </div>
            <div class="p-6">
                @if($student->user->enrollments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($student->user->enrollments as $enrollment)
                            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Status: <span class="font-medium">{{ ucfirst($enrollment->status) }}</span></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Progress: <span class="font-medium">{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</span></p>
                                @if($enrollment->enrolled_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enrolled: {{ $enrollment->enrolled_at->format('M j, Y') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">No modules enrolled yet.</p>
                @endif
            </div>
        </div>

        {{-- Internal Tests --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Internal Tests</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">No internal test records available.</p>
            </div>
        </div>
    </div>
</div>
