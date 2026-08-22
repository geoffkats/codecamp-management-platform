<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Code Club Summary — {{ $club['name'] ?? 'Club' }}</title>
    @php
        $primary = $branding['primary'] ?? '#0f172a';
        $accent = $branding['accent'] ?? '#f97316';
        $muted = $branding['muted'] ?? '#64748b';
        $soft = $branding['soft'] ?? '#f1f5f9';
        $org = $branding['org_name'] ?? 'Code Academy Uganda';
        $logoPath = $branding['logo_path'] ?? null;
        $logoSrc = null;
        if ($logoPath && file_exists($logoPath)) {
            $mime = @mime_content_type($logoPath) ?: 'image/png';
            $logoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
        }
    @endphp
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            background: #fff;
            line-height: 1.4;
        }
        table { border-collapse: collapse; width: 100%; }
        .page { width: 210mm; min-height: 297mm; position: relative; }
        .pad { padding: 8mm 10mm 16mm; }
        .header { background: {{ $primary }}; color: #fff; padding: 7mm 10mm 6mm; }
        .header td { color: #fff; vertical-align: middle; }
        .brand { font-size: 14pt; font-weight: bold; }
        .brand-sub { font-size: 7.5pt; opacity: 0.85; margin-top: 1mm; }
        .pill {
            display: inline-block;
            background: {{ $accent }};
            color: #fff;
            font-size: 7pt;
            font-weight: bold;
            padding: 1.2mm 3mm;
            border-radius: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-top: 3mm;
        }
        .title-bar {
            background: #1e3a5f;
            color: #fff;
            padding: 3mm 10mm;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .term-chip {
            float: right;
            background: rgba(255,255,255,0.15);
            padding: 1mm 3mm;
            border-radius: 2mm;
            font-size: 8pt;
        }
        .section { margin-bottom: 5mm; }
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            color: {{ $accent }};
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 0.3mm solid #cbd5e1;
            padding-bottom: 1.5mm;
            margin-bottom: 2.5mm;
        }
        .stat-box {
            background: {{ $soft }};
            border-radius: 2.5mm;
            text-align: center;
            padding: 4mm 2mm;
        }
        .stat-num { font-size: 16pt; font-weight: bold; color: {{ $primary }}; line-height: 1; }
        .stat-lbl { font-size: 7pt; color: {{ $muted }}; margin-top: 1.5mm; font-weight: bold; }
        .bar-track { height: 3mm; background: #e2e8f0; border-radius: 1.5mm; margin-top: 1.5mm; }
        .bar-fill { height: 3mm; border-radius: 1.5mm; }
        .list-table th, .list-table td {
            border: 0.3mm solid #e2e8f0;
            padding: 2mm 2.5mm;
            font-size: 8pt;
            text-align: left;
        }
        .list-table th { background: {{ $soft }}; }
        .sign-box {
            border: 0.3mm solid #cbd5e1;
            border-radius: 2.5mm;
            padding: 4mm;
            min-height: 26mm;
        }
        .sign-label { font-size: 7.5pt; color: {{ $muted }}; text-transform: uppercase; }
        .sign-name { font-size: 9pt; font-weight: bold; margin-top: 8mm; }
        .footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: {{ $primary }}; color: #fff;
            padding: 3mm 10mm; font-size: 7pt;
        }
        .footer td { color: #fff; }
        .muted { color: {{ $muted }}; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <table>
            <tr>
                <td style="width: 70%;">
                    <div class="brand">{{ $org }}</div>
                    <div class="brand-sub">{{ $branding['partner_label'] ?? 'In Partnership with' }} {{ $school['name'] ?? '—' }}</div>
                    <div class="pill">Club Summary</div>
                </td>
                <td style="width: 30%; text-align: right;">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="logo" style="height: 16mm; width: auto;">
                    @else
                        @include('reports.partials.code-academy-logo')
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="title-bar">
        {{ config('codeclub-reports.school_summary_title', 'Code Club Summary Report') }}
        <span class="term-chip">{{ $term_label }}</span>
    </div>

    <div class="pad">
        <div class="section">
            <div style="font-size: 13pt; font-weight: bold;">{{ $club['name'] }}</div>
            <div class="muted" style="margin-top: 1mm;">
                {{ $school['name'] ?? '—' }}
                @if(!empty($period['label'])) · {{ $period['label'] }} @endif
                @if(!empty($club['schedule_summary'])) · {{ $club['schedule_summary'] }} @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Club Totals</div>
            <table>
                <tr>
                    @foreach([
                        ['Students', $totals['students'] ?? 0],
                        ['Active learners', $totals['active'] ?? 0],
                        ['Avg attendance', ($totals['avg_attendance'] ?? 0).'%'],
                        ['Avg overall', ($totals['avg_overall'] ?? 0).'%'],
                        ['Projects', $totals['projects'] ?? 0],
                    ] as $i => $stat)
                        <td style="width: 20%; padding-right: {{ $i < 4 ? '2mm' : '0' }};">
                            <div class="stat-box">
                                <div class="stat-num">{{ $stat[1] }}</div>
                                <div class="stat-lbl">{{ $stat[0] }}</div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Track Class Averages</div>
            @forelse($track_averages as $track)
                <div style="margin-bottom: 3mm;">
                    <table>
                        <tr>
                            <td style="width: 45%; font-weight: bold; color: {{ $track['color'] }};">{{ $track['label'] }}</td>
                            <td style="width: 25%;" class="muted">{{ $track['enrolled_count'] }} enrolled</td>
                            <td style="width: 30%; text-align: right; font-weight: bold;">
                                {{ $track['average'] !== null ? $track['average'].'%' : '—' }}
                            </td>
                        </tr>
                    </table>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ max(2, (int) ($track['average'] ?? 0)) }}%; background: {{ $track['color'] }};"></div>
                    </div>
                </div>
            @empty
                <div class="muted">No track data available.</div>
            @endforelse
        </div>

        <div class="section">
            <div class="section-title">Top Performers</div>
            @if(count($top_performers) > 0)
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Overall</th>
                            <th>Label</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_performers as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['score'] }}%</td>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['attendance'] !== null ? $row['attendance'].'%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="muted">No active members to rank.</div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Signatures</div>
            <table>
                <tr>
                    <td style="width: 48%; padding-right: 2mm;">
                        <div class="sign-box">
                            <div class="sign-label">Head Instructor</div>
                            <div class="sign-name">{{ $instructor['name'] ?? 'Club Facilitator' }}</div>
                            <div class="muted" style="font-size: 7.5pt; margin-top: 1mm;">Signature / Date</div>
                        </div>
                    </td>
                    <td style="width: 48%; padding-left: 2mm;">
                        <div class="sign-box">
                            <div class="sign-label">Operations / School</div>
                            <div class="sign-name">{{ $school['name'] ?? '—' }}</div>
                            <div class="muted" style="font-size: 7.5pt; margin-top: 1mm;">Signature / Stamp</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>{{ $branding['footer_contact'] ?? '' }} · {{ $branding['footer_web'] ?? '' }}</td>
                <td style="text-align: right;">Generated {{ $generated_at }}</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
