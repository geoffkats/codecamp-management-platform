@php
    use App\Models\CampReport;
    use App\Models\SystemSetting;
    use App\Services\Reports\CampReportService;

    $orgName = SystemSetting::get('app_name', config('app.name'));
    $logo = SystemSetting::get('logo') ?: SystemSetting::get('logo_dark');
    $pct = fn ($value) => $value !== null ? $value.'%' : '–';
    $rateText = fn ($rate) => match (true) {
        $rate === null => 'text-gray-400',
        $rate >= 85 => 'text-emerald-700',
        $rate >= CampReportService::AT_RISK_RATE => 'text-amber-700',
        default => 'text-red-700',
    };
    $rateBar = fn ($rate) => match (true) {
        $rate >= 85 => 'bg-emerald-600',
        $rate >= CampReportService::AT_RISK_RATE => 'bg-amber-500',
        default => 'bg-red-600',
    };
    $followUp = $studentSummary['follow_up'];
    $followUpShown = array_slice($followUp, 0, 15);
    $reference = 'CAMP-'.str_pad((string) $camp->id, 3, '0', STR_PAD_LEFT).'-'.$camp->start_date->format('Y');

    $h2 = 'flex items-baseline gap-3 border-b-2 border-blue-950 pb-2 text-lg font-bold text-blue-950';
    $h3 = 'mt-6 mb-2 text-sm font-bold text-gray-900';
    $th = 'border-b border-gray-300 bg-gray-50 px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-600';
    $td = 'border-b border-gray-100 px-3 py-2';
    $textarea = 'mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-900';
@endphp

