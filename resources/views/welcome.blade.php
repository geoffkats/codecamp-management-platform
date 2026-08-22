@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name'));
    $appTagline = \App\Models\SystemSetting::get('app_tagline', 'Digital Excellence Through Education');
    $logo = \App\Models\SystemSetting::get('logo');
    $logoDark = \App\Models\SystemSetting::get('logo_dark');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $appName }} - {{ $appTagline }}</title>
        <meta name="description" content="Leading provider of professional digital education and ICDL certification services. Enterprise-grade learning solutions for organizations and individuals.">
        @php
            $favicon = \App\Models\SystemSetting::get('favicon');
        @endphp
        @if($favicon)
            <link rel="icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
        @elseif(file_exists(public_path('favicon.ico')))
            <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        @endif
        @if(file_exists(public_path('apple-touch-icon.png')))
            <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Zoho PageSense analytics --}}
        <script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js"></script>
        @include('partials.analytics.head')
    </head>
    <body class="bg-white dark:bg-blue-950 antialiased">
        @include('partials.analytics.body')
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 dark:bg-blue-950/95 backdrop-blur-md border-b border-blue-200 dark:border-blue-900 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                            @if($logo || $logoDark)
                                <img src="{{ asset('storage/' . ($logo ?: $logoDark)) }}" alt="{{ $appName }}" class="h-10 dark:hidden">
                                <img src="{{ asset('storage/' . ($logoDark ?: $logo)) }}" alt="{{ $appName }}" class="h-10 hidden dark:block">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-blue-900 dark:bg-blue-700 flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">{{ substr($appName, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">{{ $appName }}</span>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Professional Digital Education</span>
                            </div>
                        </a>
                        <span class="hidden lg:inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-semibold border border-orange-200">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                            </svg>
                            ICDL Accredited
                        </span>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="hidden md:flex items-center gap-8">
                            <a href="#solutions" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Solutions</a>
                            <a href="#impact" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Impact</a>
                            <a href="#partners" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Partners</a>
                            <a href="#faq" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">FAQs</a>
                        </div>
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors font-medium text-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors font-medium text-sm">
                                Sign In
                            </a>
                            @if (Route::has('register'))
                                <div class="hidden sm:flex items-center gap-3">
                                    <a href="#contact" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-blue-700 text-gray-700 dark:text-gray-200 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-blue-900 transition">
                                        Book Appointment
                                    </a>
                                    <a href="{{ route('register') }}" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all text-sm">
                                        Get Started
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Professional Content -->
                    <div class="space-y-8">
                        <div>
                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-4">Code Academy Uganda</p>
                            <h1 class="text-5xl md:text-6xl lg:text-6xl font-bold text-blue-900 dark:text-white leading-tight tracking-tight">
                                Professional Digital Skills for Uganda
                            </h1>
                        </div>
                        
                        <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed max-w-lg">
                            Empowering learners and institutions in Uganda with ICDL-accredited training, professional development, and industry-ready tech skills.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                                    Access Platform
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                                    Start Free Trial
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-white dark:bg-blue-900 hover:bg-orange-50 dark:hover:bg-blue-800 text-blue-900 dark:text-white border-2 border-blue-900 dark:border-blue-700 rounded-lg font-semibold transition-all">
                                    Sign In
                                </a>
                            @endauth
                        </div>

                        <!-- Trust Metrics -->
                        <div class="grid grid-cols-3 gap-8 pt-8 border-t border-blue-200 dark:border-blue-900">
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">500+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Active Learners</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">50+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Professional Courses</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">ICDL</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Accredited Center</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Visual Element -->
                    <div class="relative hidden lg:block">
                        <div class="absolute -top-10 -right-10 w-40 h-40 text-orange-400/20 dark:text-blue-300/20">
                            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="8"/>
                                <circle cx="50" cy="50" r="26" stroke="currentColor" stroke-width="8"/>
                            </svg>
                        </div>
                        <div class="absolute -bottom-12 -left-12 w-48 h-48 text-blue-900/10 dark:text-white/10">
                            <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <rect x="6" y="6" width="108" height="108" rx="18" stroke="currentColor" stroke-width="6"/>
                                <path d="M24 70L52 42L70 60L96 34" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="relative bg-orange-50 dark:bg-blue-900 rounded-2xl overflow-hidden shadow-lg border border-orange-200 dark:border-blue-700">
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-orange-50/40 to-orange-100/30 dark:via-blue-900/20 dark:to-blue-800/10"></div>
                            <img src="{{ asset('images/hero-training.svg') }}" alt="Professional training and certification" class="relative w-full h-full object-cover">
                            <div class="absolute bottom-6 left-6 right-6 bg-white/95 dark:bg-blue-950/90 backdrop-blur-sm border border-orange-200/60 dark:border-blue-700/40 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Certified Programs</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">ICDL • Professional Development • Workforce Upskilling</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 text-white text-xs font-semibold">A+</span>
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-900 text-white text-xs font-semibold">ISO</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Solutions Section -->
        <section id="solutions" class="py-24 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 mb-14">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-4">Solutions</p>
                        <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white">
                            Professional Development Programs
                        </h2>
                        <p class="mt-4 text-lg text-gray-700 dark:text-gray-300 max-w-2xl">
                            Purpose-built pathways for institutions, schools, and professionals. Each program is structured around measurable outcomes and industry standards.
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-6">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">ICDL</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Accredited Center</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">50+</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Course Tracks</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">500+</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Graduates</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="bg-white dark:bg-blue-900 rounded-2xl border border-orange-200 dark:border-blue-700 p-8">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-blue-900 dark:text-white">ICDL Certification</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Internationally recognized digital skills</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-blue-800">Core</span>
                        </div>
                        <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Computer essentials, online collaboration, and productivity tools.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Standardized assessments with audit-ready reporting.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Certification preparation and exam management.
                            </li>
                        </ul>
                        <div class="mt-8 pt-6 border-t border-orange-200 dark:border-blue-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Best for: Schools, institutions, and corporate teams.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-blue-900 rounded-2xl border border-orange-200 dark:border-blue-700 p-8">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-blue-900 dark:text-white">Enterprise Upskilling</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Structured workforce development</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-blue-800">Scale</span>
                        </div>
                        <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Role-based learning pathways and progress benchmarks.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Cohort management, attendance, and completion analytics.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Compliance-ready reports for leadership reviews.
                            </li>
                        </ul>
                        <div class="mt-8 pt-6 border-t border-orange-200 dark:border-blue-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Best for: Government agencies and enterprises.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-blue-900 rounded-2xl border border-orange-200 dark:border-blue-700 p-8">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-blue-900 dark:text-white">Professional Tech Tracks</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Job-ready skills for learners</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-blue-800">Career</span>
                        </div>
                        <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Web development, mobile apps, and data foundations.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Portfolio projects with mentorship and review.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 w-2 h-2 rounded-full bg-orange-500"></span>
                                Career readiness, CV reviews, and mock interviews.
                            </li>
                        </ul>
                        <div class="mt-8 pt-6 border-t border-orange-200 dark:border-blue-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Best for: Students and professionals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Impact Section -->
        <section id="impact" class="py-24 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-4">Proven Results</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mb-4">
                        Measurable Business Impact
                    </h2>
                    <p class="text-lg text-gray-700 dark:text-gray-300 max-w-2xl mx-auto">
                        Organizations using our platform report significant improvements in employee skills, reduced training costs, and accelerated digital transformation.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center p-8 rounded-lg bg-white dark:bg-blue-900 border border-orange-200 dark:border-blue-700">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">94%</div>
                        <p class="text-gray-700 dark:text-gray-300">Learner Completion Rate</p>
                    </div>
                    <div class="text-center p-8 rounded-lg bg-white dark:bg-blue-900 border border-orange-200 dark:border-blue-700">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">3.2x</div>
                        <p class="text-gray-700 dark:text-gray-300">ROI within 12 Months</p>
                    </div>
                    <div class="text-center p-8 rounded-lg bg-white dark:bg-blue-900 border border-orange-200 dark:border-blue-700">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">87%</div>
                        <p class="text-gray-700 dark:text-gray-300">Certification Success Rate</p>
                    </div>
                    <div class="text-center p-8 rounded-lg bg-white dark:bg-blue-900 border border-orange-200 dark:border-blue-700">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">500+</div>
                        <p class="text-gray-700 dark:text-gray-300">Certified Professionals</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners & Accreditation -->
        <section id="partners" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-10">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Partners & Accreditation</p>
                        <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white">Trusted Learning Partners</h2>
                    </div>
                    <p class="text-lg text-gray-700 dark:text-gray-300 max-w-2xl">
                        We collaborate with schools, institutions, and professional bodies to deliver recognized certifications and job-ready skills.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="flex items-center gap-3 h-20 px-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-lg bg-white border border-orange-200 text-blue-900">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-700">ICDL Center</span>
                    </div>
                    <div class="flex items-center gap-3 h-20 px-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-lg bg-white border border-orange-200 text-blue-900">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-700">School Partners</span>
                    </div>
                    <div class="flex items-center gap-3 h-20 px-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-lg bg-white border border-orange-200 text-blue-900">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v6H3V3zm0 10h18v8H3v-8z" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-700">Corporate Training</span>
                    </div>
                    <div class="flex items-center gap-3 h-20 px-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-lg bg-white border border-orange-200 text-blue-900">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-700">Community Programs</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-24 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-4">Why Choose Us</p>
                        <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mb-6">
                            Enterprise-Grade Learning Infrastructure
                        </h2>
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-8">
                            Built for organizations that demand reliability, scalability, and measurable outcomes. Our platform is trusted by leading institutions across East Africa.
                        </p>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-blue-700 dark:bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 dark:text-white mb-1">ICDL Accredited</h3>
                                    <p class="text-gray-700 dark:text-gray-300">Deliver internationally recognized certifications through our authorized testing center.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-blue-700 dark:bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 dark:text-white mb-1">Advanced Analytics</h3>
                                    <p class="text-gray-700 dark:text-gray-300">Real-time dashboards and reporting to track learner progress and organizational KPIs.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-blue-700 dark:bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 dark:text-white mb-1">Dedicated Support</h3>
                                    <p class="text-gray-700 dark:text-gray-300">Professional customer success team available to ensure smooth implementation and adoption.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-blue-700 dark:bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 dark:text-white mb-1">Custom Integration</h3>
                                    <p class="text-gray-700 dark:text-gray-300">Seamlessly integrate with your existing HR and business systems via API.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-blue-900 rounded-2xl p-12 border border-orange-200 dark:border-blue-700">
                        <div class="space-y-8">
                            <div class="border-l-4 border-orange-500 dark:border-blue-400 pl-6">
                                <h4 class="text-blue-900 dark:text-white font-semibold text-lg mb-2">Scalable Infrastructure</h4>
                                <p class="text-gray-700 dark:text-gray-300 text-sm">Support from 10 to 10,000+ concurrent users without performance degradation.</p>
                            </div>

                            <div class="border-l-4 border-orange-500 dark:border-blue-400 pl-6">
                                <h4 class="text-blue-900 dark:text-white font-semibold text-lg mb-2">Mobile-Optimized</h4>
                                <p class="text-gray-700 dark:text-gray-300 text-sm">Deliver training on any device. Responsive design ensures optimal experience everywhere.</p>
                            </div>

                            <div class="border-l-4 border-orange-500 dark:border-blue-400 pl-6">
                                <h4 class="text-blue-900 dark:text-white font-semibold text-lg mb-2">Security First</h4>
                                <p class="text-gray-700 dark:text-gray-300 text-sm">Enterprise-grade encryption, regular audits, and compliance with international data standards.</p>
                            </div>

                            <div class="border-l-4 border-orange-500 dark:border-blue-400 pl-6">
                                <h4 class="text-blue-900 dark:text-white font-semibold text-lg mb-2">Offline Capability</h4>
                                <p class="text-gray-700 dark:text-gray-300 text-sm">Users can continue learning without internet connection. Automatic sync when online.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-24 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Client Feedback</p>
                        <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white">Trusted by Institutions</h2>
                    </div>
                    <p class="text-lg text-gray-700 dark:text-gray-300 max-w-2xl">
                        Our platform helps organizations deliver measurable digital learning outcomes with enterprise-grade reliability.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white dark:bg-blue-900 rounded-xl p-8 border border-orange-200 dark:border-blue-700">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-semibold">EA</div>
                            <div>
                                <p class="font-semibold text-blue-900 dark:text-white">Emily A.</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Training Manager</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                            “We rolled out ICDL training across 300+ staff and saw completion rates rise within weeks.”
                        </p>
                    </div>
                    <div class="bg-white dark:bg-blue-900 rounded-xl p-8 border border-orange-200 dark:border-blue-700">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-semibold">KM</div>
                            <div>
                                <p class="font-semibold text-blue-900 dark:text-white">Kenny M.</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Operations Lead</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                            “The analytics and reporting tools make it easy to show ROI to leadership.”
                        </p>
                    </div>
                    <div class="bg-white dark:bg-blue-900 rounded-xl p-8 border border-orange-200 dark:border-blue-700">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-semibold">JN</div>
                            <div>
                                <p class="font-semibold text-blue-900 dark:text-white">Jennifer N.</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">HR Director</p>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                            “Clean UX, great support, and a professional experience our teams trust.”
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-24 px-4 sm:px-6 lg:px-8 bg-white dark:bg-blue-950">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-12">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">FAQs</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white">Answers to Common Questions</h2>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">Quick guidance on enrollment, certification, and training support.</p>
                </div>

                <div class="space-y-4">
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-white">How do students enroll?</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">Learners can register online and choose a program. Our team verifies details and shares the onboarding guide.</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-white">Do you provide ICDL certifications?</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">Yes. We are an ICDL-accredited testing center and provide certification pathways and assessments.</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-white">Can organizations train entire teams?</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">Absolutely. We deliver cohort-based programs with reporting, progress tracking, and custom dashboards.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resources Section -->
        <section class="py-24 px-4 sm:px-6 lg:px-8 bg-orange-50 dark:bg-blue-950">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 mb-12">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Resources</p>
                        <h2 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white">Professional Insights</h2>
                    </div>
                    <p class="text-lg text-gray-700 dark:text-gray-300 max-w-2xl">
                        Practical guides and frameworks to support workforce digital transformation and certification readiness.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white dark:bg-blue-900 rounded-xl border border-orange-200 dark:border-blue-700 overflow-hidden">
                        <img src="{{ asset('images/resource-icdl.svg') }}" alt="ICDL adoption guide" class="w-full h-40 object-cover">
                        <div class="p-6">
                            <h3 class="font-semibold text-blue-900 dark:text-white mb-2">ICDL Adoption Guide</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">A step-by-step framework to roll out ICDL certification in large teams.</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-blue-900 rounded-xl border border-orange-200 dark:border-blue-700 overflow-hidden">
                        <img src="{{ asset('images/resource-roadmap.svg') }}" alt="Digital skills roadmap" class="w-full h-40 object-cover">
                        <div class="p-6">
                            <h3 class="font-semibold text-blue-900 dark:text-white mb-2">Digital Skills Roadmap</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Define competency pathways that align with business objectives.</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-blue-900 rounded-xl border border-orange-200 dark:border-blue-700 overflow-hidden">
                        <img src="{{ asset('images/resource-analytics.svg') }}" alt="Learning analytics playbook" class="w-full h-40 object-cover">
                        <div class="p-6">
                            <h3 class="font-semibold text-blue-900 dark:text-white mb-2">Learning Analytics Playbook</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Best practices for tracking engagement, progress, and ROI.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="contact" class="py-24 px-4 sm:px-6 lg:px-8 bg-blue-950 relative overflow-hidden">
            <div class="absolute -top-16 -right-16 w-64 h-64 text-orange-500/15">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="8"/>
                    <path d="M18 60L40 38L54 52L82 24" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="absolute -bottom-20 -left-12 w-56 h-56 text-orange-500/10">
                <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <rect x="10" y="10" width="100" height="100" rx="18" stroke="currentColor" stroke-width="6"/>
                    <circle cx="60" cy="60" r="22" stroke="currentColor" stroke-width="6"/>
                </svg>
            </div>
            <div class="max-w-4xl mx-auto text-center relative">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Transform Your Organization's Digital Capability
                </h2>
                <p class="text-xl text-gray-200 mb-8">
                    Schedule a consultation with our team to explore how our platform can accelerate your digital transformation initiatives.
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                        Go to Dashboard
                    </a>
                @else
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                            Request Demo
                        </a>
                        <a href="{{ route('login') }}" class="inline-block px-8 py-4 bg-transparent hover:bg-white/10 text-white border-2 border-white/30 rounded-lg font-semibold transition-all">
                            Sign In
                        </a>
                    </div>
                @endauth
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-blue-900 text-blue-100 py-16 px-4 sm:px-6 lg:px-8 border-t border-blue-700 relative overflow-hidden">
            <div class="absolute -top-20 -right-10 w-72 h-72 text-orange-500/10">
                <svg viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <circle cx="70" cy="70" r="60" stroke="currentColor" stroke-width="8"/>
                    <circle cx="70" cy="70" r="36" stroke="currentColor" stroke-width="8"/>
                </svg>
            </div>
            <div class="absolute -bottom-24 -left-16 w-72 h-72 text-orange-500/10">
                <svg viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <rect x="12" y="12" width="116" height="116" rx="22" stroke="currentColor" stroke-width="8"/>
                    <path d="M36 86L66 56L84 74L112 46" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="max-w-7xl mx-auto relative">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div>
                        <h3 class="text-white font-bold text-lg mb-4">{{ $appName }}</h3>
                        <p class="text-sm mb-4 leading-relaxed text-gray-300">
                            Enterprise-grade learning platform delivering professional development and ICDL certifications.
                        </p>
                        <div class="space-y-2 text-sm text-gray-300">
                            <p>📍 Kampala, Uganda</p>
                            <p>📧 info@{{ strtolower(str_replace(' ', '', $appName)) }}.ug</p>
                            <p>📞 +256 (0) 700 000 000</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-gray-200 font-semibold mb-4 text-sm uppercase tracking-wide">Platform</h4>
                        <ul class="space-y-2 text-sm text-gray-300">
                            @auth
                                <li><a href="{{ route('courses.index') }}" class="hover:text-white transition-colors">Courses</a></li>
                                <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a></li>
                            @else
                                <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Sign Up</a></li>
                                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a></li>
                            @endauth
                            <li><a href="#solutions" class="hover:text-white transition-colors">Solutions</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-gray-200 font-semibold mb-4 text-sm uppercase tracking-wide">Programs</h4>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li><span class="hover:text-white transition-colors cursor-default">ICDL Certification</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Web Development</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Mobile Development</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Database Design</span></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-gray-200 font-semibold mb-4 text-sm uppercase tracking-wide">Legal</h4>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li><span class="hover:text-white transition-colors cursor-default">Privacy Policy</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Terms of Service</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Cookie Policy</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Contact Support</span></li>
                        </ul>
                    </div>
                </div>
                <div class="pt-12 border-t border-blue-700 text-center text-sm">
                    <p class="mb-2">&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
                    <p class="text-gray-300">Professional Learning & Development Platform | ICDL Accredited Testing Center</p>
                </div>
            </div>
        </footer>
    </body>
</html>
