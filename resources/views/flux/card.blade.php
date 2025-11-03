@blaze

@php
$classes = Flux::classes()
    ->add('rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-800 shadow-xs')
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-card>
    {{ $slot }}
</div>

