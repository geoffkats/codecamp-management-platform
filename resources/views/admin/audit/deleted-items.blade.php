<x-layouts.app title="Restore Deleted Items">
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.audit.logs') }}" class="text-blue-600 hover:text-blue-900">← Back to Logs</a>
            <h1 class="text-3xl font-bold text-gray-900">Restore Deleted Items</h1>
        </div>
        <p class="mt-2 text-sm text-gray-600">Select a deleted item to restore or permanently delete</p>
    </div>

    <!-- Filter by Type -->
    @if(!$modelType)
        <div class="flex gap-2 mb-6">
            <a href="{{ route('admin.audit.deleted-items') }}" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200">
                All Types
            </a>
            <a href="{{ route('admin.audit.deleted-items', ['type' => 'Course']) }}" class="px-4 py-2 rounded-lg hover:bg-blue-100">
                Courses
            </a>
            <a href="{{ route('admin.audit.deleted-items', ['type' => 'Lesson']) }}" class="px-4 py-2 rounded-lg hover:bg-blue-100">
                Lessons
            </a>
            <a href="{{ route('admin.audit.deleted-items', ['type' => 'Assessment']) }}" class="px-4 py-2 rounded-lg hover:bg-blue-100">
                Assessments
            </a>
            <a href="{{ route('admin.audit.deleted-items', ['type' => 'Quiz']) }}" class="px-4 py-2 rounded-lg hover:bg-blue-100">
                Quizzes
            </a>
            <a href="{{ route('admin.audit.deleted-items', ['type' => 'Assignment']) }}" class="px-4 py-2 rounded-lg hover:bg-blue-100">
                Assignments
            </a>
        </div>
    @endif

    <!-- Courses -->
    @if($deletedCourses->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900">🗑️ Deleted Courses ({{ $deletedCourses->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($deletedCourses as $course)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $course->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $course->instructor?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $course->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button class="text-green-600 hover:text-green-900 restore-btn" 
                                        data-model-type="Course" data-model-id="{{ $course->id }}">
                                        Restore
                                    </button>
                                    <a href="{{ route('admin.audit.show', ['Course', $course->id]) }}" class="text-blue-600 hover:text-blue-900">
                                        History
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 delete-btn"
                                        data-model-type="Course" data-model-id="{{ $course->id }}">
                                        Permanently Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Lessons -->
    @if($deletedLessons->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900">🗑️ Deleted Lessons ({{ $deletedLessons->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($deletedLessons as $lesson)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $lesson->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $lesson->course?->title ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $lesson->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button class="text-green-600 hover:text-green-900 restore-btn"
                                        data-model-type="Lesson" data-model-id="{{ $lesson->id }}">
                                        Restore
                                    </button>
                                    <a href="{{ route('admin.audit.show', ['Lesson', $lesson->id]) }}" class="text-blue-600 hover:text-blue-900">
                                        History
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 delete-btn"
                                        data-model-type="Lesson" data-model-id="{{ $lesson->id }}">
                                        Permanently Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Assessments -->
    @if($deletedAssessments->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900">🗑️ Deleted Assessments ({{ $deletedAssessments->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Lesson</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($deletedAssessments as $assessment)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $assessment->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assessment->lesson?->title ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assessment->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button class="text-green-600 hover:text-green-900 restore-btn"
                                        data-model-type="Assessment" data-model-id="{{ $assessment->id }}">
                                        Restore
                                    </button>
                                    <a href="{{ route('admin.audit.show', ['Assessment', $assessment->id]) }}" class="text-blue-600 hover:text-blue-900">
                                        History
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 delete-btn"
                                        data-model-type="Assessment" data-model-id="{{ $assessment->id }}">
                                        Permanently Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Quizzes -->
    @if($deletedQuizzes->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900">🗑️ Deleted Quizzes ({{ $deletedQuizzes->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Lesson</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($deletedQuizzes as $quiz)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $quiz->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $quiz->lesson?->title ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $quiz->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button class="text-green-600 hover:text-green-900 restore-btn"
                                        data-model-type="Quiz" data-model-id="{{ $quiz->id }}">
                                        Restore
                                    </button>
                                    <a href="{{ route('admin.audit.show', ['Quiz', $quiz->id]) }}" class="text-blue-600 hover:text-blue-900">
                                        History
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 delete-btn"
                                        data-model-type="Quiz" data-model-id="{{ $quiz->id }}">
                                        Permanently Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Assignments -->
    @if($deletedAssignments->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900">🗑️ Deleted Assignments ({{ $deletedAssignments->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Lesson</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Deleted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($deletedAssignments as $assignment)
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $assignment->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->lesson?->title ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button class="text-green-600 hover:text-green-900 restore-btn"
                                        data-model-type="Assignment" data-model-id="{{ $assignment->id }}">
                                        Restore
                                    </button>
                                    <a href="{{ route('admin.audit.show', ['Assignment', $assignment->id]) }}" class="text-blue-600 hover:text-blue-900">
                                        History
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 delete-btn"
                                        data-model-type="Assignment" data-model-id="{{ $assignment->id }}">
                                        Permanently Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($deletedCourses->isEmpty() && $deletedLessons->isEmpty() && $deletedAssessments->isEmpty() && $deletedQuizzes->isEmpty() && $deletedAssignments->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <p class="text-gray-500 text-lg">No deleted items found</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const parseResponse = async (response) => {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        try {
            return isJson ? await response.json() : { message: await response.text() };
        } catch (error) {
            return { message: 'Unexpected response format.' };
        }
    };

    // Restore button
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (modelType === 'Course') {
                if (!confirm('Restore this course and all its modules, lessons, quizzes, and assignments?')) return;
            } else {
                if (!confirm('Restore this item?')) return;
            }

            const modelType = this.dataset.modelType;
            const modelId = this.dataset.modelId;

            try {
                const response = await fetch('{{ route("admin.audit.restore") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ model_type: modelType, model_id: modelId })
                });

                const data = await parseResponse(response);
                if (response.ok) {
                    alert(data.message || 'Restored');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Request failed'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    // Delete button
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Permanently delete this item? This cannot be undone.')) return;

            const modelType = this.dataset.modelType;
            const modelId = this.dataset.modelId;

            try {
                const response = await fetch('{{ route("admin.audit.force-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ model_type: modelType, model_id: modelId })
                });

                const data = await parseResponse(response);
                if (response.ok) {
                    alert(data.message || 'Deleted');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Request failed'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
});
</script>
</x-layouts.app>
