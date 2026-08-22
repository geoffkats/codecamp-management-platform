<div class="space-y-2">
    <div>
        <label for="import-default-class-grade" class="mb-1.5 block text-sm font-semibold text-slate-800 dark:text-slate-200">
            Default class / grade
            <span class="font-normal text-slate-500">(optional)</span>
        </label>
        <input
            id="import-default-class-grade"
            type="text"
            wire:model="importDefaultClassGrade"
            placeholder="e.g. P2.C, P.3, P2.A"
            maxlength="50"
            class="w-full max-w-xs rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white dark:focus:ring-amber-900"
        />
        @error('importDefaultClassGrade')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex max-w-lg cursor-pointer items-start gap-2.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800/50">
        <input type="checkbox" wire:model="importApplyClassToAll" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
        <span class="text-xs text-slate-600 dark:text-slate-300">
            <span class="font-semibold text-slate-800 dark:text-slate-200">Apply to every student</span>
            — use the value above for all rows, even if the file already has a class (e.g. replace with P2.C).
        </span>
    </label>

    <p class="max-w-lg text-xs text-slate-500 dark:text-slate-400">
        <strong>Different streams (P2.C, P2.E, …)?</strong> Put each student’s class in the
        <span class="font-mono">class_grade</span> column in your file — one class per row.
        Leave this default empty if every student already has a class in the sheet.
        The file name may suggest a value here, but you can clear or change it (e.g. to <span class="font-mono">P2.C</span>).
    </p>
</div>
