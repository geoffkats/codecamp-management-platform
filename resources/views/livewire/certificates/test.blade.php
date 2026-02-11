<div class="p-6 space-y-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Certificate Generator (Test)</h1>
        <p class="text-gray-600 dark:text-gray-400">Generate a sample certificate to preview how it looks.</p>
    </div>

    @if(session()->has('message'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Sample Details</h2>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Recipient</label>
                    <select wire:model="userId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Select a user</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})</option>
                        @endforeach
                    </select>
                    @error('userId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
                    <select wire:model="courseId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Select a course</option>
                        @foreach($availableCourses as $course)
                            <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                        @endforeach
                    </select>
                    @error('courseId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Certificate Title</label>
                    <input type="text" wire:model="title" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Issued Date</label>
                    <input type="date" wire:model="issuedAt" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('issuedAt') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Certificate Number (optional)</label>
                    <input type="text" wire:model="certificateNumber" placeholder="Auto-generate if empty" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('certificateNumber') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    @error('description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <flux:button wire:click="generateSample" variant="primary" class="w-full">
                    Generate Sample Certificate
                </flux:button>
            </div>

            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                Tip: Use this page to test layout and positioning before issuing real certificates.
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Preview</h2>
                    @if($sampleCertificate)
                        <div class="flex items-center gap-2">
                            <flux:button href="{{ route('certificates.view', $sampleCertificate) }}" variant="ghost">
                                Open PDF
                            </flux:button>
                            <flux:button href="{{ route('certificates.download', $sampleCertificate) }}" variant="outline">
                                Download
                            </flux:button>
                        </div>
                    @endif
                </div>

                @if($sampleCertificate)
                    <div class="p-4">
                        <iframe
                            title="Certificate Preview"
                            src="{{ route('certificates.view', $sampleCertificate) }}"
                            class="w-full h-[680px] border border-gray-200 dark:border-gray-700 rounded-md"
                        ></iframe>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p>No sample generated yet. Fill the form to create one.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
