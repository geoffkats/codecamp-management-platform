@php
    $input = 'mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white';
    $label = 'text-sm font-medium text-gray-700 dark:text-zinc-300';
@endphp

<div class="mx-auto max-w-3xl space-y-5 p-4 sm:p-6">
    <a href="{{ route('camp-revisions.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white">
        <flux:icon.arrow-left class="size-4" /> Revised content
    </a>

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Submit revised content</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Share what you improved while teaching this camp: updated slides, worksheets, project files or notes. Your supervisor reviews it like any other submission.</p>
    </div>

    <form wire:submit="save" class="space-y-5">
        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}" for="campId">Camp <span class="text-red-500">*</span></label>
                    <select id="campId" wire:model="campId" class="{{ $input }}">
                        <option value="">Select camp</option>
                        @foreach($camps as $camp)
                            <option value="{{ $camp->id }}">{{ $camp->name }} ({{ ucfirst($camp->status) }})</option>
                        @endforeach
                    </select>
                    @error('campId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="courseId">Course it replaces or improves</label>
                    <select id="courseId" wire:model="courseId" class="{{ $input }}">
                        <option value="">Not tied to one course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    @error('courseId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="{{ $label }}" for="title">Title <span class="text-red-500">*</span></label>
                <input id="title" type="text" wire:model="title" placeholder="e.g. Week 2 Python slides, reworked with more practice" class="{{ $input }}" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}" for="notes">What changed and why <span class="text-red-500">*</span></label>
                <textarea id="notes" wire:model="notes" rows="6" class="{{ $input }}"
                          placeholder="What did you change from the old content? What did students struggle with? What worked better?"></textarea>
                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Files <span class="text-red-500">*</span></h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-zinc-400">Slides, documents, Scratch (.sb3), Python, zip folders and images. Up to 15 files, 12 MB each.</p>

            <label class="mt-3 flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center hover:border-orange-300 dark:border-zinc-700">
                <flux:icon.arrow-up-tray class="size-6 text-orange-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-zinc-200">Choose files</span>
                <span class="text-xs text-gray-400" wire:loading wire:target="picked">Uploading…</span>
                <input type="file" wire:model="picked" multiple class="sr-only" />
            </label>
            @error('picked.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('uploads') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('uploads.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            @if($uploads)
                <ul class="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-100 dark:divide-zinc-800 dark:border-zinc-800">
                    @foreach($uploads as $i => $upload)
                        <li wire:key="upload-{{ $i }}" class="flex items-center gap-3 px-3 py-2 text-sm">
                            <flux:icon.document class="size-4 shrink-0 text-gray-400" />
                            <span class="min-w-0 flex-1 truncate text-gray-700 dark:text-zinc-200">{{ $upload->getClientOriginalName() }}</span>
                            <span class="text-xs text-gray-400">{{ number_format($upload->getSize() / 1024) }} KB</span>
                            <button type="button" wire:click="removeUpload({{ $i }})" class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="save,picked"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60">
                <flux:icon.paper-airplane class="size-4" /> Send to supervisor
            </button>
        </div>
    </form>
</div>
