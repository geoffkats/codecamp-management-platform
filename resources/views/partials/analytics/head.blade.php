{{--
    Site-wide analytics bootstrap.
    Prefer GTM. If a GTM ID is set (admin settings or .env), only GTM is loaded.
    Otherwise, fall back to direct GA4 gtag.js when a Measurement ID is set.
--}}
@php
    use App\Support\GoogleAnalytics;

    $gtmId = GoogleAnalytics::gtmId();
    $ga4Id = GoogleAnalytics::ga4MeasurementId();
@endphp

@if (filled($gtmId))
    <!-- Google Tag Manager -->
    <script>
        window.dataLayer = window.dataLayer || [];
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer',@json($gtmId));
    </script>
    <!-- End Google Tag Manager -->
@elseif (filled($ga4Id))
    <!-- Google tag (gtag.js) — GA4 fallback when GTM is not configured -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4Id) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($ga4Id));
    </script>
@endif
