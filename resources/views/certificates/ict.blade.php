<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        html, body {
            margin: 0;
            padding: 0;
            width: {{ $page['width'] ?? 297 }}mm;
            height: {{ $page['height'] ?? 210 }}mm;
        }
        body {
            font-family: "Poppins", "DejaVu Sans", Arial, sans-serif;
            @if(!empty($backgroundImage) && file_exists($backgroundImage))
            background-image: url("file://{{ $backgroundImage }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            @else
            background: #ffffff;
            @endif
            position: relative;
        }
    </style>
</head>
<body>
    <div style="position:absolute; left: {{ $layout['name']['x'] ?? 120 }}{{ $unit ?? 'mm' }}; top: {{ $layout['name']['y'] ?? 310 }}{{ $unit ?? 'mm' }}; width: {{ $layout['name']['width'] ?? 220 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['name']['size'] ?? 28 }}pt; font-weight: 700; text-align: {{ strtolower($layout['name']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['name']['color'] ?? [11,45,107]) }}); line-height: 1.2;">
        {{ $studentName }}
    </div>

    <div style="position:absolute; left: {{ $layout['candidate_no']['x'] ?? 120 }}{{ $unit ?? 'mm' }}; top: {{ $layout['candidate_no']['y'] ?? 350 }}{{ $unit ?? 'mm' }}; width: {{ $layout['candidate_no']['width'] ?? 160 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['candidate_no']['size'] ?? 12 }}pt; font-weight: 400; text-align: {{ strtolower($layout['candidate_no']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['candidate_no']['color'] ?? [55,65,81]) }}); line-height: 1.2;">
        {{ $candidateNo }}
    </div>

    <div style="position:absolute; left: {{ $layout['module']['x'] ?? 120 }}{{ $unit ?? 'mm' }}; top: {{ $layout['module']['y'] ?? 420 }}{{ $unit ?? 'mm' }}; width: {{ $layout['module']['width'] ?? 220 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['module']['size'] ?? 14 }}pt; font-weight: 400; text-align: {{ strtolower($layout['module']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['module']['color'] ?? [55,65,81]) }}); line-height: 1.2;">
        {{ $module }}
    </div>

    <div style="position:absolute; left: {{ $layout['version']['x'] ?? 120 }}{{ $unit ?? 'mm' }}; top: {{ $layout['version']['y'] ?? 460 }}{{ $unit ?? 'mm' }}; width: {{ $layout['version']['width'] ?? 120 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['version']['size'] ?? 12 }}pt; font-weight: 400; text-align: {{ strtolower($layout['version']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['version']['color'] ?? [55,65,81]) }}); line-height: 1.2;">
        {{ $version }}
    </div>

    <div style="position:absolute; left: {{ $layout['date']['x'] ?? 420 }}{{ $unit ?? 'mm' }}; top: {{ $layout['date']['y'] ?? 520 }}{{ $unit ?? 'mm' }}; width: {{ $layout['date']['width'] ?? 120 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['date']['size'] ?? 12 }}pt; font-weight: 400; text-align: {{ strtolower($layout['date']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['date']['color'] ?? [55,65,81]) }}); line-height: 1.2;">
        {{ $date }}
    </div>

    <div style="position:absolute; left: {{ $layout['footer_date']['x'] ?? 420 }}{{ $unit ?? 'mm' }}; top: {{ $layout['footer_date']['y'] ?? 780 }}{{ $unit ?? 'mm' }}; width: {{ $layout['footer_date']['width'] ?? 120 }}{{ $unit ?? 'mm' }}; font-size: {{ $layout['footer_date']['size'] ?? 12 }}pt; font-weight: 400; text-align: {{ strtolower($layout['footer_date']['align'] ?? 'left') }}; color: rgb({{ implode(',', $layout['footer_date']['color'] ?? [55,65,81]) }}); line-height: 1.2;">
        {{ $footerDate }}
    </div>
</body>
</html>
