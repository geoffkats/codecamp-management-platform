@php $appName = config('app.name'); @endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Membership application - {{ $appName }}</title>
    </head>
    <body style="margin:0;background-color:#f8fafc;font-family:Arial, Helvetica, sans-serif;color:#0f172a;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;padding:24px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;background-color:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;">
                        <tr>
                            <td style="background:linear-gradient(135deg, #ea580c 0%, #f97316 100%);padding:24px;">
                                <div style="color:#ffffff;font-weight:bold;font-size:20px;">Code Academy Uganda</div>
                                <div style="margin-top:6px;color:#fff7ed;font-size:14px;">Code Camp membership application</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 24px 8px;">
                                <h2 style="margin:0 0 8px;font-size:20px;color:#0f172a;">Thank you, {{ $application->meta['parent_name'] ?? 'parent' }}!</h2>
                                <p style="margin:0;color:#475569;font-size:14px;line-height:1.6;">
                                    We have received the membership application for <strong>{{ $application->full_name }}</strong>.
                                    Our team will contact you on {{ $application->phone }} to confirm the place and arrange the next step.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px 24px 24px;">
                                <h3 style="margin:0 0 8px;font-size:15px;color:#0f172a;">Application details</h3>
                                @include('emails.partials.membership-details', ['application' => $application])
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 24px 24px;color:#475569;font-size:13px;line-height:1.6;">
                                Reply to this email if anything above is wrong.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:18px 24px;background-color:#0f172a;color:#94a3b8;font-size:12px;">
                                Code Academy Uganda · Reference #{{ $application->id }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
