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
                <a href="{{ route('students.print-credentials', $student->id) }}" target="_blank" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Print Credentials
                </a>
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
                <a href="{{ $student->program_type === 'codeclub' && config('features.code_club', false) ? route('students.edit-codeclub', $student->id) : route('students.edit', $student->id) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
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
                    @if($student->user)
                    <x-user-avatar :user="$student->user" size="xl" rounded="full" class="ring-2 ring-gray-200 dark:ring-gray-700" />
                    @else
                    <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold text-3xl">
                        {{ strtoupper(substr($student->full_name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $student->student_id }}</p>
                        @if($student->icdl_number)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ICDL: {{ $student->icdl_number }}</p>
                        @endif
                        <div class="flex gap-4 mt-3">
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-full">
                                {{ $student->class_grade ?? 'No Class' }}
                            </span>
                            @php
                                $categoryLabel = match($student->student_category ?? 'codecamp') {
                                    'school_club' => 'School Club',
                                    'ict_school' => 'ICT School',
                                    default => 'Codecamp',
                                };
                            @endphp
                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm rounded-full">
                                {{ $categoryLabel }}
                            </span>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm rounded-full">
                                {{ ucfirst($student->gender ?? 'N/A') }}
                            </span>
                            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm rounded-full">
                                {{ str_replace('_', ' ', $student->exam_readiness_status ?? 'not_ready') }}
                            </span>
                            <span class="px-3 py-1 {{ $student->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }} text-sm rounded-full">
                                {{ $student->is_active ? 'Active' : 'Removed' }}
                            </span>
                            @if($student->school)
                            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm rounded-full">
                                {{ $student->school->name }}
                            </span>
                            @endif
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
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Login ID</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->user?->loginIdentifier() ?: $student->student_id }}</p>
                    </div>
                    <div class="md:col-span-2 lg:col-span-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Address</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Achievements & Kudos ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Achievement Badges --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🏅 Achievement Badges</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $badges->count() }} earned</span>
                </div>
                <div class="p-5">
                    @if($badges->isEmpty())
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <div class="text-4xl mb-2">🔒</div>
                        <p class="text-sm">No badges earned yet — keep learning!</p>
                    </div>
                    @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        @foreach($badges as $badge)
                        <div
                            title="{{ $badge->name }}: {{ $badge->description }}"
                            class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:scale-105 transition-transform cursor-default group"
                        >
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center text-2xl shadow-sm"
                                style="background-color: {{ $badge->color }}22; border: 2px solid {{ $badge->color }}44"
                            >
                                {{ $badge->icon }}
                            </div>
                            <span class="text-[10px] text-center font-semibold text-gray-700 dark:text-gray-300 leading-tight line-clamp-2">
                                {{ $badge->name }}
                            </span>
                            <span class="text-[9px] text-gray-400 dark:text-gray-500">
                                {{ $badge->pivot->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->format('M j') : '' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Kudos --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">👏 Peer Kudos</h2>
                </div>
                <div class="p-5 flex flex-col gap-4">
                    {{-- Total count big display --}}
                    <div class="text-center">
                        <div class="text-5xl font-black text-pink-500">{{ number_format($kudosCount) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">total kudos received</div>
                    </div>

                    {{-- Give kudos widget (only for students, not own profile) --}}
                    @livewire('students.give-kudos', ['toUserId' => $student->user_id, 'toUserName' => $student->full_name], key('kudos-'.$student->user_id))

                    {{-- Recent kudos --}}
                    @if($recentKudos->isNotEmpty())
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Recent</p>
                        <div class="space-y-2">
                            @foreach($recentKudos as $kudo)
                            <div class="flex items-start gap-2 text-xs">
                                <span class="text-pink-400 mt-0.5">👏</span>
                                <div>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $kudo->sender?->name ?? 'Someone' }}</span>
                                    @if($kudo->message)
                                    <p class="text-gray-500 dark:text-gray-400 italic">"{{ $kudo->message }}"</p>
                                    @endif
                                    <p class="text-gray-400 dark:text-gray-500">{{ $kudo->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- ── /Achievements & Kudos ────────────────────────────────────────── --}}

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

        {{-- Learning Accounts (staff only — passwords never shown to students) --}}
        @if($canViewLearningAccounts)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Online Accounts</h2>
                @if($student->program_type === 'codeclub')
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Scratch and GitHub credentials for club activities</p>
                @endif
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
        @endif

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

    {{-- Camp Journey Timeline --}}
    @if($campHistory->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-2 mb-5">
            <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Camp Journey</h3>
        </div>

        <div class="relative">
            {{-- Vertical line --}}
            <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-600"></div>

            <div class="space-y-6">
                @foreach($campHistory as $enrollment)
                    @php
                        $statusColors = [
                            'active'      => ['dot' => 'bg-green-500', 'badge' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                            'completed'   => ['dot' => 'bg-gray-400',  'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'],
                            'transferred' => ['dot' => 'bg-blue-400',  'badge' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'],
                            'dropped'     => ['dot' => 'bg-red-400',   'badge' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'],
                        ];
                        $sc = $statusColors[$enrollment->status] ?? $statusColors['completed'];
                        $coursesInCamp = $campCourses[$enrollment->camp_id] ?? collect();
                    @endphp
                    <div class="flex gap-4 pl-2">
                        {{-- Dot --}}
                        <div class="relative flex-shrink-0 w-4 h-4 rounded-full {{ $sc['dot'] }} ring-2 ring-white dark:ring-gray-800 mt-0.5 z-10"></div>

                        <div class="flex-1 pb-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $enrollment->camp?->name ?? 'Unknown Camp' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $enrollment->enrolled_at->format('d M Y') }}
                                        @if($enrollment->completed_at)
                                            → {{ $enrollment->completed_at->format('d M Y') }}
                                        @endif
                                    </p>
                                    @if($enrollment->previousCamp)
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                                            ↑ transferred from {{ $enrollment->previousCamp->name }}
                                        </p>
                                    @endif
                                    @if($enrollment->status === 'transferred' && isset($transferDestinations[$enrollment->camp_id]))
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                                            → transferred to {{ $transferDestinations[$enrollment->camp_id]->camp?->name }}
                                        </p>
                                    @endif
                                </div>
                                <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-bold {{ $sc['badge'] }}">
                                    @if($enrollment->status === 'transferred' && isset($transferDestinations[$enrollment->camp_id]))
                                        transferred → {{ $transferDestinations[$enrollment->camp_id]->camp?->name ?? '—' }}
                                    @else
                                        {{ ucfirst($enrollment->status) }}
                                    @endif
                                </span>
                            </div>

                            @if($coursesInCamp->count())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach($coursesInCamp as $courseEnrollment)
                                        <span class="px-2 py-0.5 text-xs rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">
                                            {{ $courseEnrollment->course?->title ?? '—' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
