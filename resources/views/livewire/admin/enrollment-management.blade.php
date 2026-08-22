<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Global Enrollment Management</h1>
                        <p class="text-purple-100 text-lg">Manage all course enrollments, invitations, and requests across the platform</p>
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="$set('showInviteModal', true)" variant="primary">
                            Enroll or Invite Students
                        </flux:button>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30">
                        <p class="text-purple-100 text-sm mb-1">Total Enrollments</p>
                        <p class="text-3xl font-bold">{{ number_format($stats['total_enrollments']) }}</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30">
                        <p class="text-purple-100 text-sm mb-1">Pending Requests</p>
                        <p class="text-3xl font-bold">{{ $stats['pending_requests'] }}</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30">
                        <p class="text-purple-100 text-sm mb-1">Active Invitations</p>
                        <p class="text-3xl font-bold">{{ $stats['pending_invitations'] }}</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30">
                        <p class="text-purple-100 text-sm mb-1">Active Students</p>
                        <p class="text-3xl font-bold">{{ number_format($stats['active_students']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded-lg p-4 flex items-center gap-3 shadow-lg animate-slide-in">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Filters Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
            @if(config('features.code_club', false))
                <div class="flex flex-wrap gap-2">
                    @foreach(['all' => 'All Programs', 'codecamp' => 'CodeCamp', 'codeclub' => 'Code Club', 'ict' => 'ICT'] as $value => $label)
                        <button type="button" wire:click="$set('filterProgram', '{{ $value }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterProgram === $value ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-64">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter by Course</label>
                    <flux:select wire:model.live="selectedCourseId">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </flux:select>
                </div>

                @if($activeTab === 'requests')
                    <div class="min-w-48">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status Filter</label>
                        <flux:select wire:model.live="filterStatus">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </flux:select>
                    </div>
                @endif

                @if($activeTab === 'invitations')
                    <div class="min-w-48">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Invitation Status</label>
                        <flux:select wire:model.live="invitationFilter">
                            <option value="active">Active (awaiting response)</option>
                            <option value="expired">Expired / cancelled</option>
                            <option value="accepted">Accepted</option>
                            <option value="declined">Declined</option>
                            <option value="all">All history</option>
                        </flux:select>
                    </div>
                @endif

                @if($activeTab === 'enrollments')
                    <div class="min-w-48">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Enrollment Status</label>
                        <flux:select wire:model.live="enrollmentFilter">
                            <option value="active">In progress</option>
                            <option value="completed">Completed</option>
                            <option value="all">All</option>
                        </flux:select>
                    </div>
                    <div class="min-w-48">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter by Camp</label>
                        <flux:select wire:model.live="selectedCampId">
                            <option value="">All Camps</option>
                            @foreach($camps as $camp)
                                <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    @if(config('features.code_club', false) && ($filterProgram === 'all' || $filterProgram === 'codeclub'))
                        <div class="min-w-48">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter by Club</label>
                            <flux:select wire:model.live="selectedClubId">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}">{{ $club->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    <div class="flex-1 min-w-64">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Search Students</label>
                        <flux:input wire:model.live.debounce.300ms="searchStudent" placeholder="Search by name or email..." />
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <nav class="flex -mb-px overflow-x-auto">
                    <button wire:click="$set('activeTab', 'overview')" 
                            class="flex-shrink-0 py-4 px-6 text-sm font-semibold border-b-3 transition-all {{ $activeTab === 'overview' ? 'border-purple-500 text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Overview
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'requests')" 
                            class="flex-shrink-0 py-4 px-6 text-sm font-semibold border-b-3 transition-all {{ $activeTab === 'requests' ? 'border-purple-500 text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Enrollment Requests
                            @if($stats['pending_requests'] > 0)
                                <span class="bg-red-500 text-white text-xs rounded-full px-2.5 py-0.5 font-bold animate-pulse">
                                    {{ $stats['pending_requests'] }}
                                </span>
                            @endif
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'invitations')" 
                            class="flex-shrink-0 py-4 px-6 text-sm font-semibold border-b-3 transition-all {{ $activeTab === 'invitations' ? 'border-purple-500 text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Invitations
                            @if($stats['pending_invitations'] > 0)
                                <span class="bg-yellow-500 text-white text-xs rounded-full px-2.5 py-0.5 font-bold">
                                    {{ $stats['pending_invitations'] }}
                                </span>
                            @endif
                        </div>
                    </button>

                    <button wire:click="$set('activeTab', 'enrollments')" 
                            class="flex-shrink-0 py-4 px-6 text-sm font-semibold border-b-3 transition-all {{ $activeTab === 'enrollments' ? 'border-purple-500 text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            All Enrollments
                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-full px-2.5 py-0.5 font-bold">
                                {{ number_format($stats['total_enrollments']) }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Overview Tab --}}
                @if($activeTab === 'overview')
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {{-- Recent Requests Card --}}
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border-2 border-blue-200 dark:border-blue-800 hover:shadow-xl transition cursor-pointer"
                                 wire:click="$set('activeTab', 'requests')">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    @if($stats['pending_requests'] > 0)
                                        <span class="animate-bounce bg-red-500 text-white text-xs rounded-full px-3 py-1 font-bold">
                                            {{ $stats['pending_requests'] }} New
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Enrollment Requests</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and approve pending requests</p>
                                <div class="mt-4 flex items-center text-blue-600 dark:text-blue-400 text-sm font-semibold">
                                    View All
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Invitations Card --}}
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-6 border-2 border-purple-200 dark:border-purple-800 hover:shadow-xl transition cursor-pointer"
                                 wire:click="$set('activeTab', 'invitations')">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Course Invitations</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage sent invitations</p>
                                <div class="mt-4 flex items-center text-purple-600 dark:text-purple-400 text-sm font-semibold">
                                    View All
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Enrollments Card --}}
                            <div class="bg-gradient-to-br from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 rounded-xl p-6 border-2 border-green-200 dark:border-green-800 hover:shadow-xl transition cursor-pointer"
                                 wire:click="$set('activeTab', 'enrollments')">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">All Enrollments</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View all student enrollments</p>
                                <div class="mt-4 flex items-center text-green-600 dark:text-green-400 text-sm font-semibold">
                                    View All
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Enrollment Requests Tab --}}
                @if($activeTab === 'requests')
                    <div class="space-y-4">
                        @if($requests->count() > 0)
                            <div class="grid gap-4">
                                @foreach($requests as $request)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-2 border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition-all hover:shadow-xl">
                                        <div class="p-6">
                                            <div class="flex items-start justify-between gap-4">
                                                {{-- Student Info --}}
                                                <div class="flex items-start gap-4 flex-1">
                                                    <div class="relative">
                                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                                                            {{ substr($request->user->name, 0, 1) }}
                                                        </div>
                                                        <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-blue-500 border-2 border-white dark:border-gray-800 rounded-full flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex-1">
                                                        <div class="flex items-start justify-between">
                                                            <div>
                                                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $request->user->name }}</h4>
                                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->user->email }}</p>
                                                            </div>
                                                        </div>
                                                        
                                                        {{-- Course Info --}}
                                                        <div class="mt-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                </svg>
                                                                <a href="{{ route('courses.show', $request->course) }}" 
                                                                   wire:navigate
                                                                   class="text-sm font-semibold text-purple-600 dark:text-purple-400 hover:underline">
                                                                    {{ $request->course->title }}
                                                                </a>
                                                            </div>
                                                        </div>

                                                        {{-- Request Details --}}
                                                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                            <div class="flex items-center gap-1">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {{ $request->requested_at->diffForHumans() }}
                                                            </div>
                                                            <span>•</span>
                                                            <span>Request ID: #{{ $request->id }}</span>
                                                        </div>

                                                        @if($request->message)
                                                            <div class="mt-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-3 rounded">
                                                                <p class="text-xs font-semibold text-blue-900 dark:text-blue-100 mb-1">Student's Message:</p>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $request->message }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="flex flex-col gap-2">
                                                    <span class="px-3 py-1 rounded-full text-xs font-bold text-center
                                                        {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                                           ($request->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                                           'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                                                        {{ ucfirst($request->status) }}
                                                    </span>

                                                    @if($request->status === 'pending')
                                                        <flux:button wire:click="approveRequest({{ $request->id }})" 
                                                                    variant="success" 
                                                                    size="sm"
                                                                    class="w-full">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Approve
                                                        </flux:button>
                                                        <flux:button 
                                                            x-data 
                                                            @click="$wire.rejectionReason = prompt('Reason for rejection (minimum 10 characters):'); if($wire.rejectionReason && $wire.rejectionReason.length >= 10) $wire.rejectRequest({{ $request->id }}); else if($wire.rejectionReason) alert('Reason must be at least 10 characters')" 
                                                            variant="danger" 
                                                            size="sm"
                                                            class="w-full">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            Reject
                                                        </flux:button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{ $requests->links() }}
                        @else
                            <div class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No enrollment requests</h3>
                                <p class="text-gray-500 dark:text-gray-400 mt-1">No students have requested enrollment yet</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Invitations Tab --}}
                @if($activeTab === 'invitations')
                    <div class="space-y-4">
                        @if($invitationFilter === 'active')
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing invitations that are still valid and awaiting a student response.
                                Expired invitations are moved to the archive automatically.
                            </p>
                        @endif

                        @if($invitations->count() > 0)
                            <div class="grid gap-4">
                                @foreach($invitations as $invitation)
                                    @php($displayStatus = $invitation->effectiveStatus())
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-2 border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition-all">
                                        <div class="p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                                    {{ substr($invitation->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <h4 class="font-bold text-gray-900 dark:text-white">{{ $invitation->user->name }}</h4>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invitation->user->email }}</p>
                                                        </div>
                                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                                            {{ $displayStatus === 'accepted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                                               ($displayStatus === 'declined' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                                                               ($displayStatus === 'expired' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' :
                                                               'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400')) }}">
                                                            {{ ucfirst($displayStatus) }}
                                                        </span>
                                                    </div>

                                                    <div class="mt-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                                                        <a href="{{ route('courses.show', $invitation->course) }}" 
                                                           wire:navigate
                                                           class="text-sm font-semibold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                            </svg>
                                                            {{ $invitation->course->title }}
                                                        </a>
                                                    </div>

                                                    <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                                                        <span>Invited {{ $invitation->invited_at->diffForHumans() }}</span>
                                                        @if($invitation->expires_at)
                                                            <span>•</span>
                                                            <span class="{{ $invitation->isExpired() ? 'text-red-600 font-semibold' : '' }}">
                                                                {{ $invitation->isExpired() ? 'Expired' : 'Expires ' . $invitation->expires_at->diffForHumans() }}
                                                            </span>
                                                        @endif
                                                        <span>•</span>
                                                        <span>By: {{ $invitation->inviter->name }}</span>
                                                    </div>

                                                    @if($invitation->isActionable())
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <flux:button wire:click="cancelInvitation({{ $invitation->id }})"
                                                                        wire:confirm="Cancel this invitation?"
                                                                        variant="ghost"
                                                                        size="sm">
                                                                Cancel Invitation
                                                            </flux:button>
                                                        </div>
                                                    @elseif($displayStatus === 'expired')
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <flux:button wire:click="resendInvitation({{ $invitation->id }})"
                                                                        variant="primary"
                                                                        size="sm">
                                                                Resend Invitation
                                                            </flux:button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{ $invitations->links() }}
                        @else
                            <div class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    @if($invitationFilter === 'active')
                                        No active invitations
                                    @elseif($invitationFilter === 'expired')
                                        No expired invitations
                                    @else
                                        No invitations found
                                    @endif
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400 mt-1 mb-4">
                                    @if($invitationFilter === 'active')
                                        Send a new invitation or enroll students directly from the button above.
                                    @else
                                        Try a different filter or send new invitations.
                                    @endif
                                </p>
                                <flux:button wire:click="$set('showInviteModal', true)" variant="primary">
                                    Send Your First Invitation
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- All Enrollments Tab --}}
                @if($activeTab === 'enrollments')
                    <div class="space-y-4">
                        @if($enrollments->count() > 0)
                            <div class="grid gap-4">
                                @foreach($enrollments as $enrollment)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-2 border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-700 transition-all">
                                        <div class="p-6">
                                            <div class="flex items-start gap-4">
                                                <div class="relative">
                                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                                                        {{ substr($enrollment->user->name, 0, 1) }}
                                                    </div>
                                                    @if($enrollment->completed_at)
                                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 border-2 border-white dark:border-gray-800 rounded-full flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between mb-2">
                                                        <div>
                                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $enrollment->user->name }}</h4>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $enrollment->user->email }}</p>
                                                        </div>
                                                    </div>

                                                    {{-- Course --}}
                                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-3">
                                                        <a href="{{ route('courses.show', $enrollment->course) }}"
                                                           wire:navigate
                                                           class="text-sm font-semibold text-green-600 dark:text-green-400 hover:underline flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                            </svg>
                                                            {{ $enrollment->course->title }}
                                                        </a>
                                                        @if($enrollment->camp)
                                                            <p class="text-xs text-orange-600 dark:text-orange-400 mt-1.5 font-semibold">
                                                                Camp: {{ $enrollment->camp->name }}
                                                            </p>
                                                        @endif
                                                        @if($enrollment->club)
                                                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1.5 font-semibold">
                                                                Club: {{ $enrollment->club->name }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    {{-- Progress Bar --}}
                                                    <div class="mb-3">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Progress</span>
                                                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400">{{ number_format($enrollment->progress_percentage, 1) }}%</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2.5 rounded-full transition-all" 
                                                                 style="width: {{ $enrollment->progress_percentage }}%"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Stats --}}
                                                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                                        <div class="flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Enrolled {{ $enrollment->enrolled_at->diffForHumans() }}
                                                        </div>
                                                        <span>•</span>
                                                        <span>{{ $enrollment->lessons_completed ?? 0 }} lessons completed</span>
                                                        @if($enrollment->completed_at)
                                                            <span>•</span>
                                                            <span class="text-green-600 dark:text-green-400 font-semibold">Completed {{ $enrollment->completed_at->format('M d, Y') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{ $enrollments->links() }}
                        @else
                            <div class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No enrollments found</h3>
                                <p class="text-gray-500 dark:text-gray-400 mt-1">No students enrolled in courses yet</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Professional Invite Modal with Course Selection --}}
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ selectedCount: @entangle('selectedStudents').live }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
                <div class="fixed inset-0 transition-opacity bg-gray-900/80 backdrop-blur-md" wire:click="$set('showInviteModal', false)"></div>

                <div class="inline-block w-full max-w-4xl overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 relative z-10">
                    {{-- Modal Header --}}
                    <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
                        
                        <div class="relative z-10 flex items-start justify-between">
                            <div class="text-white">
                                <h3 class="text-3xl font-bold mb-2">Enroll or Invite Students</h3>
                                <p class="text-purple-100">Enroll directly or send time-limited invitations</p>
                            </div>
                            <button wire:click="$set('showInviteModal', false)" 
                                    class="text-white/80 hover:text-white transition rounded-xl p-2 hover:bg-white/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-8 py-6 space-y-6 max-h-[70vh] overflow-y-auto">
                        {{-- Course Selection --}}
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-5 border-2 border-purple-200 dark:border-purple-800">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Select Course *
                            </label>
                            <flux:select wire:model.live="selectedCourseId">
                                <option value="">Choose a course...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">
                                        {{ $course->title }} ({{ $course->enrollment_type ? ucfirst($course->enrollment_type) : 'Open' }})
                                    </option>
                                @endforeach
                            </flux:select>
                            @error('selectedCourseId')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($selectedCourseId)
                            {{-- Enroll mode --}}
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/40">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">How should students get access?</p>
                                <div class="flex flex-wrap gap-3">
                                    <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2 text-sm {{ $enrollMode === 'direct' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300' : 'border-gray-300 dark:border-gray-600' }}">
                                        <input type="radio" wire:model.live="enrollMode" value="direct" class="text-green-600">
                                        Enroll directly
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2 text-sm {{ $enrollMode === 'invite' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20 text-purple-800 dark:text-purple-300' : 'border-gray-300 dark:border-gray-600' }}">
                                        <input type="radio" wire:model.live="enrollMode" value="invite" class="text-purple-600">
                                        Send invitation (expires)
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    @if($enrollMode === 'direct')
                                        Students are enrolled immediately — no invitation step required.
                                    @else
                                        Students must accept the invitation before they are enrolled. Expired invitations are archived automatically.
                                    @endif
                                </p>
                            </div>

                            {{-- Search Students --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Search Available Students
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <flux:input wire:model.live.debounce.300ms="searchStudent" 
                                               placeholder="Type student name or email to search..." 
                                               class="pl-12 text-base" />
                                </div>
                            </div>

                            {{-- Selection Counter --}}
                            <div class="bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/40 dark:to-pink-900/40 rounded-xl p-4 border-2 border-purple-300 dark:border-purple-700 shadow-inner">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-purple-900 dark:text-purple-100">
                                                <span x-text="selectedCount.length"></span> Student<span x-show="selectedCount.length !== 1">s</span> Selected
                                            </p>
                                            <p class="text-xs text-purple-700 dark:text-purple-300">
                                                @if($enrollMode === 'direct')
                                                    Ready to enroll directly
                                                @else
                                                    Ready to send invitations
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if(count($selectedStudents) > 0)
                                        <flux:button wire:click="$set('selectedStudents', [])" variant="ghost" size="sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Clear
                                        </flux:button>
                                    @endif
                                </div>
                            </div>

                            {{-- Student Selection Grid --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Select Students to Invite
                                </label>
                                <div class="border-3 border-dashed border-purple-300 dark:border-purple-700 rounded-2xl p-2 max-h-80 overflow-y-auto bg-gradient-to-br from-gray-50 to-purple-50/30 dark:from-gray-900/50 dark:to-purple-900/20">
                                    @forelse($availableStudents as $student)
                                        <label class="flex items-center gap-4 p-4 mb-2 hover:bg-white dark:hover:bg-gray-800 rounded-xl cursor-pointer transition-all group border-2 border-transparent hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-lg">
                                            <input type="checkbox" 
                                                   wire:model.live="selectedStudents" 
                                                   value="{{ $student->id }}" 
                                                   class="w-6 h-6 rounded-lg border-2 border-gray-300 text-purple-600 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 transition">
                                            
                                            <div class="flex items-center gap-4 flex-1">
                                                <div class="relative">
                                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-110 transition-transform">
                                                        {{ substr($student->name, 0, 1) }}
                                                    </div>
                                                    @if($student->is_active)
                                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-3 border-white dark:border-gray-800 rounded-full flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex-1">
                                                    <p class="text-base font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                                                        {{ $student->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->email }}</p>
                                                    @if($student->points)
                                                        <div class="flex items-center gap-3 mt-2">
                                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-xs font-semibold text-purple-700 dark:text-purple-300">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                                </svg>
                                                                Level {{ $student->points->level }}
                                                            </span>
                                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg text-xs font-semibold text-yellow-700 dark:text-yellow-300">
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                                {{ number_format($student->points->total_points) }} XP
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <svg class="w-6 h-6 text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-center py-12">
                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-2xl mb-3">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @if($searchStudent)
                                                    No students found matching "{{ $searchStudent }}"
                                                @else
                                                    All students are already invited or enrolled in this course
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Try selecting a different course or searching for other students</p>
                                        </div>
                                    @endforelse
                                </div>
                                @error('selectedStudents') 
                                    <p class="mt-3 text-sm text-red-600 dark:text-red-400 flex items-center gap-2 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            @if($enrollMode === 'invite')
                            {{-- Invitation Settings --}}
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5 border-2 border-indigo-200 dark:border-indigo-800">
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    Invitation Settings
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            📅 Expires In
                                        </label>
                                        <div class="relative">
                                            <flux:input type="number" wire:model.live="expiresInDays" min="1" max="90" class="pr-16" />
                                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-600 dark:text-gray-400">days</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            📊 Expiration Date
                                        </label>
                                        <div class="bg-white dark:bg-gray-800 rounded-xl px-4 py-3 border-2 border-gray-300 dark:border-gray-600">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ now()->addDays($expiresInDays ?? 7)->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($enrollMode === 'invite')
                            {{-- Personal Message --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Personal Message (Optional)
                                </label>
                                <flux:textarea wire:model="invitationMessage" 
                                              rows="4" 
                                              placeholder="Example: We're thrilled to invite you to join our exclusive advanced programming course. This is a fantastic opportunity to level up your skills with hands-on projects and expert guidance..."
                                              class="text-sm"></flux:textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    This message will appear in the invitation notification sent to students
                                </p>
                            </div>
                            @endif
                        @else
                            <div class="text-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-400">Please select a course first</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Choose which course you want to invite students to</p>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-purple-50 dark:from-gray-900/50 dark:to-purple-900/20 border-t-2 border-gray-200 dark:border-gray-700 flex items-center justify-between sticky bottom-0">
                        <div class="text-sm">
                            <p class="text-gray-600 dark:text-gray-400" x-show="selectedCount.length === 0">
                                Select students and click send
                            </p>
                            <p class="font-bold text-purple-600 dark:text-purple-400" x-show="selectedCount.length > 0">
                                @if($enrollMode === 'direct')
                                    <span x-text="selectedCount.length"></span> student<span x-show="selectedCount.length !== 1">s</span> ready to enroll
                                @else
                                    <span x-text="selectedCount.length"></span> invitation<span x-show="selectedCount.length !== 1">s</span> ready to send
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <flux:button wire:click="$set('showInviteModal', false)" variant="ghost">
                                Cancel
                            </flux:button>
                            <flux:button wire:click="sendInvitations" 
                                        variant="primary"
                                        :disabled="count($selectedStudents) === 0 || !$selectedCourseId"
                                        class="relative group">
                                @if($enrollMode === 'direct')
                                    Enroll Selected
                                @else
                                    <svg class="w-5 h-5 mr-2 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Send Invitations</span>
                                @endif
                                <span x-show="selectedCount.length > 0" class="ml-1 px-2 py-0.5 bg-white/20 rounded-full text-xs">
                                    <span x-text="selectedCount.length"></span>
                                </span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Styles --}}
    <style>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    </style>
</div>
