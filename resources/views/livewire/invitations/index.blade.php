<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Course Invitations</h1>
                    <p class="text-purple-100">View and manage your course invitations</p>
                </div>
                <a href="{{ route('courses.index') }}" wire:navigate 
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
        @if(session()->has('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px">
                    <button wire:click="$set('filter', 'pending')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $filter === 'pending' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Pending
                            @if($stats['pending'] > 0)
                                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                                    {{ $stats['pending'] }}
                                </span>
                            @endif
                        </div>
                    </button>
                    <button wire:click="$set('filter', 'accepted')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $filter === 'accepted' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Accepted
                        </div>
                    </button>
                    <button wire:click="$set('filter', 'declined')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $filter === 'declined' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Declined
                        </div>
                    </button>
                    <button wire:click="$set('filter', 'expired')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $filter === 'expired' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Expired
                        </div>
                    </button>
                    <button wire:click="$set('filter', 'all')" 
                            class="py-4 px-6 text-sm font-medium border-b-2 transition {{ $filter === 'all' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            All
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                @if($invitations->count() > 0)
                    <div class="space-y-3">
                        @foreach($invitations as $invitation)
                            @php
                                $isExpired = $invitation->expires_at && $invitation->expires_at->isPast();
                                $isPending = $invitation->status === 'pending' && !$isExpired;
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-4 flex-1">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-lg">
                                            {{ substr($invitation->course->title, 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $invitation->course->title }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Invited by: {{ $invitation->inviter->name ?? 'System' }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                <span>Invited {{ $invitation->invited_at?->diffForHumans() ?? 'Recently' }}</span>
                                                @if($invitation->expires_at)
                                                    <span>•</span>
                                                    <span class="{{ $invitation->isExpired() ? 'text-red-600' : '' }}">
                                                        Expires {{ $invitation->expires_at->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($invitation->message)
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 bg-white dark:bg-gray-800 p-2 rounded">
                                                    "{{ $invitation->message }}"
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 ml-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                                            {{ $invitation->status === 'accepted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                               ($invitation->status === 'declined' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                               ($isExpired ? 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' : 
                                               'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400')) }}">
                                            {{ ucfirst($invitation->status) }}
                                        </span>
                                        @if($isPending)
                                            <flux:button wire:click="acceptInvitation({{ $invitation->id }})" variant="primary" size="sm">
                                                Accept
                                            </flux:button>
                                            <flux:button wire:click="declineInvitation({{ $invitation->id }})" variant="danger" size="sm">
                                                Decline
                                            </flux:button>
                                        @elseif($invitation->status === 'accepted')
                                            <flux:button href="{{ route('courses.learn', $invitation->course) }}" variant="primary" size="sm" wire:navigate>
                                                Go to Course
                                            </flux:button>
                                        @else
                                            <flux:button href="{{ route('courses.show', $invitation->course) }}" variant="ghost" size="sm" wire:navigate>
                                                View Course
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
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            @if($filter === 'pending')
                                No pending invitations
                            @elseif($filter === 'accepted')
                                No accepted invitations
                            @elseif($filter === 'declined')
                                No declined invitations
                            @elseif($filter === 'expired')
                                No expired invitations
                            @else
                                No invitations yet
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
