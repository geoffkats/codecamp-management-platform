@props([
    'text' => '',
    'scratchScale' => 0.7,
])

@php
    $segments = \App\Support\QuestionText::segments((string) $text);
    $hash = substr(md5((string) $text), 0, 10);
@endphp

@if($segments !== [])
    <div {{ $attributes->merge(['class' => 'space-y-3 break-words']) }}>
        @foreach($segments as $index => $segment)
            @if($segment['type'] === 'text')
                <p class="whitespace-pre-line">{!! \App\Support\QuestionText::inline($segment['content']) !!}</p>
            @elseif($segment['type'] === 'code')
                <div wire:ignore wire:key="qt-{{ $hash }}-{{ $index }}"
                     class="not-prose overflow-hidden rounded-xl bg-slate-900 text-left text-sm font-normal shadow-sm ring-1 ring-slate-800">
                    <div class="flex items-center gap-1.5 border-b border-white/5 bg-white/5 px-3 py-1.5">
                        <span class="size-2.5 rounded-full bg-rose-400/80"></span>
                        <span class="size-2.5 rounded-full bg-amber-400/80"></span>
                        <span class="size-2.5 rounded-full bg-emerald-400/80"></span>
                        <span class="ml-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $segment['label'] }}</span>
                    </div>
                    <pre class="m-0 overflow-x-auto bg-transparent px-4 py-3 leading-relaxed"><code class="question-code block whitespace-pre font-mono text-[13px] text-slate-100" data-language="{{ $segment['language'] }}" x-data x-init="window.cauHighlightCode?.($el)">{{ $segment['content'] }}</code></pre>
                </div>
            @else
                <div wire:ignore wire:key="qs-{{ $hash }}-{{ $index }}" x-data x-init="window.cauRenderScratch?.($el)" data-scratch data-scale="{{ $scratchScale }}"
                     class="not-prose overflow-x-auto rounded-xl bg-white p-3 text-left font-normal shadow-sm ring-1 ring-amber-200 dark:bg-gray-50">
                    <div class="mb-2 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-600">
                        <flux:icon name="puzzle-piece" variant="micro" class="size-3.5" /> Scratch blocks
                    </div>
                    <div data-target></div>
                    <pre data-source class="m-0 whitespace-pre bg-transparent font-mono text-[13px] leading-relaxed text-gray-700">{{ $segment['content'] }}</pre>
                </div>
            @endif
        @endforeach
    </div>
@endif
