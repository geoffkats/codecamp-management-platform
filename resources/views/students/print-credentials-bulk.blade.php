<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Credentials (Bulk)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .title { font-size: 22px; font-weight: 700; }
        .subtitle { color: #6b7280; font-size: 13px; }
        .print-btn { background: #2563eb; color: #fff; border: 0; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .row { display: flex; justify-content: space-between; border-bottom: 1px dashed #e5e7eb; padding: 6px 0; }
        .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        .value { font-size: 13px; font-weight: 600; }
        .page-break { page-break-after: always; }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
            .card { border: none; border-bottom: 1px solid #e5e7eb; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Student Login Credentials (Bulk)</div>
            <div class="subtitle">Generated on {{ now()->format('M j, Y g:i A') }}</div>
        </div>
        <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    @foreach($students as $student)
        <div class="card">
            <div class="row">
                <div class="label">Student Name</div>
                <div class="value">{{ $student->full_name }}</div>
            </div>
            <div class="row">
                <div class="label">Student ID</div>
                <div class="value">{{ $student->student_id }}</div>
            </div>
            <div class="row">
                <div class="label">Login Username</div>
                <div class="value">{{ $student->user?->loginIdentifier() ?: $student->student_id }}</div>
            </div>
            <div class="row">
                <div class="label">Password</div>
                <div class="value">{{ $student->user?->initial_password ?: 'Set by student' }}</div>
            </div>
            <div class="row">
                <div class="label">Program</div>
                <div class="value">{{ strtoupper($student->program_type ?? 'N/A') }}</div>
            </div>
            @if($student->school)
                <div class="row">
                    <div class="label">School</div>
                    <div class="value">{{ $student->school->name }}</div>
                </div>
            @endif
        </div>
    @endforeach
</body>
</html>
