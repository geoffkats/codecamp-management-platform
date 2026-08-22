<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* ── Page setup ── */
        @page {
            margin: 0;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            width: 210mm;
            height: 297mm;
            position: relative;
            overflow: hidden;
        }

        /* ── Certificate background image ── */
        .bg-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }

        /* ── Content layer sits on top ── */
        .content {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 1;
        }

        /* ── Candidate name & number ── */
        .candidate-name {
            position: absolute;
            top: 115mm;
            left: 20mm;
            width: 130mm;
            font-size: 13pt;
            font-weight: bold;
            color: #1a1a1a;
            letter-spacing: 0.3pt;
        }

        .candidate-no {
            position: absolute;
            top: 115mm;
            left: 160mm;
            width: 40mm;
            font-size: 12pt;
            font-weight: bold;
            color: #1a1a1a;
            text-align: left;
        }

        /* ── Modules table ── */
        .modules-wrapper {
            position: absolute;
            top: 148mm;
            left: 18mm;
            width: 174mm;
        }

        .modules-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modules-table td {
            font-size: 9.5pt;
            color: #1a1a1a;
            padding: 3.5mm 2mm;
            vertical-align: top;
            border-bottom: 0.3pt solid #d0d0d0;
        }

        .modules-table td:first-child  { width: 45%; }
        .modules-table td:nth-child(2) { width: 35%; }
        .modules-table td:last-child   { width: 20%; }

        /* ── Signature date (bottom right) ── */
        .signature-date {
            position: absolute;
            bottom: 22mm;
            left: 160mm;
            width: 40mm;
            font-size: 9.5pt;
            color: #1a1a1a;
        }
    </style>
</head>
<body>

    {{-- Background: the original certificate template converted to an image --}}
    @if (!empty($backgroundImage) && file_exists($backgroundImage))
        <img
            class="bg-template"
            src="file://{{ $backgroundImage }}"
            alt=""
        />
    @endif

    <div class="content">

        {{-- Candidate name --}}
        <div class="candidate-name">{{ $candidateName }}</div>

        {{-- Candidate number --}}
        <div class="candidate-no">{{ $candidateNo }}</div>

        {{-- Modules table --}}
        <div class="modules-wrapper">
            <table class="modules-table">
                @foreach ($modules as $module)
                    <tr>
                        <td>{{ $module['name'] }}</td>
                        <td>{{ $module['version'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($module['date'])->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        {{-- Signature / issue date --}}
        <div class="signature-date">
            {{ \Carbon\Carbon::parse($signatureDate)->format('d M Y') }}
        </div>

    </div>

</body>
</html>