<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name'));
    $favicon = \App\Models\SystemSetting::get('favicon');
@endphp

<title>{{ $title ?? $appName }}</title>

@if($favicon)
    <link rel="icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endif
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

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

{{-- jQuery and Summernote - Load only when needed --}}
@stack('editor-scripts')

@stack('styles')
