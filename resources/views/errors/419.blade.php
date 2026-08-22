<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page expired</title>
    <script>
        @php
            $continueUrl = auth()->check()
                ? url('/curriculum/builder')
                : url('/login');
        @endphp
        if (window.history.replaceState) {
            window.history.replaceState(null, '', @json($continueUrl));
        }
    </script>
</head>
<body style="font-family: system-ui, sans-serif; background: #f8fafc; margin: 0;">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;">
        <div style="max-width: 28rem; text-align: center;">
            <h1 style="font-size: 1.5rem; margin: 0 0 0.75rem;">This page expired</h1>
            <p style="color: #475569; margin: 0 0 1.5rem;">
                Your session token expired after a long wait or a heavy save. Refreshing this screen keeps you stuck — open the link below instead.
            </p>
            <a href="{{ $continueUrl }}"
               style="display: inline-block; background: #ea580c; color: white; font-weight: 600; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none;">
                Continue
            </a>
        </div>
    </div>
</body>
</html>
