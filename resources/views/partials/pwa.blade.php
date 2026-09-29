<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="{{ \App\Http\Controllers\PwaController::THEME_COLOR }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ \App\Models\SystemSetting::get('app_name', config('app.name')) }}">
@unless(file_exists(public_path('apple-touch-icon.png')))
    <link rel="apple-touch-icon" href="{{ route('pwa.icon', ['size' => 192]) }}">
@endunless
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(@json(asset('sw.js')), { scope: @json(rtrim(url('/'), '/') . '/') }).catch(() => {});
        });
    }
</script>
