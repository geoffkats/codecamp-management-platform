@php
    use App\Models\CampReport;
    use App\Models\SystemSetting;
    use App\Services\Reports\CampReportService;
    use Illuminate\Support\Str;

    $orgName = SystemSetting::get('app_name', config('app.name'));
    $pct = fn ($value) => $value !== null ? $value.'%' : '–';
    $rateColor = fn ($rate) => match (true) {
        $rate === null => '#9ca3af',
        $rate >= 85 => '#047857',
        $rate >= CampReportService::AT_RISK_RATE => '#b45309',
        default => '#b91c1c',
    };
    $followUp = $studentSummary['follow_up'];
    $followUpShown = array_slice($followUp, 0, 15);
    $reference = 'CAMP-'.str_pad((string) $camp->id, 3, '0', STR_PAD_LEFT).'-'.$camp->start_date->format('Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $camp->name }} - End of camp report</title>
    <style>
        @page { margin: 40px 46px 56px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1f2937; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        h1 { font-size: 21px; color: #111827; margin: 18px 0 8px; }
        h2 { font-size: 12.5px; color: #172554; margin: 22px 0 8px; padding-bottom: 4px; border-bottom: 1.5px solid #172554; }
        h2 .n { color: #ea580c; margin-right: 6px; }
        h3 { font-size: 10px; color: #111827; margin: 12px 0 5px; }
        p { margin: 0 0 6px; }
        .footer { position: fixed; bottom: -36px; left: 0; right: 0; font-size: 7.5px; color: #9ca3af; border-top: 0.5px solid #d1d5db; padding-top: 5px; }
        .footer .page:after { content: counter(page); }
        .rule { height: 3px; background: #172554; margin-top: 10px; }
        .rule-accent { height: 1.5px; width: 33%; background: #f97316; }
        .eyebrow { color: #ea580c; font-size: 8px; letter-spacing: 2px; text-transform: uppercase; font-weight: bold; }
        .muted { color: #6b7280; }
        .meta td { padding: 2px 10px 2px 0; vertical-align: top; width: 33%; }
        .meta .k { color: #6b7280; font-size: 8px; }
        .meta .v { font-weight: bold; color: #111827; }
        .figures td { border: 0.5px solid #d1d5db; padding: 7px 8px; vertical-align: top; width: 16.66%; }
        .figures .k { font-size: 7px; text-transform: uppercase; letter-spacing: .6px; color: #6b7280; font-weight: bold; }
        .figures .v { font-size: 15px; font-weight: bold; color: #172554; margin-top: 2px; }
        .figures .h { font-size: 7.5px; color: #6b7280; }
        .data th { background: #f3f4f6; color: #4b5563; font-size: 7.5px; text-transform: uppercase; letter-spacing: .4px; text-align: left; padding: 4px 6px; border-bottom: 0.5px solid #d1d5db; }
        .data td { padding: 4px 6px; border-bottom: 0.5px solid #e5e7eb; vertical-align: top; }
        .data tfoot td { font-weight: bold; border-bottom: none; border-top: 0.5px solid #9ca3af; }
        .compact td, .compact th { padding: 2.5px 5px; font-size: 8px; }
        .r { text-align: right; } .c { text-align: center; }
        .bar { height: 6px; background: #f3f4f6; }
        .bar div { height: 6px; }
        .two td.col { width: 50%; vertical-align: top; }
        .two td.col.left { padding-right: 12px; }
        .two td.col.right { padding-left: 12px; }
        .three td.col { width: 33.33%; vertical-align: top; padding-right: 10px; }
        ol, ul { margin: 0; padding-left: 14px; }
        li { margin-bottom: 3px; }
        .digest td { border-bottom: 0.5px solid #e5e7eb; padding: 6px 4px; vertical-align: top; }
        .quote { border-left: 2px solid #d1d5db; padding: 1px 7px; margin: 5px 0; font-style: italic; color: #4b5563; }
        .sign td { width: 50%; padding-right: 30px; padding-top: 34px; }
        .sign .line { border-bottom: 0.5px solid #6b7280; height: 1px; }
        .page-break { page-break-before: always; }
        .avoid { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="footer">
        <table><tr>
            <td>{{ $orgName }} · {{ $camp->name }} · End of camp report · Ref. {{ $reference }}</td>
            <td class="r">Generated {{ $generatedAt->format('j M Y') }} · Page <span class="page"></span></td>
        </tr></table>
    </div>

    {{-- Letterhead --}}
    <table><tr>
        <td style="vertical-align: middle;">
            <table><tr>
                @if($logo)
                    <td style="width: 52px; vertical-align: middle;"><img src="{{ $logo }}" style="max-height: 42px; max-width: 46px;"></td>
                @endif
                <td style="vertical-align: middle;">
                    <div style="font-size: 12px; font-weight: bold; color: #172554;">{{ $orgName }}</div>
                    <div class="muted" style="font-size: 8px;">Code Camp programme</div>
                </td>
            </tr></table>
        </td>
        <td class="r" style="vertical-align: middle;">
            <div class="eyebrow">End of camp report</div>
            <div class="muted" style="font-size: 8px;">Ref. {{ $reference }}</div>
        </td>
    </tr></table>
    <div class="rule"></div>
    <div class="rule-accent"></div>

    <h1>{{ $camp->name }}</h1>
    <table class="meta">
        <tr>
            <td><div class="k">Reporting period</div><div class="v">{{ $camp->date_range }}</div></td>
            <td><div class="k">Teaching days</div><div class="v">{{ $period['teaching_days'] ?: '–' }}</div></td>
            <td><div class="k">Camp status</div><div class="v">{{ ucfirst($camp->status) }}</div></td>
        </tr>
        <tr>
            <td><div class="k">Students enrolled</div><div class="v">{{ $enrollment['total'] }}</div></td>
            <td><div class="k">Instructors reporting</div><div class="v">{{ count($instructors) }}</div></td>
            <td><div class="k">Data as of</div><div class="v">{{ $generatedAt->format('j M Y, H:i') }}</div></td>
        </tr>
    </table>

    {{-- 1 --}}
    <h2><span class="n">1</span> Executive summary</h2>
    <p style="font-size: 10px;">{{ $narrative->summary }}</p>
    <table class="figures" style="margin-top: 8px;"><tr>
        @foreach([
            ['Students', $enrollment['total'], $enrollment['active'].' active at close'],
            ['Attendance', $pct($attendance['rate']), ($attendance['average_daily'] ?? 0).' students a day'],
            ['Need follow-up', count($followUp), 'below '.CampReportService::AT_RISK_RATE.'% attendance'],
            ['Daily reports', $reports['total'], count($instructors).' instructors'],
            ['Average score', $pct($learning['average_score']), $learning['attempts'].' attempts'],
            ['Lesson rating', $feedback['average'] !== null ? $feedback['average'].'/5' : '–', $feedback['count'].' responses'],
        ] as [$label, $value, $hint])
            <td><div class="k">{{ $label }}</div><div class="v">{{ $value }}</div><div class="h">{{ $hint }}</div></td>
        @endforeach
    </tr></table>

    {{-- 2 --}}
    <h2><span class="n">2</span> Enrolment</h2>
    <table class="two"><tr>
        <td class="col left">
            <table class="data">
                <tr><td>Students enrolled</td><td class="r"><strong>{{ $enrollment['total'] }}</strong></td></tr>
                @if($enrollment['capacity'])<tr><td>Capacity used</td><td class="r"><strong>{{ $enrollment['total'] }} of {{ $enrollment['capacity'] }} ({{ $enrollment['fill_rate'] }}%)</strong></td></tr>@endif
                <tr><td>Active / completed at close</td><td class="r"><strong>{{ $enrollment['active'] }} / {{ $enrollment['completed'] }}</strong></td></tr>
                <tr><td>Transferred / dropped</td><td class="r"><strong>{{ $enrollment['transferred'] }} / {{ $enrollment['dropped'] }}</strong></td></tr>
                @foreach($enrollment['gender'] as $label => $count)<tr><td>{{ $label }}</td><td class="r"><strong>{{ $count }}</strong></td></tr>@endforeach
                @if($enrollment['average_age'])<tr><td>Average age</td><td class="r"><strong>{{ $enrollment['average_age'] }} years</strong></td></tr>@endif
                @if($uniforms['total'])<tr><td>Uniforms paid</td><td class="r"><strong>{{ $uniforms['paid'] }} of {{ $uniforms['total'] }}</strong></td></tr>@endif
            </table>
        </td>
        <td class="col right">
            @if($enrollment['classes'])
                <table class="data">
                    <thead><tr><th>Class</th><th style="width: 50%;"></th><th class="r">Students</th></tr></thead>
                    @foreach($enrollment['classes'] as $class => $count)
                        <tr>
                            <td>{{ $class }}</td>
                            <td><div class="bar"><div style="width: {{ $enrollment['total'] ? $count / $enrollment['total'] * 100 : 0 }}%; background: #1e3a8a;"></div></div></td>
                            <td class="r">{{ $count }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td>
    </tr></table>

    {{-- 3 --}}
    <h2><span class="n">3</span> Attendance</h2>
    @if($attendance['records'])
        <p>
            {{ number_format($attendance['records']) }} check-ins were recorded over {{ $period['teaching_days'] }} teaching days for {{ $attendance['students_tracked'] }} students.
            Overall attendance was <strong>{{ $pct($attendance['rate']) }}</strong>, and {{ $pct($attendance['punctuality']) }} of students who came arrived on time.
            @if($attendance['best_day']) The best day was {{ $attendance['best_day']['date']->format('l j M') }} ({{ $attendance['best_day']['rate'] }}%)@endif{{ $attendance['worst_day'] ? ' and the lowest was '.$attendance['worst_day']['date']->format('l j M').' ('.$attendance['worst_day']['rate'].'%).' : '.' }}
        </p>
        <table class="two"><tr>
            <td class="col left" style="width: 58%;">
                <h3>3.1 Daily attendance</h3>
                <table class="data">
                    <thead><tr><th>Day</th><th class="r">Present</th><th class="r">Late</th><th class="r">Absent</th><th style="width: 34%;">Attendance</th></tr></thead>
                    <tbody>
                        @foreach($attendanceDays as $day)
                            <tr>
                                <td>{{ $day['date']->format('D j M') }}</td>
                                <td class="r">{{ $day['present'] }}</td>
                                <td class="r">{{ $day['late'] }}</td>
                                <td class="r">{{ $day['absent'] }}</td>
                                <td>
                                    <table><tr>
                                        <td style="padding: 0; border: none;"><div class="bar"><div style="width: {{ $day['rate'] }}%; background: {{ $rateColor($day['rate']) }};"></div></div></td>
                                        <td class="r" style="padding: 0 0 0 4px; width: 26px; border: none; color: {{ $rateColor($day['rate']) }}; font-weight: bold;">{{ $day['rate'] }}%</td>
                                    </tr></table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr>
                        <td>Total</td>
                        <td class="r">{{ number_format($attendance['present']) }}</td>
                        <td class="r">{{ number_format($attendance['late']) }}</td>
                        <td class="r">{{ number_format($attendance['absent']) }}</td>
                        <td class="r">{{ $pct($attendance['rate']) }}</td>
                    </tr></tfoot>
                </table>
            </td>
            <td class="col right" style="width: 42%;">
                <h3>3.2 How regularly students attended</h3>
                <table class="data">
                    @foreach($studentSummary['bands'] as $band)
                        <tr>
                            <td>{{ $band['label'] }} <span class="muted">{{ $band['range'] }}</span></td>
                            <td class="r"><strong>{{ $band['count'] }}</strong></td>
                            <td class="r muted" style="width: 30px;">{{ $band['share'] }}%</td>
                        </tr>
                    @endforeach
                </table>
                <p class="muted" style="margin-top: 4px;">{{ $studentSummary['perfect'] }} {{ Str::plural('student', $studentSummary['perfect']) }} attended every day on time.</p>

                @if(count($studentSummary['by_class']) > 1)
                    <h3>3.3 Attendance by class</h3>
                    <table class="data">
                        <thead><tr><th>Class</th><th class="r">Students</th><th class="r">Attendance</th></tr></thead>
                        @foreach($studentSummary['by_class'] as $row)
                            <tr>
                                <td>{{ $row['class'] }}</td>
                                <td class="r">{{ $row['students'] }}</td>
                                <td class="r" style="color: {{ $rateColor($row['rate']) }}; font-weight: bold;">{{ $pct($row['rate']) }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr></table>

        <div class="avoid">
            <h3>3.4 Students who need follow-up</h3>
            @if($followUp)
                <p>{{ count($followUp) }} {{ Str::plural('student', count($followUp)) }} attended less than {{ CampReportService::AT_RISK_RATE }}% of the days. Parents or schools should be contacted before the next camp.</p>
                <table class="data">
                    <thead><tr><th>Student</th><th>ID</th><th>Class</th><th class="r">Days attended</th><th class="r">Absent</th><th class="r">Attendance</th></tr></thead>
                    @foreach($followUpShown as $student)
                        <tr>
                            <td><strong>{{ $student['name'] }}</strong></td>
                            <td class="muted">{{ $student['student_id'] }}</td>
                            <td>{{ $student['class'] ?: '–' }}</td>
                            <td class="r">{{ $student['present'] + $student['late'] }} of {{ $student['days'] }}</td>
                            <td class="r">{{ $student['absent'] }}</td>
                            <td class="r" style="color: #b91c1c; font-weight: bold;">{{ $student['rate'] }}%</td>
                        </tr>
                    @endforeach
                </table>
                @if(count($followUp) > count($followUpShown))
                    <p class="muted" style="margin-top: 4px;">The {{ count($followUp) - count($followUpShown) }} other students below {{ CampReportService::AT_RISK_RATE }}% are listed in Appendix A.</p>
                @endif
            @else
                <p>Every student attended at least {{ CampReportService::AT_RISK_RATE }}% of the days.</p>
            @endif
        </div>
    @else
        <p class="muted">No attendance was recorded for this camp.</p>
    @endif

    {{-- 4 --}}
    <h2><span class="n">4</span> Teaching and instructor reports</h2>
    @if($instructors)
        <p>
            {{ count($instructors) }} {{ Str::plural('instructor', count($instructors)) }} submitted {{ $reports['total'] }} daily {{ Str::plural('report', $reports['total']) }}.
            {{ $reports['with_challenges'] }} reported a challenge and {{ $reports['follow_ups'] }} asked for follow-up.
            On average each session used {{ $reports['average_approaches'] }} teaching approaches.
        </p>

        <h3>4.1 Reporting by instructor</h3>
        <table class="data">
            <thead><tr><th>Instructor</th><th>Courses</th><th class="r">Reports</th><th class="r">Days</th><th class="r">Approaches / session</th><th class="r">Follow-ups</th></tr></thead>
            @foreach($instructors as $instructor)
                <tr>
                    <td><strong>{{ $instructor['name'] }}</strong></td>
                    <td>{{ implode(', ', $instructor['courses']) ?: '–' }}</td>
                    <td class="r">{{ $instructor['reports'] }}</td>
                    <td class="r">{{ $instructor['days'] }}</td>
                    <td class="r">{{ $instructor['approaches'] }}</td>
                    <td class="r">{{ $instructor['follow_ups'] }}</td>
                </tr>
            @endforeach
        </table>

        <div class="avoid">
            <h3>4.2 Teaching approaches used</h3>
            <table>
                @foreach($reports['approaches'] as $approach)
                    @php $share = $reports['total'] ? round($approach['count'] / $reports['total'] * 100) : 0; @endphp
                    <tr>
                        <td style="width: 150px; padding: 2px 0;">{{ $approach['label'] }}</td>
                        <td style="padding: 2px 6px;"><div class="bar"><div style="width: {{ $share }}%; background: #1e3a8a;"></div></div></td>
                        <td class="r muted" style="width: 90px;">{{ $share }}% of sessions</td>
                    </tr>
                @endforeach
            </table>
        </div>

        @if($reports['themes'])
            <div class="avoid">
                <h3>4.3 Recurring challenges</h3>
                <p class="muted">Challenges and issues from all {{ $reports['total'] }} daily reports, grouped by theme.</p>
                <table class="data">
                    <thead><tr><th>Theme</th><th class="r">Reports</th><th class="r">Instructors</th><th>Example</th></tr></thead>
                    @foreach($reports['themes'] as $theme)
                        <tr>
                            <td style="white-space: nowrap;"><strong>{{ $theme['label'] }}</strong></td>
                            <td class="r">{{ $theme['reports'] }}</td>
                            <td class="r">{{ $theme['instructors'] }}</td>
                            <td class="muted" style="font-style: italic;">“{{ $theme['example'] }}”</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <h3>4.{{ $reports['themes'] ? 4 : 3 }} What each instructor reported</h3>
        <table class="digest">
            @foreach($instructors as $instructor)
                <tr class="avoid">
                    <td style="width: 26%;">
                        <strong>{{ $instructor['name'] }}</strong>
                        <div class="muted">{{ $instructor['reports'] }} {{ Str::plural('report', $instructor['reports']) }}</div>
                    </td>
                    <td>
                        @if($instructor['latest_summary'])<p><strong>Latest session:</strong> {{ $instructor['latest_summary'] }}</p>@endif
                        @if($instructor['challenges'])
                            <strong>Challenges raised</strong>
                            <ul>@foreach($instructor['challenges'] as $challenge)<li>{{ $challenge }}</li>@endforeach</ul>
                        @else
                            <span class="muted">No challenges reported.</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="muted">No daily reports were submitted for this camp.</p>
    @endif

    {{-- 5 --}}
    <div class="avoid">
        <h2><span class="n">5</span> Learning outcomes</h2>
        <table class="figures"><tr>
            @foreach([
                ['Students assessed', $learning['students_assessed'].' of '.$enrollment['total']],
                ['Average score', $pct($learning['average_score'])],
                ['Pass rate', $pct($learning['pass_rate'])],
                ['Certificates issued', $learning['certificates']],
            ] as [$label, $value])
                <td style="width: 25%;"><div class="k">{{ $label }}</div><div class="v">{{ $value }}</div></td>
            @endforeach
        </tr></table>
        @if($courses)
            <table class="data" style="margin-top: 8px;">
                <thead><tr><th>Course</th><th class="r">Sessions reported</th><th class="r">Students</th><th class="r">Average progress</th><th class="r">Completed</th></tr></thead>
                @foreach($courses as $course)
                    <tr>
                        <td><strong>{{ $course['title'] }}</strong></td>
                        <td class="r">{{ $course['sessions'] }}</td>
                        <td class="r">{{ $course['students'] }}</td>
                        <td class="r">{{ $pct($course['avg_progress']) }}</td>
                        <td class="r">{{ $course['completed'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    {{-- 6 --}}
    <div class="avoid">
        <h2><span class="n">6</span> Student feedback and issues</h2>
        <table class="two"><tr>
            <td class="col left">
                <h3 style="margin-top: 0;">6.1 Lesson feedback</h3>
                @if($feedback['count'])
                    <p>Average rating <strong>{{ $feedback['average'] ?? '–' }} / 5</strong> from {{ $feedback['count'] }} {{ Str::plural('response', $feedback['count']) }}.</p>
                    <table>
                        @foreach($feedback['distribution'] as $stars => $count)
                            <tr>
                                <td style="width: 40px; padding: 1px 0;">{{ $stars }} {{ Str::plural('star', $stars) }}</td>
                                <td style="padding: 1px 4px;"><div class="bar"><div style="width: {{ $feedback['count'] ? $count / $feedback['count'] * 100 : 0 }}%; background: #1e3a8a;"></div></div></td>
                                <td class="r" style="width: 20px;">{{ $count }}</td>
                            </tr>
                        @endforeach
                    </table>
                    @foreach(array_slice($feedback['comments'], 0, 3) as $comment)
                        <div class="quote">“{{ $comment }}”</div>
                    @endforeach
                @else
                    <p class="muted">Students did not leave lesson feedback during this camp.</p>
                @endif
            </td>
            <td class="col right">
                <h3 style="margin-top: 0;">6.2 Issues logged</h3>
                @if($issues['total'])
                    <p>{{ $issues['total'] }} {{ Str::plural('issue', $issues['total']) }} logged: {{ $issues['open'] }} still open, {{ $issues['resolved'] }} resolved.</p>
                    <table class="data">
                        @foreach($issues['items'] as $issue)
                            <tr>
                                <td>
                                    <strong>{{ $issue['title'] }}</strong>
                                    @if($issue['description'])<div class="muted">{{ $issue['description'] }}</div>@endif
                                </td>
                                <td class="r" style="white-space: nowrap;">
                                    <span style="color: {{ in_array($issue['severity'], ['critical', 'high']) ? '#b91c1c' : '#6b7280' }};">{{ ucfirst($issue['severity']) }}</span><br>
                                    <span style="color: {{ $issue['status'] === 'resolved' ? '#047857' : '#b45309' }};">{{ ucfirst(str_replace('_', ' ', $issue['status'])) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <p class="muted">No issues were logged.</p>
                @endif
                @if($revisions['total'])
                    <p style="margin-top: 6px;">Trainers submitted {{ $revisions['total'] }} revised content {{ Str::plural('file', $revisions['total']) }} ({{ $revisions['approved'] }} approved, {{ $revisions['pending'] }} waiting).</p>
                @endif
            </td>
        </tr></table>
    </div>

    {{-- 7 --}}
    <div class="avoid">
        <h2><span class="n">7</span> Findings and recommendations</h2>
        <table class="three"><tr>
            @foreach([['7.1 What went well', $narrative->highlights], ['7.2 Challenges', $narrative->challenges], ['7.3 Recommendations', $narrative->recommendations]] as [$label, $text])
                <td class="col">
                    <h3 style="margin-top: 0;">{{ $label }}</h3>
                    @if($points = CampReport::points($text))
                        <ol>@foreach($points as $point)<li>{{ $point }}</li>@endforeach</ol>
                    @else
                        <p class="muted">Nothing noted.</p>
                    @endif
                </td>
            @endforeach
        </tr></table>

        <table class="sign"><tr>
            @foreach(['Prepared by', 'Reviewed by'] as $role)
                <td><div class="line"></div><div class="muted" style="font-size: 7.5px; margin-top: 3px;">{{ $role }} — name, signature and date</div></td>
            @endforeach
        </tr></table>
    </div>

    {{-- Appendix A --}}
    @if($students)
        <div class="page-break"></div>
        <h2>Appendix A · Student attendance register ({{ count($students) }} students)</h2>
        <table class="data compact">
            <thead><tr><th style="width: 22px;">#</th><th>Student</th><th>ID</th><th>Class</th><th class="r">Present</th><th class="r">Late</th><th class="r">Absent</th><th class="r">Attendance</th><th class="r">Avg score</th></tr></thead>
            <tbody>
                @foreach($students as $i => $student)
                    <tr>
                        <td class="muted">{{ $i + 1 }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td class="muted">{{ $student['student_id'] }}</td>
                        <td>{{ $student['class'] ?: '–' }}</td>
                        <td class="r">{{ $student['present'] }}</td>
                        <td class="r">{{ $student['late'] }}</td>
                        <td class="r">{{ $student['absent'] }}</td>
                        <td class="r" style="color: {{ $rateColor($student['rate']) }}; font-weight: bold;">{{ $student['rate'] !== null ? $student['rate'].'%' : 'No record' }}</td>
                        <td class="r">{{ $pct($student['avg_score']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
