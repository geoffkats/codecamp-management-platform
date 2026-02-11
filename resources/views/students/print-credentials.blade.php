<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Credentials</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; max-width: 720px; margin: 0 auto; }
        .title { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .subtitle { color: #6b7280; font-size: 13px; margin-bottom: 18px; }
        .row { display: flex; justify-content: space-between; border-bottom: 1px dashed #e5e7eb; padding: 10px 0; }
        .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .value { font-size: 14px; font-weight: 600; }
        .footer { margin-top: 18px; font-size: 12px; color: #6b7280; }
        .print-btn { margin: 0 auto 18px; display: block; background: #2563eb; color: #fff; border: 0; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
            .card { border: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print</button>

    <div class="card">
        <div class="title">Student Login Credentials</div>
        <div class="subtitle">Keep this safe. Share only with the student/parent.</div>

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
            <div class="value">{{ $student->user?->email ?: $student->student_id }}</div>
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

        <div class="footer">
            Generated on {{ now()->format('M j, Y g:i A') }}
        </div>
    </div>
</body>
</html>
