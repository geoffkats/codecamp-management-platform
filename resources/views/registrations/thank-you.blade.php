<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Thank You - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Zoho PageSense analytics --}}
        <script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js"></script>
        @include('partials.analytics.head')
    </head>
    <body class="bg-white dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-20 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 text-orange-600 mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mb-4">Thank You!</h1>
                <p class="text-lg text-gray-700 dark:text-gray-300 mb-8">
                    {{ session('message', 'Your registration has been received. Our team will contact you shortly.') }}
                </p>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                    Back to Home
                </a>
            </div>
        </div>
        {{-- Fires only when RegistrationController flashed registration_conversion --}}
        @include('partials.analytics.conversion')
    </body>
</html>
