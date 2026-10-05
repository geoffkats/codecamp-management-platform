<x-layouts.app :title="__('Help & What\'s new')">
    <div class="mx-auto max-w-5xl p-4 sm:p-6">
        <div class="flex h-[calc(100vh-3rem)] min-h-[32rem] flex-col overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-800">
            @include('help.panel')
        </div>
    </div>
</x-layouts.app>
