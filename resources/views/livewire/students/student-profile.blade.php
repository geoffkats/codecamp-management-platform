<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('students.index') }}" wire:navigate class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Student Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $student->student_id }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="exportPDF" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Export PDF
                </button>
                <button wire:click="downloadData" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Download Data
                </button>
                @if(auth()->user()->hasRole('teacher') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
                <a href="{{ route('students.teacher-update', $student->id) }}" wire:navigate class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Teacher Update
                </a>
                @endif
                @if(auth()->user()->hasRole('operations_manager') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
                <a href="{{ route('students.edit', $student->id) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Edit Profile
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session()->has('message'))
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-blue-800 dark:text-blue-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Personal Information Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-6 mb-6">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-3xl">
                        {{ strtoupper(substr($student->full_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $student->student_id }}</p>
                        <div class="flex gap-4 mt-3">
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-full">
                                {{ $student->class_grade ?? 'No Class' }}
                            </span>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm rounded-full">
                                {{ ucfirst($student->gender ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Date of Birth</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nationality</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->nationality ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->user->email }}</p>
                    </div>
                    <div class="md:col-span-2 lg:col-span-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Address</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Parent/Guardian Information --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Parent/Guardian Information</h2>
            </div>
            <div class="p-6">
                @php
                    $parentData = $student->parent_data ?? [];
                @endphp
                
                @if(!empty($parentData['parent1']))
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Primary Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Name</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent1']['name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Relationship</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ ucfirst($parentData['parent1']['relationship'] ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Phone</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent1']['phone'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent1']['email'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Primary Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Name</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $student->parent_guardian_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Phone</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $student->parent_guardian_contact ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($parentData['parent2']))
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Secondary Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Name</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent2']['name'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Relationship</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ ucfirst($parentData['parent2']['relationship']) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Phone</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent2']['phone'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $parentData['parent2']['email'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Accounts Information --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Learning Accounts</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Scratch Account</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $student->scratch_account ?? 'Not set' }}</p>
                    @if($student->scratch_password)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Password: {{ $student->scratch_password }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">GitHub Account</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $student->github_account ?? 'Not set' }}</p>
                </div>
            </div>
        </div>

        {{-- Enrolled Courses --}}
        @if($student->user->enrollments->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Enrolled Courses</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($student->user->enrollments as $enrollment)
                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Status: <span class="font-medium">{{ ucfirst($enrollment->status) }}</span></p>
                    @if($enrollment->enrolled_at)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enrolled: {{ $enrollment->enrolled_at->format('M j, Y') }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Gadgets/Devices --}}
        @if($student->gadgets->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Registered Devices</h2>
            </div>
            <div class="p-6 space-y-4">
                @foreach($student->gadgets as $gadget)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Type</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->device_type }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Brand</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->brand ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Serial Number</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->serial_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Condition</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->condition ?? 'N/A' }}</p>
                        </div>
                        @if($gadget->ram)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">RAM</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->ram }}</p>
                        </div>
                        @endif
                        @if($gadget->storage)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Storage</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->storage }}</p>
                        </div>
                        @endif
                        @if($gadget->accessories)
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Accessories</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $gadget->accessories }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Uniform & Payment Status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Uniform & Fees</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Uniform Size</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $student->uniform_size ?? 'Not set' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">T-shirt Collected</p>
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $student->tshirt_collected ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $student->tshirt_collected ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Uniform Payment</p>
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $student->uniform_paid ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' }}">
                        {{ $student->uniform_paid ? 'Paid' : 'Pending' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
