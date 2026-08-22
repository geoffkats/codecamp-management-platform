@php
    $appName = config('app.name');
    $typeLabel = strtoupper($registration->type);
    $fields = [
        'Name' => $registration->full_name,
        'Email' => $registration->email,
        'Phone' => $registration->phone,
        'Organization' => $registration->organization_name,
        'Role' => $registration->role_title,
        'Program Interest' => $registration->program_interest,
        'Course Interest' => $registration->course_interest,
        'School Level' => $registration->school_level,
        'Students Count' => $registration->students_count,
        'National ID / Passport' => $registration->national_id,
        'Date of Birth' => $registration->date_of_birth?->format('Y-m-d'),
        'Gender' => $registration->gender,
        'Preferred Schedule' => $registration->preferred_schedule,
        'Preferred Exam Date' => $registration->preferred_exam_date?->format('Y-m-d'),
        'ICDL Modules' => $registration->icdl_modules ? implode(', ', $registration->icdl_modules) : null,
    ];

    if ($registration->meta) {
        foreach ($registration->meta as $key => $value) {
            if (!$value) {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', $key));
            $fields[$label] = is_array($value) ? implode(', ', $value) : $value;
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>New Registration - {{ $appName }}</title>
    </head>
    <body style="margin:0;background-color:#f8fafc;font-family:Arial, Helvetica, sans-serif;color:#0f172a;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;padding:24px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="background-color:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;">
                        <tr>
                            <td style="background:linear-gradient(135deg, #ea580c 0%, #f97316 100%);padding:24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="color:#ffffff;font-weight:bold;font-size:20px;">{{ $appName }}</td>
                                        <td align="right" style="color:#fff7ed;font-size:12px;letter-spacing:0.08em;">NEW REGISTRATION</td>
                                    </tr>
                                </table>
                                <div style="margin-top:12px;color:#fff7ed;font-size:14px;">Type: <strong style="color:#ffffff;">{{ $typeLabel }}</strong></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 24px 8px;">
                                <h2 style="margin:0 0 8px;font-size:22px;color:#0f172a;">Registration Details</h2>
                                <p style="margin:0;color:#475569;font-size:14px;">A new request has been submitted. Review the details below and follow up.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 24px 24px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                    @foreach($fields as $label => $value)
                                        @if($value)
                                            <tr>
                                                <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b;width:40%;">{{ $label }}</td>
                                                <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#0f172a;font-weight:600;">{{ $value }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                        @if($registration->message)
                            <tr>
                                <td style="padding:0 24px 24px;">
                                    <div style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:16px;">
                                        <div style="font-size:12px;color:#9a3412;font-weight:700;letter-spacing:0.08em;">MESSAGE</div>
                                        <div style="margin-top:8px;font-size:14px;color:#7c2d12;">{{ $registration->message }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding:20px 24px 28px;background-color:#0f172a;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="color:#e2e8f0;font-size:12px;">Code Academy Uganda</td>
                                        <td align="right" style="color:#94a3b8;font-size:12px;">{{ now()->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
