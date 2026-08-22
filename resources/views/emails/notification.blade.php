@php
    $appName = config('app.name');
    $palette = [
        'info' => ['#0ea5e9', '#e0f2fe', '#0369a1'],
        'success' => ['#22c55e', '#dcfce7', '#166534'],
        'warning' => ['#f59e0b', '#fef3c7', '#92400e'],
        'error' => ['#ef4444', '#fee2e2', '#991b1b'],
    ];
    $theme = $palette[$type] ?? $palette['info'];
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} - {{ $appName }}</title>
    </head>
    <body style="margin:0;background-color:#f8fafc;font-family:Arial, Helvetica, sans-serif;color:#0f172a;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;padding:24px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="background-color:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;">
                        <tr>
                            <td style="background:linear-gradient(135deg, {{ $theme[0] }} 0%, #0f172a 100%);padding:24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="color:#ffffff;font-weight:bold;font-size:20px;">{{ $appName }}</td>
                                        <td align="right" style="color:#e2e8f0;font-size:12px;letter-spacing:0.08em;">NOTIFICATION</td>
                                    </tr>
                                </table>
                                <div style="margin-top:12px;color:#e2e8f0;font-size:14px;">Type: <strong style="color:#ffffff;">{{ strtoupper($type) }}</strong></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 24px 8px;">
                                <h2 style="margin:0 0 8px;font-size:22px;color:#0f172a;">{{ $title }}</h2>
                                <p style="margin:0;color:#475569;font-size:14px;">{{ $bodyMessage }}</p>
                            </td>
                        </tr>
                        @if(!empty($data))
                            <tr>
                                <td style="padding:0 24px 24px;">
                                    <div style="background-color:{{ $theme[1] }};border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                                        <div style="font-size:12px;color:{{ $theme[2] }};font-weight:700;letter-spacing:0.08em;">DETAILS</div>
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin-top:8px;">
                                            @foreach($data as $label => $value)
                                                <tr>
                                                    <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;width:40%;">{{ $label }}</td>
                                                    <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;font-weight:600;">{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
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
