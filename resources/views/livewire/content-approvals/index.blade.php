<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Content Approvals</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Review and approve content submissions</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Approved</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search content..." />
            <flux:select wire:model.live="filterStatus" label="Status">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </flux:select>
            <flux:select wire:model.live="filterType" label="Type">
                <option value="all">All Types</option>
                <option value="course">Courses</option>
                <option value="lesson">Lessons</option>
                <option value="module">Modules</option>
                <option value="assessment">Assessments</option>
            </flux:select>
            <flux:select wire:model.live="filterPriority" label="Priority">
                <option value="all">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </flux:select>
        </div>
    </div>

    {{-- Approvals List --}}
    @if($approvals->count() > 0)
        <div class="space-y-4">
            @foreach($approvals as $approval)
                <a href="{{ route('content-approvals.review', $approval) }}" class="block">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 {{ $approval->status === 'pending' ? 'border-orange-300 dark:border-orange-700' : ($approval->status === 'approved' ? 'border-green-300 dark:border-green-700' : 'border-red-300 dark:border-red-700') }} hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            @php
                                                $title = $approval->approvable ? match(class_basename($approval->approvable_type)) {
                                                    'Course' => $approval->approvable->title,
                                                    'Lesson' => $approval->approvable->title,
                                                    'CourseModule' => $approval->approvable->title,
                                                    'Assessment' => $approval->approvable->title,
                                                    default => 'Unknown Content',
                                                } : 'Deleted Item';
                                            @endphp
                                            {{ $title }}
                                        </h3>
                                        @if($approval->status === 'approved')
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-green-100 dark:bg-green-900/40 border border-green-200 dark:border-green-800 rounded-md">
                                                <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span class="text-xs font-semibold text-green-700 dark:text-green-300">{{ ucfirst($approval->status) }}</span>
                                            </div>
                                        @else
                                            <flux:badge size="sm" variant="{{ $approval->status === 'rejected' ? 'danger' : 'warning' }}">
                                                {{ ucfirst($approval->status) }}
                                            </flux:badge>
                                        @endif
                                        <flux:badge size="sm" variant="ghost">
                                            {{ class_basename($approval->approvable_type) }}
                                        </flux:badge>
                                        @if($approval->priority)
                                            <flux:badge size="xs" variant="{{ $approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost') }}">
                                                {{ ucfirst($approval->priority) }} Priority
                                            </flux:badge>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <span>Submitted by {{ $approval->submitter->name ?? 'Unknown' }}</span>
                                        <span>•</span>
                                        <span>{{ $approval->submitted_at->diffForHumans() }}</span>
                                        @if($approval->reviewed_at)
                                            <span>•</span>
                                            <span>Reviewed {{ $approval->reviewed_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if($approval->rejection_reason)
                                        <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <p class="text-sm text-red-800 dark:text-red-200">
                                                <strong>Reason:</strong> {{ $approval->rejection_reason }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    @if($approval->status === 'pending')
                                        <flux:button wire:click.stop="approveContent({{ $approval->id }})" variant="primary" size="sm">
                                            Approve
                                        </flux:button>
                                        <flux:button wire:click.stop="rejectContent({{ $approval->id }}, 'Needs revision')" variant="danger" size="sm">
                                            Reject
                                        </flux:button>
                                    @endif
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $approvals->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">All Clear!</h3>
            <p class="text-gray-600 dark:text-gray-400">No content approvals found</p>
        </div>
    @endif
</div>
