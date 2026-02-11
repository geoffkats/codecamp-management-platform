<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Schools & Teacher Assignments</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage schools and bind ICT teachers to institutions.</p>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session()->has('message'))
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <div class="flex gap-2">
            <button wire:click="setTab('schools')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'schools' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">Schools</button>
            <button wire:click="setTab('teachers')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'teachers' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">Teacher Assignments</button>
        </div>

        @if($activeTab === 'schools')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $schoolId ? 'Edit School' : 'Add School' }}</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Name *</label>
                            <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Code (auto if blank)</label>
                            <input type="text" wire:model="code" placeholder="Leave blank to auto-generate" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                            <input type="text" wire:model="address" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="saveSchool" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save</button>
                            @if($schoolId)
                                <button wire:click="resetSchoolForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg">Cancel</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Schools</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Students</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">ICT Teachers</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Assessment Attempts</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Pass Rate</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($schools as $school)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $school->name }}
                                            @if($school->address)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $school->address }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $school->code ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $school->students_count }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $school->school_teachers_count }}</td>
                                        @php
                                            $performance = $performanceBySchool->get($school->id);
                                            $totalAttempts = $performance?->total_attempts ?? 0;
                                            $passedAttempts = $performance?->passed_attempts ?? 0;
                                            $passRate = $totalAttempts > 0 ? ($passedAttempts / $totalAttempts) * 100 : null;
                                        @endphp
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $totalAttempts ? number_format($totalAttempts) : '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $passRate !== null ? number_format($passRate, 1) . '%' : '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('admin.schools.show', $school->id) }}" wire:navigate class="inline-flex items-center justify-center w-8 h-8 rounded-md text-green-600 hover:text-green-800 hover:bg-green-50 dark:hover:bg-green-900/20" title="View">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                                        <circle cx="12" cy="12" r="3.25" />
                                                    </svg>
                                                    <span class="sr-only">View</span>
                                                </a>
                                                <button wire:click="editSchool({{ $school->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651a1.5 1.5 0 0 1 0 2.121l-9.9 9.9a4.5 4.5 0 0 1-1.897 1.102l-3.192.957.957-3.192a4.5 4.5 0 0 1 1.102-1.897l9.9-9.9a1.5 1.5 0 0 1 2.121 0Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 8.25 17.25" />
                                                    </svg>
                                                    <span class="sr-only">Edit</span>
                                                </button>
                                                <button wire:click="deleteSchool({{ $school->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12M9.75 7.5V6a1.5 1.5 0 0 1 1.5-1.5h1.5A1.5 1.5 0 0 1 14.25 6v1.5m-6 0 1 12a1.5 1.5 0 0 0 1.5 1.5h3.5a1.5 1.5 0 0 0 1.5-1.5l1-12" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 11v6m3-6v6" />
                                                    </svg>
                                                    <span class="sr-only">Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No schools created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($schools->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $schools->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($activeTab === 'teachers')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assign Teacher Profile</h2>
                    <div class="space-y-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="createNewTeacher" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            Create new teacher user
                        </label>

                        @if(!$createNewTeacher)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter Teachers</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="$set('teacherRoleFilter','all')" class="px-3 py-1.5 text-xs rounded-full {{ $teacherRoleFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">
                                    All
                                </button>
                                <button type="button" wire:click="$set('teacherRoleFilter','ict_teacher')" class="px-3 py-1.5 text-xs rounded-full {{ $teacherRoleFilter === 'ict_teacher' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">
                                    ICT Teachers
                                </button>
                                <button type="button" wire:click="$set('teacherRoleFilter','codecamp_trainer')" class="px-3 py-1.5 text-xs rounded-full {{ $teacherRoleFilter === 'codecamp_trainer' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">
                                    CodeCamp Trainers
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Teachers</label>
                            <input type="text" wire:model.live.debounce.300ms="teacherSearch" placeholder="Search by name or email" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teacher User *</label>
                            <select wire:model="teacherUserId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select Teacher</option>
                                @foreach($availableTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                                @endforeach
                            </select>
                            @error('teacherUserId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                            <input type="text" wire:model="newTeacherName" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('newTeacherName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                            <input type="email" wire:model="newTeacherEmail" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('newTeacherEmail') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                            <input type="password" wire:model="newTeacherPassword" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('newTeacherPassword') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password *</label>
                            <input type="password" wire:model="newTeacherPasswordConfirmation" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            @error('newTeacherPasswordConfirmation') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role *</label>
                            <select wire:model="teacherRole" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="ict_teacher">ICT Teacher</option>
                                <option value="codecamp_trainer">CodeCamp Trainer</option>
                            </select>
                            @error('teacherRole') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School (ICT only) *</label>
                            <select wire:model="teacherSchoolId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Select School</option>
                                @foreach($schoolOptions as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                            @error('teacherSchoolId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <button wire:click="assignTeacher" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Assignment</button>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Teacher Profiles</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Teacher</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">School</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($teacherProfiles as $profile)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $profile->user->name }}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $profile->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $profile->role === 'ict_teacher' ? 'ICT Teacher' : 'CodeCamp Trainer' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $profile->school?->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <button wire:click="removeTeacherProfile({{ $profile->id }})" class="text-red-600 hover:text-red-800">Remove</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No teacher profiles assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($teacherProfiles->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $teacherProfiles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
