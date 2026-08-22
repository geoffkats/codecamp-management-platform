@if($showCourseSettings)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60" wire:click="closeCourseSettings"></div>
        <div class="relative w-full max-w-5xl max-h-[85vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course settings</h3>
                <button type="button" wire:click="closeCourseSettings"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Close
                </button>
            </div>
            <div class="p-6">
                @include('livewire.curriculum.new-builder.partials.manage.course-overview')
            </div>
        </div>
    </div>
@endif
