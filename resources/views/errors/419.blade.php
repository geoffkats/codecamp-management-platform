<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session expired</title>
    <script>
        window.location.replace('/login');
    </script>
</head>
<body style="font-family: system-ui, sans-serif; background: #f8fafc; margin: 0;">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; text-align: center;">
        <div>
            <h1 style="font-size: 1.5rem;">Session expired</h1>
            <p style="color: #475569;">Do not refresh this page. Open login instead.</p>
            <p>
                <a href="/login" style="color: #ea580c; font-weight: 700;">Go to login</a>
                &nbsp;·&nbsp;
                <a href="/dashboard" style="color: #ea580c; font-weight: 700;">Go to dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
