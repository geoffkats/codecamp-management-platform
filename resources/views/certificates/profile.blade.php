<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CODE Profile Certificate</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            width: 210mm;
            height: 297mm;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }

        @php
            $sig = $layout['signature'] ?? [];
            $signatory = $layout['signatory'] ?? [];
            $datePos = $layout['date'] ?? [];
        @endphp

        @if(!empty($backgroundImage) && ($useBackground ?? true))
        .bg {
            position: absolute;
            top: 0; left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }

        .overlay { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; z-index: 5; }

        .field-name {
            position: absolute;
            top: 99.5mm; left: 28.4mm; width: 104mm;
            font-size: 15pt; font-weight: bold;
            color: {{ $brandColor ?? '#1546c0' }};
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .field-no {
            position: absolute;
            top: 101.5mm; left: 145.1mm; width: 34.6mm;
            font-size: 11pt; font-weight: bold;
            color: {{ $brandColor ?? '#1546c0' }};
            font-family: "DejaVu Sans Mono", monospace;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .modules-overlay {
            position: absolute;
            top: 150mm; left: 28.7mm; width: 151mm;
            border-collapse: collapse;
        }
        .modules-overlay td {
            font-size: 9.5pt;
            color: #2b2b2b;
            padding: 2.4mm 0;
            vertical-align: top;
        }
        .modules-overlay td.col-name { width: 66mm; font-weight: bold; color: {{ $brandColor ?? '#1546c0' }}; }
        .modules-overlay td.col-version { width: 58mm; }
        .modules-overlay td.col-date { text-align: left; white-space: nowrap; }

        .field-signature {
            position: absolute;
            bottom: {{ $sig['bottom_mm'] ?? 40.5 }}mm;
            left: {{ $sig['left_mm'] ?? 28 }}mm;
            width: {{ $sig['width_mm'] ?? 55 }}mm;
            height: {{ $sig['max_height_mm'] ?? 9 }}mm;
            line-height: 0;
            z-index: 6;
        }
        .field-signature img {
            height: {{ $sig['max_height_mm'] ?? 9 }}mm;
            width: auto;
            max-width: {{ $sig['width_mm'] ?? 55 }}mm;
            display: block;
            margin: 0;
            padding: 0;
        }

        .field-signatory {
            position: absolute;
            top: {{ $signatory['top_mm'] ?? 258 }}mm;
            left: {{ $signatory['left_mm'] ?? 28 }}mm;
            width: {{ $signatory['width_mm'] ?? 95 }}mm;
            font-size: {{ $signatory['font_size_pt'] ?? 8 }}pt;
            font-weight: bold;
            color: {{ $labelColor ?? '#2d7fd4' }};
            line-height: 1.2;
            text-align: center;
            z-index: 6;
        }

        .field-date {
            position: absolute;
            top: {{ $datePos['top_mm'] ?? 250.5 }}mm;
            left: {{ $datePos['left_mm'] ?? 148.5 }}mm;
            width: {{ $datePos['width_mm'] ?? 31 }}mm;
            font-size: {{ $datePos['font_size_pt'] ?? 10 }}pt;
            font-weight: bold;
            color: {{ $labelColor ?? '#2d7fd4' }};
            line-height: 1;
            text-align: center;
        }
        @else
        .frame { width: 210mm; height: 297mm; background: {{ $brandColor ?? '#1546c0' }}; padding: {{ $borderWidth ?? 5 }}mm; }
        .certificate { width: 100%; height: 100%; background: #fff; position: relative; padding: 14mm 16mm 12mm; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 14mm; }
        .header-table td { vertical-align: top; }
        .logo-right { text-align: right; font-size: 22pt; font-weight: bold; line-height: 1.05; color: {{ $brandColor ?? '#1546c0' }}; }
        .logo-mark { width: 14mm; height: 14mm; border: 1.2mm solid {{ $brandColor ?? '#1546c0' }}; display: inline-block; vertical-align: middle; text-align: center; font-size: 8pt; font-weight: bold; line-height: 12mm; margin-right: 3mm; color: {{ $brandColor ?? '#1546c0' }}; }
        .logo-text-code { font-size: 16pt; font-weight: bold; letter-spacing: 1pt; color: {{ $brandColor ?? '#1546c0' }}; }
        .logo-text-academy { font-size: 10pt; font-weight: bold; letter-spacing: 1pt; color: {{ $brandColor ?? '#1546c0' }}; }
        .logo-text-uganda { font-size: 7pt; font-weight: bold; letter-spacing: 2pt; border-top: 0.4mm solid {{ $brandColor ?? '#1546c0' }}; padding-top: 1mm; text-align: center; color: {{ $brandColor ?? '#1546c0' }}; }
        .title { font-size: 34pt; font-weight: bold; line-height: 1.05; margin-bottom: 10mm; color: {{ $brandColor ?? '#1546c0' }}; }
        .candidate-table { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        .candidate-table td { border-bottom: 0.6mm solid {{ $brandColor ?? '#1546c0' }}; padding-bottom: 2mm; }
        .candidate-name { font-size: 16pt; font-weight: bold; width: 68%; color: {{ $brandColor ?? '#1546c0' }}; }
        .candidate-no { font-size: 11pt; font-weight: bold; width: 32%; font-family: "DejaVu Sans Mono", monospace; color: {{ $brandColor ?? '#1546c0' }}; }
        .labels-table { width: 100%; border-collapse: collapse; margin-bottom: 8mm; }
        .labels-table td { font-size: 8pt; font-weight: bold; color: {{ $labelColor ?? '#2d7fd4' }}; padding-top: 1.5mm; }
        .subtitle { font-size: 10pt; font-weight: bold; color: #222; margin-bottom: 5mm; }
        .modules-table { width: 100%; border-collapse: collapse; }
        .modules-table th { font-size: 9pt; font-weight: bold; color: #111; text-align: left; padding-bottom: 3mm; }
        .modules-table th:nth-child(2), .modules-table td:nth-child(2) { padding-left: 10mm; }
        .modules-table td { font-size: 9pt; color: #333; padding: 2.5mm 0; }
        .footer-block { position: absolute; left: 16mm; right: 16mm; bottom: 16mm; border-top: 0.6mm solid {{ $brandColor ?? '#1546c0' }}; padding-top: 4mm; }
        .footer-signature { height: 10mm; margin-bottom: 1mm; line-height: 0; }
        .footer-signature img { height: 10mm; width: auto; max-width: 55mm; display: block; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { font-size: 8pt; font-weight: bold; color: {{ $labelColor ?? '#2d7fd4' }}; vertical-align: top; }
        .footer-date { text-align: right; width: 35%; }
        @endif
    </style>
</head>
<body>
@if(!empty($backgroundImage) && ($useBackground ?? true))
    <div class="page">
        <img class="bg" src="{{ $backgroundImage }}" alt="">
        <div class="overlay">
            <div class="field-name">{{ $candidateName }}</div>
            <div class="field-no">{{ $candidateNo }}</div>

            <table class="modules-overlay">
                @foreach($modules as $module)
                    <tr>
                        <td class="col-name">{{ $module['name'] }}</td>
                        <td class="col-version">{{ $module['version'] }}</td>
                        <td class="col-date">{{ $module['date_formatted'] ?? \Carbon\Carbon::parse($module['date'])->format('jS M Y') }}</td>
                    </tr>
                @endforeach
            </table>

            @if(($showSignature ?? false) && !empty($signatureImage))
                <div class="field-signature">
                    <img src="{{ $signatureImage }}" alt="Signature">
                </div>
            @endif

            @if($showSignatoryText ?? true)
                <div class="field-signatory">{{ $executiveDirector }}</div>
            @endif

            <div class="field-date">{{ $signatureDateFormatted ?? \Carbon\Carbon::parse($signatureDate)->format('jS M Y') }}</div>
        </div>
    </div>
@else
    <div class="frame">
        <div class="certificate">
            <table class="header-table">
                <tr>
                    <td>
                        <span class="logo-mark">&lt;/&gt;</span>
                        <span style="display:inline-block; vertical-align:middle;">
                            <div class="logo-text-code">CODE</div>
                            <div class="logo-text-academy">ACADEMY</div>
                            <div class="logo-text-uganda">UGANDA</div>
                        </span>
                    </td>
                    <td class="logo-right">&laquo;CODE<br>CAMP&raquo;</td>
                </tr>
            </table>

            <div class="title">CODE Profile<br>Certificate</div>

            <table class="candidate-table">
                <tr>
                    <td class="candidate-name">{{ $candidateName }}</td>
                    <td class="candidate-no">{{ $candidateNo }}</td>
                </tr>
            </table>
            <table class="labels-table">
                <tr>
                    <td style="width:68%;">Candidate Name</td>
                    <td style="width:32%;">Candidate No.</td>
                </tr>
            </table>

            <div class="subtitle">has successfully completed the following modules:</div>

            <table class="modules-table">
                <thead>
                    <tr><th>Module Name</th><th>Version/Syllabus</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($modules as $module)
                        <tr>
                            <td>{{ $module['name'] }}</td>
                            <td>{{ $module['version'] }}</td>
                            <td>{{ $module['date_formatted'] ?? \Carbon\Carbon::parse($module['date'])->format('jS M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer-block">
                @if(($showSignature ?? false) && !empty($signatureImage))
                    <div class="footer-signature">
                        <img src="{{ $signatureImage }}" alt="Signature">
                    </div>
                @endif
                <table class="footer-table">
                    <tr>
                        <td>{{ $executiveDirector ?? 'Edward Ssempala, Executive Director Code Academy Uganda' }}</td>
                        <td class="footer-date">Date: {{ $signatureDateFormatted ?? \Carbon\Carbon::parse($signatureDate)->format('jS M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endif
</body>
</html>
