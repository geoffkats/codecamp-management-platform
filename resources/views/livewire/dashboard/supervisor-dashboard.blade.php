<div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Supervisor Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Content Review & Approval</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <flux:card class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Pending</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['pendingApprovals'] }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="bg-gradient-to-br from-green-500 to-green-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Approved Today</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['approvedToday'] }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="bg-gradient-to-br from-red-500 to-red-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Rejected Today</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['rejectedToday'] }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Reviewed</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['totalReviewed'] }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Approval Rate</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['approvalRate'] }}%</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </div>

        <!-- Approval Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <flux:card>
                <div class="p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['courses'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Courses</p>
                </div>
            </flux:card>
            <flux:card>
                <div class="p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['modules'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Modules</p>
                </div>
            </flux:card>
            <flux:card>
                <div class="p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['lessons'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Lessons</p>
                </div>
            </flux:card>
            <flux:card>
                <div class="p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['assessments'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Assessments</p>
                </div>
            </flux:card>
        </div>

        <!-- Approval List -->
        <flux:card>
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Content Approvals</h2>
                    <div class="flex gap-2">
                        <flux:button wire:click="filterByStatus('all')" variant="{{ $filterStatus === 'all' ? 'primary' : 'ghost' }}" size="sm">
                            All
                        </flux:button>
                        <flux:button wire:click="filterByStatus('pending')" variant="{{ $filterStatus === 'pending' ? 'primary' : 'ghost' }}" size="sm">
                            Pending
                        </flux:button>
                        <flux:button wire:click="filterByStatus('approved')" variant="{{ $filterStatus === 'approved' ? 'primary' : 'ghost' }}" size="sm">
                            Approved
                        </flux:button>
                        <flux:button wire:click="filterByStatus('rejected')" variant="{{ $filterStatus === 'rejected' ? 'primary' : 'ghost' }}" size="sm">
                            Rejected
                        </flux:button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @if($approvals->count() > 0)
                    <div class="space-y-4">
                        @foreach($approvals as $approval)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ class_basename($approval->approvable_type) }}: {{ $this->getApprovableTitle($approval) }}
                                            </h3>
                                            <flux:badge size="sm" variant="{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($approval->status) }}
                                            </flux:badge>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Submitted by {{ $approval->submitter->name }} {{ $approval->submitted_at->diffForHumans() }}
                                        </p>
                                        @if($approval->priority)
                                            <flux:badge size="xs" variant="{{ $approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost') }}" class="mt-2">
                                                {{ ucfirst($approval->priority) }} Priority
                                            </flux:badge>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($approval->status === 'pending')
                                            <flux:button wire:click="approveContent({{ $approval->id }})" variant="primary" size="sm">
                                                Approve
                                            </flux:button>
                                            <flux:button wire:click="rejectContent({{ $approval->id }}, 'Incomplete content')" variant="danger" size="sm">
                                                Reject
                                            </flux:button>
                                        @endif
                                        <flux:button href="{{ route('content-approvals.review', $approval) }}" variant="ghost" size="sm" wire:navigate>
                                            Review
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $approvals->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No approvals found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All caught up! No content pending review.</p>
                    </div>
                @endif
            </div>
        </flux:card>
    </div>
