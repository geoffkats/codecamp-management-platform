@props(['stats' => []])

@if (count($stats))
    <section class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950" aria-label="Platform at a glance">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:px-8 {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($stats)] ?? 'lg:grid-cols-4' }}">
            @foreach ($stats as $stat)
                <div class="flex flex-col-reverse gap-1 border-zinc-200 py-7 dark:border-zinc-800 sm:py-9 {{ $loop->odd ? 'pr-4' : 'pl-4 border-l' }} lg:border-l lg:px-8 {{ $loop->first ? 'lg:border-l-0 lg:pl-0' : '' }}" data-reveal>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</dt>
                    <dd class="text-3xl font-bold tracking-tight text-cau-navy tabular-nums sm:text-4xl dark:text-white">{{ number_format($stat['value']) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
