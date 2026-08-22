<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $appName = cache()->remember('app_name', 86400, fn() => \App\Models\SystemSetting::get('app_name', config('app.name')));
    $favicon = cache()->remember('favicon_path', 86400, fn() => \App\Models\SystemSetting::get('favicon'));
@endphp

<title>{{ $title ?? $appName }}</title>

@if($favicon)
    <link rel="icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
@elseif(file_exists(public_path('favicon.ico')))
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@endif
@if(file_exists(public_path('apple-touch-icon.png')))
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endif

{{-- Critical CSS inline for faster FCP --}}
<style>
    body{margin:0;min-height:100vh}
    .dark{color-scheme:dark}
</style>

{{-- Preconnect to required origins --}}
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="dns-prefetch" href="https://code.jquery.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

{{-- Load fonts with display=swap to prevent FOIT --}}
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

{{-- Vite assets with preload hints --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

{{-- Zoho PageSense analytics --}}
<script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js"></script>

{{-- Google Tag Manager / GA4 (config-driven; no duplication) --}}
@include('partials.analytics.head')

{{-- jQuery and Summernote - Load only when needed --}}
@stack('editor-scripts')

@stack('styles')
