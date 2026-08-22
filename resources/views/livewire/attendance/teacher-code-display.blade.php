<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-6xl mx-auto px-4 pt-6">
        @if(config('features.code_club', false) && auth()->user()?->hasCodeClubAccess() && \App\Support\ProgramScope::context(auth()->user()) === 'codeclub')
            <x-attendance.nav-tabs context="club" />
        @endif
    </div>
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Daily Attendance Code</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $date }}</p>
    </div>

    <div class="p-6">
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Code Display Card --}}
        <div class="max-w-2xl mx-auto">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-2xl p-12 text-center print-section">
                <div class="mb-6">
                    <h2 class="text-white text-xl font-medium mb-2">Today's Attendance Code</h2>
                    <p class="text-blue-100 text-sm">Students use this code to check in/out</p>
                </div>

                {{-- The Code --}}
                <div class="bg-white rounded-xl p-8 mb-6">
                    <div class="text-6xl font-bold text-gray-900 tracking-widest font-mono">
                        {{ $code }}
                    </div>
                </div>

                {{-- QR Code Placeholder --}}
                <div class="bg-white rounded-xl p-6 mb-6 inline-block">
                    <div class="w-48 h-48 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $code }}" 
                             alt="QR Code" 
                             class="w-full h-full">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Scan to auto-fill code</p>
                </div>

                {{-- Actions --}}
                <div class="flex justify-center gap-4 no-print">
                    <button wire:click="generateNewCode" 
                            class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                        🔄 Generate New Code
                    </button>
                    <button onclick="window.print()" 
                            class="px-6 py-3 bg-blue-700 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors">
                        🖨️ Print Code
                    </button>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 no-print">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                <ol class="space-y-3 text-gray-700 dark:text-gray-300">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                        <span>Display this code on the projector or board</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                        <span>Students enter the code when they arrive (check-in)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                        <span>Students enter the code again when they leave (check-out)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-bold mr-3">4</span>
                        <span>System automatically tracks hours on premises</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-sm font-bold mr-3">5</span>
                        <span>Code automatically changes at midnight</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</div>
