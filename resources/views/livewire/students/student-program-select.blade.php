<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
    <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Add Student</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Choose the student program to load the correct intake form.</p>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('students.create-ict') }}" wire:navigate class="block p-6 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                <h2 class="text-xl font-semibold text-blue-900 dark:text-blue-200">ICT / ICDL Student</h2>
                <p class="text-sm text-blue-800/80 dark:text-blue-200/70 mt-2">Short, school-focused form with ICDL details and module selection.</p>
            </a>

            @if($this->canAccessCodeClub())
            <a href="{{ route('students.create-codeclub') }}" wire:navigate class="block p-6 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition">
                <h2 class="text-xl font-semibold text-emerald-900 dark:text-emerald-200">Code Club Student</h2>
                <p class="text-sm text-emerald-800/80 dark:text-emerald-200/70 mt-2">School club learner linked to a Code Club program.</p>
            </a>
            @endif

            @if(!auth()->user()->isIctTeacher())
            <a href="{{ route('students.create-codecamp') }}" wire:navigate class="block p-6 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                <h2 class="text-xl font-semibold text-purple-900 dark:text-purple-200">CodeCamp Student</h2>
                <p class="text-sm text-purple-800/80 dark:text-purple-200/70 mt-2">Full intake form with parent/guardian, devices, and program details.</p>
            </a>
            @endif
        </div>
    </div>
</div>
