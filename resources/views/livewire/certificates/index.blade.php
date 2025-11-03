<div class="flex flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Certificates</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                View and download your earned certificates of completion 🎓
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-blue-100">Total Certificates</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-full bg-blue-400/20 p-3">
                    <span class="text-3xl">🎓</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-green-100">Verified</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['verified'] }}</p>
                </div>
                <div class="rounded-full bg-green-400/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-purple-100">Active</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-full bg-purple-400/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-orange-100">Expired</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['expired'] }}</p>
                </div>
                <div class="rounded-full bg-orange-400/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Courses Without Certificates -->
    @if($completedCourses->count() > 0)
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg shadow-lg overflow-hidden border-2 border-blue-200 dark:border-blue-800">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Earn Certificates! 🎓</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            You've completed {{ $completedCourses->count() }} {{ $completedCourses->count() === 1 ? 'course' : 'courses' }} that qualify for certificates
                        </p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach($completedCourses->take(3) as $enrollment)
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ $enrollment->course->title }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Completed {{ $enrollment->completed_at->diffForHumans() }}
                                </p>
                            </div>
                            <flux:button href="{{ route('certificates.generate', $enrollment->course) }}" variant="primary" size="sm" wire:navigate>
                                Generate
                            </flux:button>
                        </div>
                    @endforeach
                </div>
                @if($completedCourses->count() > 3)
                    <div class="mt-4">
                        <flux:button href="{{ route('enrollments.index') }}" variant="ghost" size="sm" wire:navigate>
                            View All Completed Courses
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    label="Search Certificates"
                    placeholder="Search by title, course, or certificate number..."
                />

                @if(!empty($courseOptions))
                    <flux:field>
                        <flux:label>Filter by Course</flux:label>
                        <flux:select wire:model.live="filterCourse">
                            @foreach($courseOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                @endif
            </div>
        </div>
    </div>

    <!-- Certificates Grid -->
    @if($certificates->count() > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($certificates as $certificate)
                @php
                    $isExpired = $certificate->expires_at && $certificate->expires_at->isPast();
                    $isVerified = $certificate->is_verified;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 {{ $isExpired ? 'opacity-75 ring-2 ring-orange-500' : ($isVerified ? 'ring-2 ring-green-500' : '') }}">
                    <!-- Certificate Preview -->
                    <div class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-blue-900/20 dark:via-gray-800 dark:to-purple-900/20 p-8 border-b-4 border-blue-500">
                        <div class="text-center">
                            <div class="mb-4">
                                <span class="text-6xl">🎓</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $certificate->title ?: 'Certificate of Completion' }}
                            </h3>
                            @if($certificate->course)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $certificate->course->title }}
                                </p>
                            @endif
                        </div>
                        @if($isVerified)
                            <div class="absolute top-4 right-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Certificate Details -->
                    <div class="p-6">
                        <div class="space-y-3">
                            @if($certificate->certificate_number)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Certificate Number</p>
                                    <p class="text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $certificate->certificate_number }}</p>
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Issued On</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $certificate->issued_at ? $certificate->issued_at->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                                @if($certificate->expires_at)
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Expires On</p>
                                        <p class="text-sm font-medium {{ $isExpired ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                            {{ $certificate->expires_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($certificate->description)
<div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Description</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">{{ $certificate->description }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Status Badges -->
                        <div class="mt-4 flex items-center gap-2 flex-wrap">
                            @if($isVerified)
                                <flux:badge variant="success" size="sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Verified
                                </flux:badge>
                            @endif
                            @if($isExpired)
                                <flux:badge variant="danger" size="sm">Expired</flux:badge>
                            @elseif($certificate->expires_at)
                                <flux:badge variant="primary" size="sm">Active</flux:badge>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex items-center gap-2">
                            <flux:button href="{{ route('certificates.show', $certificate) }}" variant="primary" class="flex-1" wire:navigate>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </flux:button>
                            @if($certificate->file_path)
                                <flux:button 
                                    href="{{ asset('storage/' . $certificate->file_path) }}" 
                                    variant="ghost" 
                                    target="_blank"
                                    download>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $certificates->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No certificates yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($this->search || $this->filterCourse !== 'all')
                        Try adjusting your filters
                    @else
                        Complete courses to earn certificates
                    @endif
                </p>
                <div class="mt-6">
                    <flux:button href="{{ route('courses.index') }}" variant="primary" wire:navigate>
                        Browse Courses
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
