@props([
    'reviewQuestions' => [],
    'allowScoring' => false,
])

@if(count($reviewQuestions) > 0)
    <div class="space-y-3">
        @foreach($reviewQuestions as $row)
            <div @class([
                'rounded-xl border p-4',
                'border-green-300 dark:border-green-800 bg-green-50/70 dark:bg-green-900/15' => $row['needs_manual'] === false && $row['is_correct'] === true,
                'border-red-300 dark:border-red-800 bg-red-50/70 dark:bg-red-900/15' => $row['needs_manual'] === false && $row['is_correct'] === false,
                'border-amber-300 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/15' => $row['needs_manual'] === true,
                'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30' => $row['needs_manual'] === false && $row['is_correct'] === null,
            ])>
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Q{{ $row['number'] }}</span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/80 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                {{ $row['type_label'] }}
                            </span>
                            <span class="text-[11px] text-gray-500">{{ number_format((float) $row['points'], 1) }} pts</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $row['question']), 220) }}
                        </p>
                    </div>

                    @if($row['needs_manual'])
                        <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-amber-200/80 dark:bg-amber-900/50 text-amber-900 dark:text-amber-200">
                            Needs marking
                        </span>
                    @elseif($row['is_correct'] === true)
                        <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-green-200/80 dark:bg-green-900/50 text-green-900 dark:text-green-200">
                            Correct · {{ number_format((float) $row['earned'], 1) }}/{{ number_format((float) $row['points'], 1) }}
                        </span>
                    @elseif($row['is_correct'] === false)
                        <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-red-200/80 dark:bg-red-900/50 text-red-900 dark:text-red-200">
                            Incorrect · 0/{{ number_format((float) $row['points'], 1) }}
                        </span>
                    @endif
                </div>

                @if(!empty($row['options']))
                    <div class="mt-3 space-y-1.5">
                        @foreach($row['options'] as $option)
                            <div @class([
                                'flex items-start gap-2 rounded-lg px-3 py-2 text-sm border',
                                'border-green-400 bg-green-100/80 dark:bg-green-900/40 text-green-900 dark:text-green-100' => $option['is_correct'],
                                'border-red-300 bg-red-100/70 dark:bg-red-900/30 text-red-900 dark:text-red-100' => $option['selected'] && ! $option['is_correct'],
                                'border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300' => ! $option['selected'] && ! $option['is_correct'],
                            ])>
                                <span class="mt-0.5 text-xs font-bold w-4 flex-shrink-0">
                                    @if($option['selected'] && $option['is_correct']) ✓
                                    @elseif($option['selected']) ✗
                                    @elseif($option['is_correct']) ○
                                    @else ·
                                    @endif
                                </span>
                                <span class="flex-1">{{ $option['text'] }}</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wide flex-shrink-0 opacity-80">
                                    @if($option['selected']) Chosen @endif
                                    @if($option['selected'] && $option['is_correct']) · @endif
                                    @if($option['is_correct']) Correct @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @elseif($row['type'] === 'file_upload')
                    @if(!empty($row['files']))
                        <div class="mt-3 space-y-2">
                            @foreach($row['files'] as $file)
                                @php
                                    $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                    $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                @endphp
                                @if($filePath === '') @continue @endif
                                <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                   class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $fileName }}</span>
                                    <span class="ml-auto text-xs text-gray-400">Download</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-500 italic">No file uploaded</p>
                    @endif
                @elseif($row['type'] === 'code_submission')
                    @if(filled($row['student_answer']) && $row['student_answer'] !== '— No answer —')
                        <pre class="mt-3 text-xs font-mono bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ $row['student_answer'] }}</pre>
                    @else
                        <p class="mt-2 text-sm text-gray-500 italic">No code submitted</p>
                    @endif
                @else
                    <div class="mt-3 space-y-2">
                        <div class="rounded-lg bg-white/80 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 mb-1">Student answer</p>
                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $row['student_answer'] }}</p>
                        </div>
                        @if(! $row['needs_manual'] && filled($row['correct_answer']))
                            <div class="rounded-lg bg-green-100/60 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-3 py-2">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-green-700 dark:text-green-300 mb-1">Correct answer</p>
                                <p class="text-sm font-medium text-green-900 dark:text-green-100">{{ $row['correct_answer'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($allowScoring && $row['needs_manual'])
                    <div class="mt-3 flex items-center gap-3">
                        <label class="text-xs font-semibold text-amber-800 dark:text-amber-200">Marks</label>
                        <input
                            type="number"
                            wire:model.live="questionScores.{{ (string) $row['id'] }}"
                            min="0"
                            max="{{ $row['points'] }}"
                            step="0.5"
                            class="w-24 px-3 py-1.5 text-sm rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                        >
                        <span class="text-xs text-gray-500">/ {{ number_format((float) $row['points'], 1) }}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
