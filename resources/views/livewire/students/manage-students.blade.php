<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Students</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage student information</p>
        </div>
        <a href="{{ route('students.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            + Add Student
        </a>
    </div>

    {{-- Filters --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, ID, or contact..." 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        
        <select wire:model.live="filterClass" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">All Classes</option>
            @foreach($classes as $class)
                <option value="{{ $class }}">{{ $class }}</option>
            @endforeach
        </select>
    </div>

    {{-- Students Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parent Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gadgets</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Uniform</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $student->student_id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $student->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->class_grade ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->parent_guardian_contact }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $student->gadgets->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded {{ $student->uniform_paid ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200' }}">
                            {{ $student->uniform_paid ? 'Paid' : 'Pending' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm space-x-3">
                        <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-green-600 hover:text-green-700 dark:text-green-400">View</a>
                        <a href="{{ route('students.edit', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No students found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</div>
