<div class="flex flex-col gap-6 p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Certificate</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ $certificate->title ?? 'Certificate of Completion' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <flux:button href="{{ route('certificates.view', $certificate) }}" variant="outline">
                View PDF
            </flux:button>
            <flux:button href="{{ route('certificates.download', $certificate) }}" variant="primary">
                Download PDF
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Preview</h2>
                </div>
                <div class="p-4">
                    <iframe
                        title="Certificate Preview"
                        src="{{ route('certificates.view', $certificate) }}"
                        class="w-full h-[640px] border border-gray-200 dark:border-gray-700 rounded-md"
                    ></iframe>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Student</h3>
                <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $certificate->user?->name ?? 'N/A' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Course</h3>
                <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $certificate->course?->title ?? 'N/A' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Certificate Number</h3>
                <p class="mt-2 text-gray-700 dark:text-gray-300 font-mono">{{ $certificate->certificate_number ?? 'N/A' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Issued Date</h3>
                <p class="mt-2 text-gray-700 dark:text-gray-300">
                    {{ $certificate->issued_at?->format('F d, Y') ?? $certificate->created_at?->format('F d, Y') ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>
</div>
