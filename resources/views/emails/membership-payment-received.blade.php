@php $appName = config('app.name'); @endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Payment received - {{ $appName }}</title>
    </head>
    <body style="margin:0;background-color:#f8fafc;font-family:Arial, Helvetica, sans-serif;color:#0f172a;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;padding:24px 12px;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;background-color:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;">
                        <tr>
                            <td style="background:linear-gradient(135deg, #059669 0%, #10b981 100%);padding:24px;">
                                <div style="color:#ffffff;font-weight:bold;font-size:20px;">Code Academy Uganda</div>
                                <div style="margin-top:6px;color:#ecfdf5;font-size:14px;">Payment received</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 24px 8px;">
                                <h2 style="margin:0 0 8px;font-size:20px;color:#0f172a;">Thank you!</h2>
                                <p style="margin:0;color:#475569;font-size:14px;line-height:1.6;">
                                    We received <strong>UGX {{ number_format($payment->amount) }}</strong> for <strong>{{ $application->full_name }}</strong>'s Code Camp membership application.
                                    Our team will be in touch with camp details.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 24px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;background-color:#f8fafc;border-radius:12px;">
                                    <tr><td style="padding:10px 14px;font-size:13px;color:#64748b;">Amount</td><td style="padding:10px 14px;font-size:14px;font-weight:600;">UGX {{ number_format($payment->amount) }}</td></tr>
                                    @if($payment->payment_method)
                                        <tr><td style="padding:10px 14px;font-size:13px;color:#64748b;">Paid with</td><td style="padding:10px 14px;font-size:14px;font-weight:600;">{{ $payment->payment_method }}</td></tr>
                                    @endif
                                    @if($payment->confirmation_code)
                                        <tr><td style="padding:10px 14px;font-size:13px;color:#64748b;">Confirmation code</td><td style="padding:10px 14px;font-size:14px;font-weight:600;">{{ $payment->confirmation_code }}</td></tr>
                                    @endif
                                    <tr><td style="padding:10px 14px;font-size:13px;color:#64748b;">Reference</td><td style="padding:10px 14px;font-size:14px;font-weight:600;">{{ $payment->merchant_reference }}</td></tr>
                                    <tr><td style="padding:10px 14px;font-size:13px;color:#64748b;">Date</td><td style="padding:10px 14px;font-size:14px;font-weight:600;">{{ ($payment->paid_at ?? now())->format('j F Y, H:i') }}</td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px 24px 24px;">
                                <h3 style="margin:0 0 8px;font-size:15px;color:#0f172a;">Application details</h3>
                                @include('emails.partials.membership-details', ['application' => $application])
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:18px 24px;background-color:#0f172a;color:#94a3b8;font-size:12px;">
                                Code Academy Uganda · Keep this email as your receipt
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
