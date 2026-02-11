<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <div class="text-center">
                <!-- Error Icon -->
                <div class="mb-6">
                    <svg class="mx-auto h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <!-- Error Title -->
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    403 - Access Denied
                </h1>

                <!-- Error Message -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 text-left">
                    <p class="text-gray-700 mb-4">
                        @if(isset($exception) && $exception->getMessage())
                            {{ $exception->getMessage() }}
                        @else
                            You don't have permission to access this page or resource.
                        @endif
                    </p>
                </div>

                <!-- Helpful Tips -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                    <h3 class="font-semibold text-blue-900 mb-2">Why might this happen?</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>You may not be enrolled in the required course</li>
                        <li>The content may be locked or unavailable</li>
                        <li>The content may require approval before access</li>
                        <li>You may need to log in with a different account</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go to Dashboard
                    </a>
                    <button onclick="history.back()" 
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Go Back
                    </button>
                </div>

                <!-- Support Link -->
                <div class="mt-8 text-sm text-gray-500">
                    Need help? 
                    <a href="mailto:support@codeacademyug.org" class="text-blue-600 hover:text-blue-800 underline">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
