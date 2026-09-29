<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ \App\Http\Controllers\PwaController::THEME_COLOR }}">
    <title>Offline · {{ $appName }}</title>
    <link rel="icon" href="{{ route('pwa.icon', ['size' => 192]) }}" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #eff6ff;
            color: #1e293b;
        }
        .card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.12);
        }
        img { width: 72px; height: 72px; border-radius: 16px; margin-bottom: 16px; }
        h1 { font-size: 22px; margin: 0 0 8px; color: #1e3a8a; }
        p { margin: 0 0 24px; line-height: 1.5; color: #475569; }
        button {
            border: 0;
            border-radius: 12px;
            padding: 12px 22px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background: #1e3a8a;
            cursor: pointer;
        }
        button:hover { background: #1e40af; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ route('pwa.icon', ['size' => 192]) }}" alt="{{ $appName }}">
        <h1>You're offline</h1>
        <p>{{ $appName }} needs an internet connection. Check your Wi-Fi or data, then try again.</p>
        <button type="button" onclick="window.location.reload()">Try again</button>
    </div>
    <script>
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
