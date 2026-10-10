@can('delete', $course)
    @php $deadline = app(\App\Services\Courses\CourseArchiver::class)->restoreDeadline($course); @endphp
    <span class="mr-1 text-[11px] font-medium text-gray-400">
        {{ $deadline && $deadline->isFuture() ? 'Restore by '.$deadline->format('M j') : 'Restore period ended' }}
    </span>
    @if($deadline && $deadline->isFuture())
        <button type="button"
                wire:click="restoreCourse({{ $course->id }})"
                wire:loading.attr="disabled"
                wire:target="restoreCourse({{ $course->id }})"
                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 disabled:opacity-50">
            <flux:icon name="arrow-uturn-left" variant="micro" class="size-3.5" /> Restore
        </button>
    @endif
@endcan
