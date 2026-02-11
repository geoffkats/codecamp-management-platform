<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Manage Course Enrollments</h1>
                    <p class="text-purple-100">{{ $course->title }}</p>
                    <div class="flex items-center gap-4 mt-3 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            Enrollment Type: {{ ucfirst(str_replace('_', ' ', $course->enrollment_type ?? 'open')) }}
                        </span>
                        @if($course->max_students)
                            <span class="bg-white/20 px-3 py-1 rounded-full">
                                Max Students: {{ $course->max_students }}
                            </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('courses.show', $course) }}" wire:navigate 
                   class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px">
                    <button wire:click="$set('activeTab', 'requests')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $activeTab === 'requests' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Enrollment Requests
                            @if($requests->where('status', 'pending')->count() > 0)
                                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $requests->where('status', 'pending')->count() }}
                                </span>
                            @endif
                        </div>
                    </button>
                    <button wire:click="$set('activeTab', 'invitations')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $activeTab === 'invitations' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Invitations
                        </div>
                    </button>
                    <button wire:click="$set('activeTab', 'students')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $activeTab === 'students' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Enrolled Students
                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-full px-2 py-0.5">
                                {{ $students->total() }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Enrollment Requests Tab --}}
                @if($activeTab === 'requests')
                    <div class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Requests</h3>
                        </div>

                        @if($requests->count() > 0)
                            <div class="space-y-3">
                                @foreach($requests as $request)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start gap-4 flex-1">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ substr($request->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $request->user->name }}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->user->email }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        Requested {{ $request->requested_at->diffForHumans() }}
                                                    </p>
                                                    @if($request->message)
                                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 bg-white dark:bg-gray-800 p-2 rounded">
                                                            "{{ $request->message }}"
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($request->status === 'pending')
                                                <div class="flex gap-2 ml-4">
                                                    <flux:button wire:click="approveRequest({{ $request->id }})" variant="success" size="sm">
                                                        Approve
                                                    </flux:button>
                                                    <flux:button 
                                                        x-data 
                                                        @click="$wire.rejectionReason = prompt('Reason for rejection:'); if($wire.rejectionReason) $wire.rejectRequest({{ $request->id }})" 
                                                        variant="danger" 
                                                        size="sm">
                                                        Reject
                                                    </flux:button>
                                                </div>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $requests->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No enrollment requests yet</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Invitations Tab --}}
                @if($activeTab === 'invitations')
                    <div class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Invitations</h3>
                            <flux:button wire:click="$set('showInviteModal', true)" variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Send Invitations
                            </flux:button>
                        </div>

                        @if($invitations->count() > 0)
                            <div class="space-y-3">
                                @foreach($invitations as $invitation)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start gap-4 flex-1">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ substr($invitation->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $invitation->user->name }}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invitation->user->email }}</p>
                                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                        <span>Invited {{ $invitation->invited_at->diffForHumans() }}</span>
                                                        @if($invitation->expires_at)
                                                            <span>•</span>
                                                            <span class="{{ $invitation->isExpired() ? 'text-red-600' : '' }}">
                                                                Expires {{ $invitation->expires_at->diffForHumans() }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="px-3 py-1 rounded-full text-xs font-medium 
                                                    {{ $invitation->status === 'accepted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                                       ($invitation->status === 'declined' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                                       ($invitation->status === 'expired' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' : 
                                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400')) }}">
                                                    {{ ucfirst($invitation->status) }}
                                                </span>
                                                @if($invitation->status === 'pending')
                                                    <flux:button wire:click="cancelInvitation({{ $invitation->id }})" variant="ghost" size="sm">
                                                        Cancel
                                                    </flux:button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $invitations->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No invitations sent yet</p>
                                <flux:button wire:click="$set('showInviteModal', true)" variant="primary" class="mt-4">
                                    Send Your First Invitation
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Enrolled Students Tab --}}
                @if($activeTab === 'students')
                    <div class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Enrolled Students ({{ $students->total() }})</h3>
                        </div>

                        @if($students->count() > 0)
                            <div class="space-y-3">
                                @foreach($students as $enrollment)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start gap-4 flex-1">
                                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                                    {{ substr($enrollment->user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-white text-base">{{ $enrollment->user->name }}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $enrollment->user->email }}</p>
                                                    
                                                    {{-- Progress Bar --}}
                                                    <div class="mt-3 space-y-1">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                                            <span class="font-semibold text-purple-600 dark:text-purple-400">{{ number_format($enrollment->progress_percentage, 1) }}%</span>
                                                        </div>
                                                        <div class="w-full h-2 bg-gray-300 dark:bg-gray-600 rounded-full overflow-hidden">
                                                            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500"
                                                                 style="width: {{ $enrollment->progress_percentage }}%"></div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center flex-wrap gap-2 mt-3">
                                                        <span class="text-xs text-gray-500 bg-white dark:bg-gray-800 px-2 py-1 rounded-full">
                                                            📅 {{ $enrollment->enrolled_at->format('M d, Y') }}
                                                        </span>
                                                        @if($enrollment->lessons_completed > 0)
                                                            <span class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-full">
                                                                📚 {{ $enrollment->lessons_completed }} lesson(s)
                                                            </span>
                                                        @endif
                                                        @if($enrollment->quizzes_completed > 0)
                                                            <span class="text-xs text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 px-2 py-1 rounded-full">
                                                                🧪 {{ $enrollment->quizzes_completed }} quiz(zes)
                                                            </span>
                                                        @endif
                                                        @if($enrollment->completed_at)
                                                            <span class="text-xs text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full font-semibold">
                                                                ✅ Completed {{ $enrollment->completed_at->format('M d, Y') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <flux:button 
                                                wire:click="unenrollStudent({{ $enrollment->id }})" 
                                                variant="danger" 
                                                size="sm"
                                                wire:confirm="Are you sure? {{ $enrollment->user->name }} will lose access to this course."
                                                class="ml-4 whitespace-nowrap">
                                                Remove
                                            </flux:button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $students->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No students enrolled yet</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Professional Invite Modal --}}
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ selectedCount: @entangle('selectedStudents').live }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" wire:click="$set('showInviteModal', false)"></div>

                {{-- Modal --}}
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-200 dark:border-gray-700">
                    {{-- Modal Header --}}
                    <div class="px-8 pt-6 pb-4 bg-gradient-to-r from-purple-600 to-pink-600">
                        <div class="flex items-start justify-between">
                            <div class="text-white">
                                <h3 class="text-2xl font-bold mb-1">Send Course Invitations</h3>
                                <p class="text-purple-100 text-sm">Invite students to join {{ $course->title }}</p>
                            </div>
                            <button wire:click="$set('showInviteModal', false)" 
                                    class="text-white/80 hover:text-white transition rounded-lg p-1 hover:bg-white/10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-8 py-6 space-y-6">
                        {{-- Search Bar --}}
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                🔍 Search Students
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <flux:input wire:model.live.debounce.300ms="searchStudents" 
                                           placeholder="Search by name, email, or ID..." 
                                           class="pl-10" />
                            </div>
                        </div>

                        {{-- Selected Counter --}}
                        <div class="flex items-center justify-between bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-semibold text-purple-900 dark:text-purple-100">
                                    <span x-text="selectedCount.length"></span> student(s) selected
                                </span>
                            </div>
                            @if(count($selectedStudents) > 0)
                                <button wire:click="$set('selectedStudents', [])" 
                                        class="text-xs text-purple-600 dark:text-purple-400 hover:underline">
                                    Clear All
                                </button>
                            @endif
                        </div>

                        {{-- Student List --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                👥 Select Students to Invite
                            </label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-1 max-h-72 overflow-y-auto bg-gray-50 dark:bg-gray-900/50">
                                @forelse($availableStudents as $student)
                                    <label class="flex items-center gap-4 p-3 hover:bg-white dark:hover:bg-gray-800 rounded-lg cursor-pointer transition-all group border-2 border-transparent hover:border-purple-200 dark:hover:border-purple-800">
                                        <input type="checkbox" 
                                               wire:model.live="selectedStudents" 
                                               value="{{ $student->id }}" 
                                               class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="relative">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                                    {{ substr($student->name, 0, 1) }}
                                                </div>
                                                @if($student->is_active)
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                                                    {{ $student->name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email }}</p>
                                                @if($student->points)
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500">Level {{ $student->points->level }}</span>
                                                        <span class="text-xs text-gray-400">•</span>
                                                        <span class="text-xs text-gray-500">{{ $student->points->total_points }} XP</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            @if($searchStudents)
                                                No students found matching "{{ $searchStudents }}"
                                            @else
                                                All available students are already invited or enrolled
                                            @endif
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                            @error('selectedStudents') 
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Invitation Details --}}
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        📅 Expires In
                                    </label>
                                    <div class="relative">
                                        <flux:input type="number" wire:model="expiresInDays" min="1" max="90" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">days</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        📊 Expiration Date
                                    </label>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-300 dark:border-gray-600">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ now()->addDays($expiresInDays ?? 7)->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                💬 Personal Message (Optional)
                            </label>
                            <flux:textarea wire:model="invitationMessage" 
                                          rows="3" 
                                          placeholder="Add a welcoming message for your students...&#10;Example: 'We're excited to have you join this exclusive course! This is a great opportunity to...'"></flux:textarea>
                            <p class="mt-1 text-xs text-gray-500">This message will be included in the invitation notification</p>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-8 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span x-show="selectedCount.length === 0">Select students to send invitations</span>
                            <span x-show="selectedCount.length > 0" class="font-semibold text-purple-600 dark:text-purple-400">
                                Ready to send <span x-text="selectedCount.length"></span> invitation(s)
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <flux:button wire:click="$set('showInviteModal', false)" variant="ghost">
                                Cancel
                            </flux:button>
                            <flux:button wire:click="sendInvitations" 
                                        variant="primary"
                                        :disabled="count($selectedStudents) === 0"
                                        class="relative">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send <span x-show="selectedCount.length > 0">(<span x-text="selectedCount.length"></span>)</span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
