@props(['label', 'value' => '—'])

<div {{ $attributes }}>
    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $label }}</p>
    <p class="mt-1 whitespace-pre-wrap font-semibold text-slate-900 dark:text-white">{{ filled($value) ? $value : '—' }}</p>
</div>
