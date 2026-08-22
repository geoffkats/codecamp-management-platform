{{-- Google Tag Manager (noscript) — place immediately after opening <body> --}}
@php
    $gtmId = \App\Support\GoogleAnalytics::gtmId();
@endphp

@if (filled($gtmId))
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode($gtmId) }}"
                height="0" width="0" style="display:none;visibility:hidden"
                title="Google Tag Manager"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif
