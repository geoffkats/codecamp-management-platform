@php
use Illuminate\Support\Str;
@endphp

<div class="flex flex-col gap-6 p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Submissions</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ auth()->user()->can('grade_submissions') ? 'Review and grade student submissions' : 'View your assignment submissions' }}
            </p>
        </div>
        @if(auth()->user()->can('grade_submissions'))
            <a href="{{ route('grades.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                View All Grades
            </a>
        @endif
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <button type="button" wire:click="$set('filter', 'all')" class="text-left bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/40 rounded-xl shadow-lg border {{ $filter === 'all' ? 'border-blue-500 ring-2 ring-blue-300' : 'border-blue-200 dark:border-blue-800' }} p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-500 dark:bg-blue-600 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Submissions</p>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['total'] }}</p>
                </div>
            </div>
        </button>

        <button type="button" wire:click="$set('filter', 'pending')" class="text-left bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/40 rounded-xl shadow-lg border {{ $filter === 'pending' ? 'border-orange-500 ring-2 ring-orange-300' : 'border-orange-200 dark:border-orange-800' }} p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-orange-500 dark:bg-orange-600 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-orange-700 dark:text-orange-300">Pending Grading</p>
                    <p class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </button>

        <button type="button" wire:click="$set('filter', 'graded')" class="text-left bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/40 rounded-xl shadow-lg border {{ $filter === 'graded' ? 'border-green-500 ring-2 ring-green-300' : 'border-green-200 dark:border-green-800' }} p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-green-500 dark:bg-green-600 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">Graded</p>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ $stats['graded'] }}</p>
                </div>
            </div>
        </button>

        @if($stats['overdue'] > 0)
            <button type="button" wire:click="$set('filter', 'overdue')" class="text-left bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/40 rounded-xl shadow-lg border {{ $filter === 'overdue' ? 'border-red-500 ring-2 ring-red-300' : 'border-red-200 dark:border-red-800' }} p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-red-500 dark:bg-red-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">Overdue</p>
                        <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ $stats['overdue'] }}</p>
                    </div>
                </div>
            </button>
        @else
            <button type="button" wire:click="$set('filter', 'overdue')" class="text-left bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-900/40 rounded-xl shadow-lg border {{ $filter === 'overdue' ? 'border-gray-500 ring-2 ring-gray-300' : 'border-gray-200 dark:border-gray-800' }} p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gray-400 dark:bg-gray-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Overdue</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">0</p>
                    </div>
                </div>
            </button>
        @endif
    </div>

    {{-- Filters and Search --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by assignment title or student name..." 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter Status</label>
                <select wire:model.live="filter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Status</option>
                    <option value="pending">Pending Grading</option>
                    <option value="graded">Graded</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Submission Type</label>
                <select wire:model.live="submissionType" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Types</option>
                    <option value="assignment">Assignments</option>
                    <option value="assessment">Assessments</option>
                </select>
            </div>
        </div>

        @if($courses->count() > 0)
            <div class="mt-4 flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Course</label>
                    <select wire:model.live="courseId" class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                @if(($showCampFilter ?? true) && $camps->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Camp</label>
                    <select wire:model.live="campId" class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Camps</option>
                        @foreach($camps as $camp)
                            <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Submissions List --}}
    @if($submissions->count() > 0)
        {{-- Sort Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-4 text-sm font-medium text-gray-600 dark:text-gray-400">
                <span>Sort by:</span>
                <button wire:click="sort('submitted_at')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Date
                    @if($sortBy === 'submitted_at')
                        <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button wire:click="sort('title')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Title
                    @if($sortBy === 'title')
                        <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
                <button wire:click="sort('due_date')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Due Date
                    @if($sortBy === 'due_date')
                        <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($submissions as $sub)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                {{-- Header with badges --}}
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap mb-2">
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ $sub['title'] }}
                                            </h3>
                                            @if($sub['type'] === 'assessment')
                                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                                    Assessment
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                                    Assignment
                                                </span>
                                            @endif
                                            
                                            @if(!$sub['graded_at'] && $sub['status'] !== 'draft')
                                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full">
                                                    Pending
                                                </span>
                                            @elseif($sub['graded_at'])
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                                    Graded
                                                </span>
                                            @endif

                                            @if($sub['due_date'] && $sub['due_date']->isPast() && !$sub['graded_at'] && $sub['status'] === 'submitted')
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full">
                                                    Overdue
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Course and Student Info --}}
                                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3 flex-wrap">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                <span class="font-medium">{{ $sub['course']->title ?? 'N/A' }}</span>
                                            </div>
                                            <span>•</span>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>{{ auth()->user()->can('grade_submissions') ? $sub['user']->name : 'You' }}</span>
                                            </div>
                                            <span>•</span>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Submitted {{ $sub['submitted_at'] ? $sub['submitted_at']->diffForHumans() : 'N/A' }}</span>
                                            </div>
                                            @if($sub['due_date'])
                                                <span>•</span>
                                                <div class="flex items-center gap-1 {{ $sub['due_date']->isPast() && !$sub['graded_at'] ? 'text-red-600 dark:text-red-400' : '' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>Due {{ $sub['due_date']->format('M d, Y') }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Grade Display --}}
                                        @if($sub['graded_at'] && $sub['score'] !== null)
                                            @php
                                                $displayPct = $sub['percentage'] ?? (
                                                    ($sub['max_score'] ?? 0) > 0
                                                        ? round(((float) $sub['score'] / $sub['max_score']) * 100, 1)
                                                        : 0
                                                );
                                            @endphp
                                            <div class="flex items-center gap-3 mb-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                                <div class="flex-1">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                                                    <span class="ml-2 text-2xl font-bold {{ $displayPct >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        {{ number_format($displayPct, 1) }}%
                                                    </span>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        ({{ number_format((float) $sub['score'], 1) }}/{{ number_format((float) $sub['max_score'], 1) }})
                                                    </span>
                                                </div>
                                                @if($sub['grader'])
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        Graded by {{ $sub['grader']->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- File Attachments Preview --}}
                                        @if(!empty($sub['attachments']) && is_array($sub['attachments']))
                                            <div class="mb-3">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Attachments:</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($sub['attachments'] as $file)
                                                        @php
                                                            $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                                            $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                                        @endphp
                                                        @if($filePath === '') @continue @endif
                                                        <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                                           class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <span>{{ $fileName }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Content Preview --}}
                                        @if($sub['content'])
                                            <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                                                    {{ Str::limit(strip_tags($sub['content']), 150) }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Feedback Preview --}}
                                        @if($sub['feedback'])
                                            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Feedback:</span>
                                                <p class="text-sm text-blue-900 dark:text-blue-100 mt-1">
                                                    {{ Str::limit($sub['feedback'], 100) }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 ml-4">
                                @if(auth()->user()->can('grade_submissions'))
                                    <a href="{{ route('grades.grade', $sub['submission']) }}"
                                       class="px-4 py-2 {{ $sub['graded_at'] ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg font-medium transition-colors text-sm">
                                        {{ $sub['graded_at'] ? 'Edit Grade' : 'Grade' }}
                                    </a>
                                @endif
                                @if($sub['type'] === 'assignment')
                                    <a href="{{ route('submissions.show', ['submissionId' => $sub['id'], 'type' => 'assignment']) }}" 
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors text-sm">
                                        View
                                    </a>
                                @else
                                    <a href="{{ route('submissions.show', ['submissionId' => $sub['submission']->id, 'type' => 'assessment']) }}" 
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors text-sm">
                                        View
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($totalPages > 1)
            <div class="mt-6 flex items-center justify-center gap-2">
                @if($currentPage > 1)
                    <button wire:click="previousPage" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Previous
                    </button>
                @endif
                
                <span class="px-4 py-2 text-gray-700 dark:text-gray-300">
                    Page {{ $currentPage }} of {{ $totalPages }}
                </span>
                
                @if($currentPage < $totalPages)
                    <button wire:click="nextPage" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Next
                    </button>
                @endif
            </div>
        @endif
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No submissions found</h3>
            <p class="text-gray-600 dark:text-gray-400">
                @if(auth()->user()->can('grade_submissions'))
                    No student submissions match your filters.
                @else
                    You haven't submitted any assignments yet.
                @endif
            </p>
        </div>
    @endif
</div>
