@can('delete', $course)
    <button type="button"
            wire:click="archiveCourse({{ $course->id }})"
            wire:confirm="Delete “{{ $course->title }}”?{{ $course->enrollments_count ? ' '.$course->enrollments_count.' enrolled '.\Illuminate\Support\Str::plural('student', $course->enrollments_count).' will no longer see it.' : '' }} You can restore it from Archived within {{ $restoreWindowDays }} days."
            wire:loading.attr="disabled"
            wire:target="archiveCourse({{ $course->id }})"
            title="Delete course"
            class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600 disabled:opacity-50 dark:hover:bg-red-500/10">
        <flux:icon name="trash" variant="mini" class="size-4" />
    </button>
@endcan