<div class="mx-auto max-w-5xl space-y-4 px-4 py-6 sm:px-6">
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.camp-reports.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All camp reports
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if($narrative)
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Written: {{ CampReport::SOURCES[$narrative->source] ?? $narrative->source }}
                    @if($narrative->source === 'manual' && $narrative->updatedBy) by {{ $narrative->updatedBy->name }} @endif
                </span>
            @endif
            @unless($editing)
                <button type="button" wire:click="startEditing"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    Edit wording
                </button>
            @endunless
            <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                    wire:confirm="Rewrite the summary and findings from the latest instructor reports and camp data? Any wording you edited will be replaced."
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                <span wire:loading.remove wire:target="generate">{{ $aiAvailable ? 'Rewrite with AI' : 'Recompile text' }}</span>
                <span wire:loading wire:target="generate">Writing…</span>
            </button>
            <a href="{{ route('admin.camps.report.pdf', $camp) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-950 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    @if(session('message'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">{{ session('message') }}</div>
    @endif
    @if(session('warning'))
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">{{ session('warning') }}</div>
    @endif

    {{-- The document --}}
    <article class="rounded-md bg-white text-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
        <div class="space-y-10 px-6 py-10 sm:px-14 sm:py-14">

            {{-- Letterhead --}}
            <header>
                <div class="flex items-start justify-between gap-6">
                    <div class="flex items-center gap-3">
                        @if($logo)
                            <img src="{{ asset('storage/'.$logo) }}" alt="{{ $orgName }}" class="h-12 w-12 object-contain">
                        @endif
                        <div>
                            <p class="text-base font-bold text-blue-950">{{ $orgName }}</p>
                            <p class="text-xs text-gray-500">Code Camp programme</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-600">End of camp report</p>
                        <p class="mt-1 text-xs text-gray-500">Ref. {{ $reference }}</p>
                    </div>
                </div>
                <div class="mt-5 h-1 bg-blue-950"></div>
                <div class="h-0.5 w-1/3 bg-orange-500"></div>

                <h1 class="mt-8 text-3xl font-bold tracking-tight text-gray-900">{{ $camp->name }}</h1>
                <dl class="mt-5 grid grid-cols-2 gap-x-8 gap-y-2 text-sm sm:grid-cols-3">
                    <div><dt class="text-gray-500">Reporting period</dt><dd class="font-medium text-gray-900">{{ $camp->date_range }}</dd></div>
                    <div><dt class="text-gray-500">Teaching days</dt><dd class="font-medium text-gray-900">{{ $period['teaching_days'] ?: '–' }}</dd></div>
                    <div><dt class="text-gray-500">Camp status</dt><dd class="font-medium text-gray-900">{{ ucfirst($camp->status) }}</dd></div>
                    <div><dt class="text-gray-500">Students enrolled</dt><dd class="font-medium text-gray-900">{{ $enrollment['total'] }}</dd></div>
                    <div><dt class="text-gray-500">Instructors reporting</dt><dd class="font-medium text-gray-900">{{ count($instructors) }}</dd></div>
                    <div><dt class="text-gray-500">Data as of</dt><dd class="font-medium text-gray-900">{{ $generatedAt->format('j M Y, H:i') }}</dd></div>
                </dl>
            </header>

            {{-- 1. Executive summary --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">1</span> Executive summary</h2>

                @if($editing)
                    <div class="mt-4 space-y-4 rounded-md border border-blue-200 bg-blue-50/50 p-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-800">Summary</label>
                            <textarea wire:model="summary" rows="6" class="{{ $textarea }}"></textarea>
                            @error('summary')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            @foreach(['highlights' => 'What went well', 'challenges' => 'Challenges', 'recommendations' => 'Recommendations'] as $field => $label)
                                <div>
                                    <label class="text-sm font-semibold text-gray-800">{{ $label }}</label>
                                    <textarea wire:model="{{ $field }}" rows="8" class="{{ $textarea }}"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">One point per line.</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="cancelEditing" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700">Cancel</button>
                            <button type="button" wire:click="save" class="rounded-md bg-blue-950 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-900">Save wording</button>
                        </div>
                    </div>
                @else
                    <p class="mt-4 leading-relaxed text-gray-800">{{ $narrative?->summary }}</p>
                @endif

                <div class="mt-6 grid grid-cols-2 border border-gray-200 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach([
                        ['Students', $enrollment['total'], $enrollment['active'].' active at close'],
                        ['Attendance', $pct($attendance['rate']), ($attendance['average_daily'] ?? 0).' students a day'],
                        ['Need follow-up', count($followUp), 'below '.CampReportService::AT_RISK_RATE.'% attendance'],
                        ['Daily reports', $reports['total'], count($instructors).' instructors'],
                        ['Average score', $pct($learning['average_score']), $learning['attempts'].' assessment attempts'],
                        ['Lesson rating', $feedback['average'] !== null ? $feedback['average'].' / 5' : '–', $feedback['count'].' responses'],
                    ] as [$label, $value, $hint])
                        <div class="border-b border-r border-gray-200 p-4 last:border-r-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $value }}</p>
                            <p class="text-xs text-gray-500">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 2. Enrolment --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">2</span> Enrolment</h2>
                <div class="mt-4 grid gap-8 md:grid-cols-2">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr><td class="{{ $td }} text-gray-600">Students enrolled</td><td class="{{ $td }} text-right font-semibold">{{ $enrollment['total'] }}</td></tr>
                            @if($enrollment['capacity'])
                                <tr><td class="{{ $td }} text-gray-600">Capacity used</td><td class="{{ $td }} text-right font-semibold">{{ $enrollment['total'] }} of {{ $enrollment['capacity'] }} ({{ $enrollment['fill_rate'] }}%)</td></tr>
                            @endif
                            <tr><td class="{{ $td }} text-gray-600">Active / completed at close</td><td class="{{ $td }} text-right font-semibold">{{ $enrollment['active'] }} / {{ $enrollment['completed'] }}</td></tr>
                            <tr><td class="{{ $td }} text-gray-600">Transferred / dropped</td><td class="{{ $td }} text-right font-semibold">{{ $enrollment['transferred'] }} / {{ $enrollment['dropped'] }}</td></tr>
                            @foreach($enrollment['gender'] as $label => $count)
                                <tr><td class="{{ $td }} text-gray-600">{{ $label }}</td><td class="{{ $td }} text-right font-semibold">{{ $count }}</td></tr>
                            @endforeach
                            @if($enrollment['average_age'])
                                <tr><td class="{{ $td }} text-gray-600">Average age</td><td class="{{ $td }} text-right font-semibold">{{ $enrollment['average_age'] }} years</td></tr>
                            @endif
                            @if($uniforms['total'])
                                <tr><td class="{{ $td }} text-gray-600">Uniforms paid</td><td class="{{ $td }} text-right font-semibold">{{ $uniforms['paid'] }} of {{ $uniforms['total'] }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                    @if($enrollment['classes'])
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Students by class</p>
                            <table class="w-full text-sm">
                                <tbody>
                                    @foreach($enrollment['classes'] as $class => $count)
                                        <tr>
                                            <td class="py-1.5 pr-3 text-gray-700">{{ $class }}</td>
                                            <td class="w-1/2 py-1.5"><div class="h-2 bg-gray-100"><div class="h-2 bg-blue-900" style="width: {{ $enrollment['total'] ? $count / $enrollment['total'] * 100 : 0 }}%"></div></div></td>
                                            <td class="w-10 py-1.5 text-right font-semibold">{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- 3. Attendance --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">3</span> Attendance</h2>

                @if($attendance['records'])
                    <p class="mt-4 text-sm leading-relaxed text-gray-700">
                        {{ number_format($attendance['records']) }} check-ins were recorded over {{ $period['teaching_days'] }} teaching days for {{ $attendance['students_tracked'] }} students.
                        Overall attendance was <strong>{{ $pct($attendance['rate']) }}</strong>, and {{ $pct($attendance['punctuality']) }} of students who came arrived on time.
                        @if($attendance['best_day']) The best day was {{ $attendance['best_day']['date']->format('l j M') }} ({{ $attendance['best_day']['rate'] }}%)@endif{{ $attendance['worst_day'] ? ' and the lowest was '.$attendance['worst_day']['date']->format('l j M').' ('.$attendance['worst_day']['rate'].'%).' : '.' }}
                    </p>

                    <div class="mt-6 grid gap-8 lg:grid-cols-5">
                        <div class="lg:col-span-3">
                            <h3 class="{{ $h3 }} mt-0">3.1 Daily attendance</h3>
                            <table class="w-full text-sm">
                                <thead><tr>
                                    <th class="{{ $th }}">Day</th><th class="{{ $th }} text-right">Present</th><th class="{{ $th }} text-right">Late</th><th class="{{ $th }} text-right">Absent</th><th class="{{ $th }} w-2/5">Attendance</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($attendanceDays as $day)
                                        <tr>
                                            <td class="{{ $td }} whitespace-nowrap">{{ $day['date']->format('D j M') }}</td>
                                            <td class="{{ $td }} text-right tabular-nums">{{ $day['present'] }}</td>
                                            <td class="{{ $td }} text-right tabular-nums">{{ $day['late'] }}</td>
                                            <td class="{{ $td }} text-right tabular-nums">{{ $day['absent'] }}</td>
                                            <td class="{{ $td }}">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-2 flex-1 bg-gray-100"><div class="h-2 {{ $rateBar($day['rate']) }}" style="width: {{ $day['rate'] }}%"></div></div>
                                                    <span class="w-10 text-right tabular-nums font-semibold {{ $rateText($day['rate']) }}">{{ $day['rate'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr class="font-semibold">
                                    <td class="px-3 py-2">Total</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($attendance['present']) }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($attendance['late']) }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($attendance['absent']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $pct($attendance['rate']) }}</td>
                                </tr></tfoot>
                            </table>
                        </div>

                        <div class="space-y-6 lg:col-span-2">
                            <div>
                                <h3 class="{{ $h3 }} mt-0">3.2 How regularly students attended</h3>
                                <table class="w-full text-sm">
                                    <tbody>
                                        @foreach($studentSummary['bands'] as $band)
                                            <tr>
                                                <td class="{{ $td }} pl-0">{{ $band['label'] }} <span class="text-xs text-gray-500">{{ $band['range'] }}</span></td>
                                                <td class="{{ $td }} text-right tabular-nums font-semibold">{{ $band['count'] }}</td>
                                                <td class="{{ $td }} w-14 pr-0 text-right tabular-nums text-gray-500">{{ $band['share'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <p class="mt-2 text-xs text-gray-500">{{ $studentSummary['perfect'] }} {{ Str::plural('student', $studentSummary['perfect']) }} attended every day on time.</p>
                            </div>

                            @if(count($studentSummary['by_class']) > 1)
                                <div>
                                    <h3 class="{{ $h3 }} mt-0">3.3 Attendance by class</h3>
                                    <table class="w-full text-sm">
                                        <thead><tr><th class="{{ $th }} pl-0">Class</th><th class="{{ $th }} text-right">Students</th><th class="{{ $th }} pr-0 text-right">Attendance</th></tr></thead>
                                        <tbody>
                                            @foreach($studentSummary['by_class'] as $row)
                                                <tr>
                                                    <td class="{{ $td }} pl-0">{{ $row['class'] }}</td>
                                                    <td class="{{ $td }} text-right tabular-nums">{{ $row['students'] }}</td>
                                                    <td class="{{ $td }} pr-0 text-right tabular-nums font-semibold {{ $rateText($row['rate']) }}">{{ $pct($row['rate']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <h3 class="{{ $h3 }}">3.4 Students who need follow-up</h3>
                    @if($followUp)
                        <p class="mb-3 text-sm text-gray-600">{{ count($followUp) }} {{ Str::plural('student', count($followUp)) }} attended less than {{ CampReportService::AT_RISK_RATE }}% of the days. Parents or schools should be contacted before the next camp.</p>
                        <table class="w-full text-sm">
                            <thead><tr>
                                <th class="{{ $th }}">Student</th><th class="{{ $th }}">Class</th><th class="{{ $th }} text-right">Days attended</th><th class="{{ $th }} text-right">Absent</th><th class="{{ $th }} text-right">Attendance</th>
                            </tr></thead>
                            <tbody>
                                @foreach($followUpShown as $student)
                                    <tr>
                                        <td class="{{ $td }}"><span class="font-medium text-gray-900">{{ $student['name'] }}</span> <span class="text-xs text-gray-400">{{ $student['student_id'] }}</span></td>
                                        <td class="{{ $td }}">{{ $student['class'] ?: '–' }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $student['present'] + $student['late'] }} of {{ $student['days'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $student['absent'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums font-semibold text-red-700">{{ $student['rate'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($followUp) > count($followUpShown))
                            <p class="mt-2 text-xs text-gray-500">The {{ count($followUp) - count($followUpShown) }} other students below {{ CampReportService::AT_RISK_RATE }}% are listed in Appendix A.</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-600">Every student attended at least {{ CampReportService::AT_RISK_RATE }}% of the days.</p>
                    @endif
                @else
                    <p class="mt-4 text-sm text-gray-500">No attendance was recorded for this camp.</p>
                @endif
            </section>

            {{-- 4. Teaching --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">4</span> Teaching and instructor reports</h2>
                @if($instructors)
                    <p class="mt-4 text-sm leading-relaxed text-gray-700">
                        {{ count($instructors) }} {{ Str::plural('instructor', count($instructors)) }} submitted {{ $reports['total'] }} daily {{ Str::plural('report', $reports['total']) }}.
                        {{ $reports['with_challenges'] }} reported a challenge and {{ $reports['follow_ups'] }} asked for follow-up.
                        On average each session used {{ $reports['average_approaches'] }} teaching approaches.
                    </p>

                    <h3 class="{{ $h3 }}">4.1 Reporting by instructor</h3>
                    <table class="w-full text-sm">
                        <thead><tr>
                            <th class="{{ $th }}">Instructor</th><th class="{{ $th }}">Courses</th><th class="{{ $th }} text-right">Reports</th><th class="{{ $th }} text-right">Days</th><th class="{{ $th }} text-right">Approaches / session</th><th class="{{ $th }} text-right">Follow-ups</th>
                        </tr></thead>
                        <tbody>
                            @foreach($instructors as $instructor)
                                <tr>
                                    <td class="{{ $td }} font-medium text-gray-900">{{ $instructor['name'] }}</td>
                                    <td class="{{ $td }} text-gray-600">{{ implode(', ', $instructor['courses']) ?: '–' }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $instructor['reports'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $instructor['days'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $instructor['approaches'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums {{ $instructor['follow_ups'] ? 'font-semibold text-amber-700' : '' }}">{{ $instructor['follow_ups'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h3 class="{{ $h3 }}">4.2 Teaching approaches used</h3>
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach($reports['approaches'] as $approach)
                                @php $share = $reports['total'] ? round($approach['count'] / $reports['total'] * 100) : 0; @endphp
                                <tr>
                                    <td class="w-48 py-1.5 pr-3 text-gray-700">{{ $approach['label'] }}</td>
                                    <td class="py-1.5"><div class="h-2 bg-gray-100"><div class="h-2 bg-blue-900" style="width: {{ $share }}%"></div></div></td>
                                    <td class="w-32 py-1.5 text-right text-gray-600">{{ $share }}% of sessions</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($reports['themes'])
                        <h3 class="{{ $h3 }}">4.3 Recurring challenges</h3>
                        <p class="mb-2 text-sm text-gray-600">Challenges and issues from all {{ $reports['total'] }} daily reports, grouped by theme.</p>
                        <table class="w-full text-sm">
                            <thead><tr>
                                <th class="{{ $th }}">Theme</th><th class="{{ $th }} text-right">Reports</th><th class="{{ $th }} text-right">Instructors</th><th class="{{ $th }}">Example</th>
                            </tr></thead>
                            <tbody>
                                @foreach($reports['themes'] as $theme)
                                    <tr>
                                        <td class="{{ $td }} whitespace-nowrap font-medium text-gray-900">{{ $theme['label'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $theme['reports'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $theme['instructors'] }}</td>
                                        <td class="{{ $td }} text-xs italic text-gray-600">“{{ $theme['example'] }}”</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <h3 class="{{ $h3 }}">4.{{ $reports['themes'] ? 4 : 3 }} What each instructor reported</h3>
                    <div class="divide-y divide-gray-200 border-y border-gray-200">
                        @foreach($instructors as $instructor)
                            <div class="grid gap-2 py-4 md:grid-cols-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $instructor['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $instructor['reports'] }} {{ Str::plural('report', $instructor['reports']) }} · last {{ optional($instructor['last_report'])->format('j M') }}</p>
                                </div>
                                <div class="space-y-2 text-sm md:col-span-3">
                                    @if($instructor['latest_summary'])
                                        <p class="text-gray-700"><span class="font-semibold text-gray-900">Latest session:</span> {{ $instructor['latest_summary'] }}</p>
                                    @endif
                                    @if($instructor['challenges'])
                                        <div>
                                            <p class="font-semibold text-gray-900">Challenges raised</p>
                                            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-gray-700">
                                                @foreach($instructor['challenges'] as $challenge)<li>{{ $challenge }}</li>@endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="text-gray-500">No challenges reported.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.daily-reports.index', ['camp' => $camp->id]) }}" wire:navigate class="mt-3 inline-block text-sm font-semibold text-blue-900 hover:underline">Read all {{ $reports['total'] }} daily reports for this camp →</a>
                @else
                    <p class="mt-4 text-sm text-gray-500">No daily reports were submitted for this camp.</p>
                @endif
            </section>

            {{-- 5. Learning --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">5</span> Learning outcomes</h2>
                <dl class="mt-4 grid grid-cols-2 border border-gray-200 sm:grid-cols-4">
                    @foreach([
                        ['Students assessed', $learning['students_assessed'].' of '.$enrollment['total']],
                        ['Average score', $pct($learning['average_score'])],
                        ['Pass rate', $pct($learning['pass_rate'])],
                        ['Certificates issued', $learning['certificates']],
                    ] as [$label, $value])
                        <div class="border-b border-r border-gray-200 p-4 last:border-r-0">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 text-xl font-bold text-blue-950">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if($courses)
                    <table class="mt-5 w-full text-sm">
                        <thead><tr>
                            <th class="{{ $th }}">Course</th><th class="{{ $th }} text-right">Sessions reported</th><th class="{{ $th }} text-right">Students</th><th class="{{ $th }} text-right">Average progress</th><th class="{{ $th }} text-right">Completed</th>
                        </tr></thead>
                        <tbody>
                            @foreach($courses as $course)
                                <tr>
                                    <td class="{{ $td }} font-medium text-gray-900">{{ $course['title'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $course['sessions'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $course['students'] }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $pct($course['avg_progress']) }}</td>
                                    <td class="{{ $td }} text-right tabular-nums">{{ $course['completed'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- 6. Feedback and issues --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">6</span> Student feedback and issues</h2>
                <div class="mt-4 grid gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="{{ $h3 }} mt-0">6.1 Lesson feedback</h3>
                        @if($feedback['count'])
                            <p class="text-sm text-gray-700">Average rating <strong class="text-blue-950">{{ $feedback['average'] ?? '–' }} / 5</strong> from {{ $feedback['count'] }} {{ Str::plural('response', $feedback['count']) }}.</p>
                            <table class="mt-2 w-full text-sm">
                                @foreach($feedback['distribution'] as $stars => $count)
                                    <tr>
                                        <td class="w-14 py-1 text-gray-600">{{ $stars }} {{ Str::plural('star', $stars) }}</td>
                                        <td class="py-1"><div class="h-2 bg-gray-100"><div class="h-2 bg-blue-900" style="width: {{ $feedback['count'] ? $count / $feedback['count'] * 100 : 0 }}%"></div></div></td>
                                        <td class="w-8 py-1 text-right tabular-nums">{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            @foreach(array_slice($feedback['comments'], 0, 3) as $comment)
                                <blockquote class="mt-3 border-l-2 border-gray-300 pl-3 text-sm italic text-gray-600">“{{ $comment }}”</blockquote>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500">Students did not leave lesson feedback during this camp.</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="{{ $h3 }} mt-0">6.2 Issues logged</h3>
                        @if($issues['total'])
                            <p class="mb-2 text-sm text-gray-700">{{ $issues['total'] }} {{ Str::plural('issue', $issues['total']) }} logged: {{ $issues['open'] }} still open, {{ $issues['resolved'] }} resolved.</p>
                            <table class="w-full text-sm">
                                <tbody>
                                    @foreach($issues['items'] as $issue)
                                        <tr>
                                            <td class="{{ $td }} pl-0">
                                                <p class="font-medium text-gray-900">{{ $issue['title'] }}</p>
                                                @if($issue['description'])<p class="text-xs text-gray-500">{{ $issue['description'] }}</p>@endif
                                            </td>
                                            <td class="{{ $td }} whitespace-nowrap pr-0 text-right text-xs">
                                                <span class="{{ in_array($issue['severity'], ['critical', 'high']) ? 'font-semibold text-red-700' : 'text-gray-500' }}">{{ ucfirst($issue['severity']) }}</span>
                                                · <span class="{{ $issue['status'] === 'resolved' ? 'text-emerald-700' : 'text-amber-700' }}">{{ ucfirst(str_replace('_', ' ', $issue['status'])) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-gray-500">No issues were logged.</p>
                        @endif
                        @if($revisions['total'])
                            <p class="mt-4 text-sm text-gray-700">
                                Trainers submitted {{ $revisions['total'] }} revised content {{ Str::plural('file', $revisions['total']) }} ({{ $revisions['approved'] }} approved, {{ $revisions['pending'] }} waiting).
                                <a href="{{ route('camp-revisions.index') }}" wire:navigate class="font-semibold text-blue-900 hover:underline">Review</a>
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- 7. Findings --}}
            <section>
                <h2 class="{{ $h2 }}"><span class="text-orange-600">7</span> Findings and recommendations</h2>
                <div class="mt-4 grid gap-8 md:grid-cols-3">
                    @foreach([['7.1 What went well', $narrative?->highlights], ['7.2 Challenges', $narrative?->challenges], ['7.3 Recommendations', $narrative?->recommendations]] as [$label, $text])
                        <div>
                            <h3 class="{{ $h3 }} mt-0">{{ $label }}</h3>
                            <ol class="list-decimal space-y-1.5 pl-5 text-sm text-gray-700">
                                @forelse(CampReport::points($text) as $point)
                                    <li>{{ $point }}</li>
                                @empty
                                    <li class="list-none -ml-5 text-gray-400">Nothing noted.</li>
                                @endforelse
                            </ol>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Sign-off --}}
            <section class="grid gap-10 pt-4 sm:grid-cols-2">
                @foreach(['Prepared by', 'Reviewed by'] as $role)
                    <div>
                        <div class="h-10 border-b border-gray-400"></div>
                        <p class="mt-1 text-xs text-gray-500">{{ $role }} — name, signature and date</p>
                    </div>
                @endforeach
            </section>

            {{-- Appendix A --}}
            @if($students)
                <section x-data="{ open: false, q: '' }">
                    <div class="flex flex-wrap items-baseline justify-between gap-3 border-b-2 border-blue-950 pb-2">
                        <h2 class="text-lg font-bold text-blue-950">Appendix A · Student attendance register</h2>
                        <button type="button" x-on:click="open = !open" class="text-sm font-semibold text-blue-900 hover:underline">
                            <span x-show="!open">Show all {{ count($students) }} students</span>
                            <span x-show="open" x-cloak>Hide register</span>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="mt-4">
                        <input type="search" x-model="q" placeholder="Search by name, ID or class…"
                               class="mb-3 w-full max-w-sm rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-900">
                        <table class="w-full text-sm">
                            <thead><tr>
                                <th class="{{ $th }} w-10">#</th><th class="{{ $th }}">Student</th><th class="{{ $th }}">Class</th><th class="{{ $th }} text-right">Present</th><th class="{{ $th }} text-right">Late</th><th class="{{ $th }} text-right">Absent</th><th class="{{ $th }} text-right">Attendance</th><th class="{{ $th }} text-right">Avg score</th>
                            </tr></thead>
                            <tbody>
                                @foreach($students as $i => $student)
                                    <tr x-show="!q || @js(Str::lower($student['name'].' '.$student['student_id'].' '.$student['class'])).includes(q.toLowerCase())">
                                        <td class="{{ $td }} text-gray-400 tabular-nums">{{ $i + 1 }}</td>
                                        <td class="{{ $td }}"><span class="font-medium text-gray-900">{{ $student['name'] }}</span> <span class="text-xs text-gray-400">{{ $student['student_id'] }}</span></td>
                                        <td class="{{ $td }}">{{ $student['class'] ?: '–' }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $student['present'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $student['late'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $student['absent'] }}</td>
                                        <td class="{{ $td }} text-right tabular-nums font-semibold {{ $rateText($student['rate']) }}">{{ $student['rate'] !== null ? $student['rate'].'%' : 'No record' }}</td>
                                        <td class="{{ $td }} text-right tabular-nums">{{ $pct($student['avg_score']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <footer class="border-t border-gray-200 pt-4 text-xs text-gray-400">
                {{ $orgName }} · {{ $camp->name }} · Ref. {{ $reference }} · Compiled from attendance records, {{ $reports['total'] }} instructor daily reports, assessments and lesson feedback.
            </footer>
        </div>
    </article>
</div>
