<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Course Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($course->featured_image)
                <div class="relative h-64 w-full overflow-hidden">
                    <img src="{{ asset('storage/' . $course->featured_image) }}" 
                         alt="{{ $course->title }}" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                </div>
            @else
                <div class="h-64 w-full bg-gradient-to-br from-blue-500 via-purple-600 to-pink-600 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <span class="relative z-10 text-8xl font-bold text-white opacity-90">{{ substr($course->title, 0, 1) }}</span>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $course->title }}</h1>
                            @if($course->is_featured)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                    ⭐ Featured
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->approval_status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($course->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400') }}">
                                {{ ucfirst($course->approval_status) }}
                            </span>
                        </div>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">{{ $course->short_description }}</p>
                        
                        <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="font-medium">{{ $course->instructor->name }}</span>
                            </div>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>{{ $course->difficulty_level }}</span>
                            </div>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $course->estimated_duration }} hours</span>
                            </div>
                            @if($course->price > 0)
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($course->price, 2) }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Free</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 ml-4">
                        @if(auth()->check() && (auth()->user()->hasAnyRole(['admin', 'supervisor']) || (auth()->user()->hasRole('teacher') && $course->instructor_id === auth()->id())))
                            {{-- Teacher/Admin/Supervisor Actions --}}
                            @if(auth()->user()->hasRole('teacher') && $course->instructor_id === auth()->id())
                                <flux:button href="{{ route('courses.edit', $course) }}" variant="outline" wire:navigate>
                                    Edit Course
                                </flux:button>
                                <flux:button href="{{ route('curriculum.builder', $course) }}" variant="primary" wire:navigate>
                                    Curriculum Builder
                                </flux:button>
                            @endif
                            
                            {{-- Enrollment Management Button (for all admins, supervisors, and course instructor) --}}
                            @if(in_array($course->enrollment_type ?? 'open', ['invite_only', 'approval_required']))
                                <flux:button href="{{ route('courses.enrollments', $course) }}" variant="ghost" wire:navigate>
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Manage Enrollments
                                </flux:button>
                            @endif

                            {{-- Admin/Supervisor Quick Actions --}}
                            @if(auth()->user()->hasAnyRole(['admin', 'supervisor']))
                                <flux:button href="{{ route('courses.edit', $course) }}" variant="outline" wire:navigate>
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Course
                                </flux:button>
                                <flux:button href="{{ route('courses.enrollments', $course) }}" variant="primary" wire:navigate>
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    View All Enrollments
                                </flux:button>
                            @endif
                        @elseif(!$enrolled && auth()->check())
                            @php
                                $enrollmentType = $course->enrollment_type ?? 'invite_only';
                            @endphp
                            
                            @if($enrollmentType === 'invite_only')
                                {{-- Check if user has an invitation --}}
                                @php
                                    $hasInvitation = \App\Models\CourseInvitation::where('course_id', $course->id)
                                        ->where('user_id', auth()->id())
                                        ->where('status', 'pending')
                                        ->exists();
                                @endphp
                                
                                @if($hasInvitation)
                                    <flux:button wire:click="enroll" variant="primary" class="px-6 py-3 text-base font-medium">
                                        Accept Invitation & Enroll
                                    </flux:button>
                                @else
                                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Invitation Required</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">This course requires an invitation from the instructor to enroll</p>
                                    </div>
                                @endif
                            @elseif($enrollmentType === 'approval_required')
                                @php
                                    $hasPendingRequest = \App\Models\EnrollmentRequest::where('course_id', $course->id)
                                        ->where('user_id', auth()->id())
                                        ->where('status', 'pending')
                                        ->exists();
                                @endphp
                                
                                @if($hasPendingRequest)
                                    <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Request Pending</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Waiting for instructor approval</p>
                                    </div>
                                @else
                                    <flux:button wire:click="enroll" variant="primary" class="px-6 py-3 text-base font-medium">
                                        Request to Enroll
                                    </flux:button>
                                @endif
                            @else
                                <flux:button wire:click="enroll" variant="primary" class="px-6 py-3 text-base font-medium">
                                    Enroll Now
                                </flux:button>
                            @endif
                        @elseif($enrolled)
                            <span class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">✓ Enrolled</span>
                            <flux:button href="{{ route('courses.learn', $course) }}" variant="primary" class="px-6 py-3 text-base font-medium" wire:navigate>
                                Continue Learning
                            </flux:button>
                        @elseif(!auth()->check())
                            <flux:button href="{{ route('login') }}" variant="primary" class="px-6 py-3 text-base font-medium" wire:navigate>
                                Sign In to Enroll
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Description --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">About This Course</h2>
                    </div>
                    <div class="p-6 prose dark:prose-invert max-w-none">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                </div>

                {{-- What You'll Learn --}}
                @if(count($course->what_you_learn ?? []) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">What You'll Learn</h2>
                        </div>
                        <div class="p-6">
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($course->what_you_learn as $outcome)
                                    <li class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">{{ $outcome }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Course Content --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Course Content</h2>
                    </div>
                    <div class="p-6">
                        @if($modules->count() > 0)
                            <div class="space-y-4">
                                @foreach($modules as $module)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $module->title }}</h3>
                                                    @if($module->description)
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $module->description }}</p>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-4">{{ $module->lessons->count() }} lessons</span>
                                            </div>
                                        </div>
                                        @if($module->lessons->count() > 0)
                                            @if($enrolled)
                                                {{-- Show full lesson details for enrolled students --}}
                                                <div class="p-4 space-y-2">
                                                    @foreach($module->lessons as $lesson)
                                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                            <div class="flex items-center gap-3">
                                                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $lesson->title }}</span>
                                                            </div>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->duration_minutes }} min</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{-- Show preview for non-enrolled students (first 2 lessons only) --}}
                                                <div class="p-4 space-y-2">
                                                    @foreach($module->lessons->take(2) as $lesson)
                                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                                            <div class="flex items-center gap-3">
                                                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                                </svg>
                                                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $lesson->title }}</span>
                                                            </div>
                                                            <span class="text-xs text-gray-400 dark:text-gray-600">{{ $lesson->duration_minutes }} min</span>
                                                        </div>
                                                    @endforeach
                                                    @if($module->lessons->count() > 2)
                                                        <div class="flex items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-800">
                                                            <div class="text-center">
                                                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                                </svg>
                                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $module->lessons->count() - 2 }} more lessons</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enroll to unlock all content</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600 dark:text-gray-400">Course content coming soon...</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Course Stats --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                        <h3 class="font-bold text-gray-900 dark:text-white">Course Statistics</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Enrollments</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $course->enrollments()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Lessons</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $course->lessons()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Modules</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $course->modules()->count() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Requirements --}}
                @if(count($course->requirements ?? []) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20">
                            <h3 class="font-bold text-gray-900 dark:text-white">Requirements</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-2">
                                @foreach($course->requirements as $requirement)
                                    <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span>{{ $requirement }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Tags --}}
                @if(count($course->tags ?? []) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white">Tags</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-2">
                                @foreach($course->tags as $tag)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Similar Courses --}}
        @if($similarCourses->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Similar Courses</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($similarCourses as $similarCourse)
                            <a href="{{ route('courses.show', $similarCourse) }}" class="block group" wire:navigate>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300">
                                    @if($similarCourse->featured_image)
                                        <img src="{{ asset('storage/' . $similarCourse->featured_image) }}" 
                                             alt="{{ $similarCourse->title }}" 
                                             class="h-32 w-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="h-32 w-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                            <span class="text-4xl font-bold text-white opacity-80">{{ substr($similarCourse->title, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $similarCourse->title }}</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $similarCourse->instructor->name }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if(session()->has('message'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // You can replace this with a toast notification library
            setTimeout(() => {
                alert('{{ session('message') }}');
            }, 100);
        });
    </script>
@endif
