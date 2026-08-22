{{-- Debug: Form should show but conditions not met --}}
<div class="p-8">
    <div class="bg-red-100 dark:bg-red-900/20 p-6 rounded-lg">
        <h2 class="text-xl font-bold text-red-900 dark:text-red-100 mb-4">Debug: Form State</h2>
        <p>showForm: {{ $showForm ? 'TRUE' : 'FALSE' }}</p>
        <p>selectedType: {{ $selectedType ?? 'NULL' }}</p>
        <p>selectedId: {{ $selectedId ?? 'NULL' }}</p>
        <p>courseId: {{ $courseId ?? 'NULL' }}</p>
        <p>course exists: {{ $course ? 'YES' : 'NO' }}</p>
    </div>
</div>
