{{-- Module Form --}}
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 md:p-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $selectedId ? 'Edit Module' : 'Create New Module' }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Organize lessons into structured modules</p>
            </div>
            <button wire:click="closeForm" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="saveModule" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-5">
                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Module Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="formData.title"
                               class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="e.g., Introduction to Programming">
                        @error('formData.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea wire:model="formData.description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                  placeholder="Describe what students will learn in this module"></textarea>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                    {{ $selectedId ? 'Update Module' : 'Create Module' }}
                </button>
                <button type="button" wire:click="closeForm"
                        class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </form>

        @if($selectedId && $canManageCourse)
            <details class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <summary class="cursor-pointer list-none">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Module Settings</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Advanced</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Archive and admin controls</p>
                </summary>
                <div class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <h4 class="text-base font-bold text-red-700 dark:text-red-300 mb-2">Archive Module</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Archived modules and their lessons are hidden from students but can be restored from the Archived tab.</p>
                    <button type="button"
                            wire:click="deleteModule({{ $selectedId }})"
                            wire:confirm="Archive this module and all of its lessons? You can restore them later from the Archived tab."
                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                        Archive Module
                    </button>
                </div>
            </details>
        @endif
    </div>
</div>
