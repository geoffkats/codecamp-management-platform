<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Assignment Brief</h2>

    @if($assessment->description)
        <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <x-rich-text :content="$assessment->description" />
        </div>
    @endif

    @if($assessment->assignment_data && filled($assessment->assignment_data['instructions'] ?? null))
        <div class="mb-5 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Instructions</h3>
            <x-rich-text :content="$assessment->assignment_data['instructions']" class="text-blue-900 dark:text-blue-200 prose-blue" />
        </div>
    @endif

    @if($assessment->due_date)
        <p class="text-sm font-semibold text-red-600 dark:text-red-400 mb-4">
            Due {{ $assessment->due_date->format('l, M j, Y') }}
        </p>
    @endif

    @if(count($assessment->assignmentAttachments()) > 0)
        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Download Materials</h3>
            <ul class="space-y-2">
                @foreach($assessment->assignmentAttachments() as $file)
                    <li>
                        <a href="{{ \App\Support\RichContent::storageUrl($file['path']) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            {{ $file['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
