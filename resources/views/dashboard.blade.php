<x-layouts.app>
    @php
        $user = auth()->user();
    @endphp

    @if($user->isAdmin())
        <livewire:dashboard.admin-dashboard />
    @elseif($user->hasRole('operations_manager'))
        <livewire:dashboard.operations-manager-dashboard />
    @elseif($user->isIctTeacher())
        <livewire:dashboard.ict-teacher-dashboard />
    @elseif($user->isTeacher())
        <livewire:dashboard.instructor-dashboard />
    @elseif($user->isSupervisor())
        <livewire:dashboard.supervisor-dashboard />
    @else
        {{-- Check if user has student profile --}}
        @if(!$user->studentProfile)
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-6 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">
                                    Student Registration Required
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p class="mb-3">
                                        Welcome to Code Academy Uganda! Your account has been created, but you need to be registered as a student before you can access courses and learning materials.
                                    </p>
                                    <p class="mb-3 font-medium">
                                        What you need to do:
                                    </p>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>Contact the Operations Manager or Administration</li>
                                        <li>Provide your personal information and parent/guardian details</li>
                                        <li>Complete the student registration process</li>
                                        <li>Once registered, you'll receive access to courses and invitations</li>
                                    </ul>
                                    <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                        <p class="font-semibold text-gray-900 dark:text-white mb-2">Contact Information:</p>
                                        <p class="text-gray-700 dark:text-gray-300">📧 Email: info@codeacademy.ug</p>
                                        <p class="text-gray-700 dark:text-gray-300">📞 Phone: +256 784 781926</p>
                                        <p class="text-gray-700 dark:text-gray-300">📍 Location: Mpererwe, Mugalu Zone, Kampala</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account Information Card --}}
                    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your Account Information</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Name:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Email:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->email ?: $user->student_id }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Pending Student Registration
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <livewire:dashboard.student-dashboard />
        @endif
    @endif
</x-layouts.app>

