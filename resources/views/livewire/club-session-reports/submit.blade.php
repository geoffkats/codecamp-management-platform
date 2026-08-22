<div class="max-w-3xl mx-auto py-8 px-4 space-y-6">
    @if(session('message'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm font-semibold text-green-800">{{ session('message') }}</div>
    @endif

    <div>
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Submit Club Session Report</h1>
        <p class="text-sm text-gray-500 mt-1">Record what happened in today's Code Club session.</p>
    </div>

    <form wire:submit="submit" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-500">Session Date *</label>
                <input type="date" wire:model.live="sessionDate" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                @error('sessionDate') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Club *</label>
                <select wire:model.live="clubId" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                    <option value="">Select club</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                    @endforeach
                </select>
                @error('clubId') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500">Attendance Count *</label>
            <input type="number" wire:model="attendanceCount" min="0" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
            @error('attendanceCount') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500">Summary *</label>
            <textarea wire:model="summary" rows="4" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
            @error('summary') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500">Topics Taught</label>
            <textarea wire:model="topicsCovered" rows="2" placeholder="What topics or concepts were covered today?" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500">New Techniques Introduced</label>
            <textarea wire:model="newTechniques" rows="2" placeholder="Any new coding techniques, tools, or skills introduced?" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
        </div>

        <div>
            <label class="text-xs font-semibold text-gray-500">Challenges</label>
            <textarea wire:model="challenges" rows="2" placeholder="Difficulties faced during the session" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-500">Rate Teamwork *</label>
                <div class="flex gap-1 mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click="$set('teamworkRating', {{ $i }})" class="text-2xl transition-colors {{ ($teamworkRating ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400" aria-label="Teamwork {{ $i }} of 5">★</button>
                    @endfor
                </div>
                <p class="text-xs text-gray-500 mt-1">1 = Poor, 5 = Excellent</p>
                @error('teamworkRating') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Collaboration Rating *</label>
                <div class="flex gap-1 mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click="$set('collaborationRating', {{ $i }})" class="text-2xl transition-colors {{ ($collaborationRating ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400" aria-label="Collaboration {{ $i }} of 5">★</button>
                    @endfor
                </div>
                <p class="text-xs text-gray-500 mt-1">1 = Poor, 5 = Excellent</p>
                @error('collaborationRating') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="followUpRequired" class="rounded border-gray-300" />
            Follow-up required
        </label>

        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Submit Report</button>
    </form>
</div>
