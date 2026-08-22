{{-- Other Forms --}}
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $selectedId ? 'Edit' : 'Create' }} {{ ucfirst($selectedType) }}
            </h2>
            <button wire:click="closeForm" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <p class="text-gray-600 dark:text-gray-400">Form for {{ $selectedType }} will go here</p>
        </div>
    </div>
</div>
