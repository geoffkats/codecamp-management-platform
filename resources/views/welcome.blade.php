@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name'));
    $appTagline = \App\Models\SystemSetting::get('app_tagline', 'E-Learning Platform');
    $logo = \App\Models\SystemSetting::get('logo');
    $logoDark = \App\Models\SystemSetting::get('logo_dark');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $appName }} - {{ $appTagline }}</title>
        <meta name="description" content="{{ $appName }}'s e-learning platform. Join our code camps, learn web development, mobile apps, and earn ICDL certifications. Empowering Ugandans with digital skills.">
        @php
            $favicon = \App\Models\SystemSetting::get('favicon');
        @endphp
        @if($favicon)
            <link rel="icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-gray-900 antialiased">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                            @if($logo || $logoDark)
                                <img src="{{ asset('storage/' . ($logo ?: $logoDark)) }}" alt="{{ $appName }}" class="h-10 dark:hidden">
                                <img src="{{ asset('storage/' . ($logoDark ?: $logo)) }}" alt="{{ $appName }}" class="h-10 hidden dark:block">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">{{ substr($appName, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $appName }}</span>
                            </div>
                        </a>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors font-medium">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors font-medium">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Modern Hero Section - Left Text + Right Image -->
        <section class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-800">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center min-h-[600px]">
                    <!-- Left: Text Content -->
                    <div class="space-y-8">
                        <div>
                            <span class="inline-block px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold mb-6">
                                ✨ Accredited ICDL Testing Center
                            </span>
                        </div>
                        
                        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white leading-tight">
                            Bridge the Digital
                            <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent block">Divide Through Code</span>
                        </h1>
                        
                        <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                            Join {{ $appName }}'s comprehensive e-learning platform. Learn web development, mobile apps, robotics, and earn globally recognized ICDL certifications.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                                    Go to Dashboard
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                                    Start Learning Free
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-700 rounded-xl font-semibold text-lg transition-all">
                                    Sign In
                                </a>
                            @endauth
                        </div>

                        <!-- Trust Indicators -->
                        <div class="flex items-center gap-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">500+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Students</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">50+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Courses</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">ICDL</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Certified</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Hero Image/Illustration -->
                    <div class="relative">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                            <!-- Placeholder for hero image - you can replace with actual image -->
                            <div class="aspect-[4/3] bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center">
                                <div class="text-center text-white p-8">
                                    <svg class="w-32 h-32 mx-auto mb-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <p class="text-xl font-semibold">Empowering Future Developers</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Cards -->
                        <div class="absolute -bottom-6 -left-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">ICDL Certified</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Globally Recognized</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="absolute -top-6 -right-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Ages 7-19</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Code Camps</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

                <!-- Stats -->
                <div class="mt-20 grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">ICDL</div>
                        <div class="text-gray-600 dark:text-gray-400">Accredited Testing Center</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">Ages 7-19</div>
                        <div class="text-gray-600 dark:text-gray-400">Code Camp Programs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-pink-600 dark:text-pink-400 mb-2">Kampala</div>
                        <div class="text-gray-600 dark:text-gray-400">Based in Uganda</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">2025</div>
                        <div class="text-gray-600 dark:text-gray-400">Active Code Camps</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        Our Code Camp Programs
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        From Scratch programming for kids to professional web development and ICDL certifications. 
                        Everything you need to succeed in the digital world.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Web Development</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Learn to build modern, responsive websites and web applications from scratch. Master HTML, CSS, JavaScript, and modern frameworks.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mobile App Development</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Create powerful and intuitive applications for Android and iOS devices. Learn modern mobile development frameworks and tools.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 bg-gradient-to-br from-pink-50 to-pink-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">ICDL Certification</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Master essential computer skills recognized globally. Earn ICDL certificates from our accredited testing center in Kampala.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 bg-gradient-to-br from-green-50 to-green-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Robotics & STEM</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Hands-on learning in AI, coding, STEM, and robotics. Perfect for ages 7-19 during our holiday code camps.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Scratch Programming</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Fun and interactive programming for children aged 3-12. Build games, animations, and stories while learning coding fundamentals.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 rounded-xl hover:shadow-xl transition-all transform hover:-translate-y-2">
                        <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Python & Advanced</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Learn Python and advanced programming for teens and adults aged 13+. Build real-world projects and applications.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        How It Works
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Get started in three simple steps
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Sign Up</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Create your free account in seconds. Choose your role as a student or instructor and start your journey.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Enroll & Learn</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Browse our extensive catalog of courses, enroll in ones that interest you, and start learning with interactive content.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="w-20 h-20 bg-pink-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Earn & Achieve</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Complete courses, earn points and badges, climb the leaderboard, and receive certificates to showcase your skills.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                            Why Choose Code Academy Uganda?
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                            We're dedicated to bridging the digital divide by providing accessible, high-quality tech education 
                            to empower Ugandans with the skills needed to thrive in the global digital economy.
                        </p>
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Accredited ICDL Center</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Earn globally recognized ICDL certifications. We're an accredited testing center for the International Computer Driving License.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Holiday Code Camps</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Join our structured code camps during holidays. Learn AI, coding, robotics, and STEM in hands-on sessions from 9am-1pm.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Age-Appropriate Programs</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Specialized programs for different age groups: Scratch for kids 3-12, Python/Web for teens 13+, and professional courses for all ages.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Based in Kampala</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Located in Mpererwe, Mugalu Zone, Kampala. Serving the Ugandan community with accessible, high-quality tech education.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-8 shadow-2xl">
                            <div class="space-y-6">
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                            <span class="text-2xl">🎯</span>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-lg">Daily Challenges</h4>
                                            <p class="text-white/80 text-sm">Complete challenges and earn bonus points!</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                            <span class="text-2xl">🏆</span>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-lg">Achievement Badges</h4>
                                            <p class="text-white/80 text-sm">Unlock badges for milestones and accomplishments</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                            <span class="text-2xl">📊</span>
                                        </div>
                                        <div>
                                            <h4 class="text-white font-bold text-lg">Progress Analytics</h4>
                                            <p class="text-white/80 text-sm">Track your learning journey with detailed insights</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-600 to-purple-600">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Ready to Join Our Code Camps?
                </h2>
                <p class="text-xl text-white/90 mb-8">
                    Join Code Academy Uganda's e-learning platform. Enroll in our holiday code camps, 
                    earn ICDL certifications, and master digital skills. Start your journey today!
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                        Go to Dashboard
                    </a>
                @else
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                            Create Free Account
                        </a>
                        <a href="{{ route('login') }}" class="inline-block px-8 py-4 bg-white/10 backdrop-blur-sm text-white border-2 border-white rounded-lg font-semibold text-lg hover:bg-white/20 transition-all">
                            Sign In
                        </a>
                    </div>
                @endauth
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-white font-bold text-xl mb-4">Code Academy Uganda</h3>
                        <p class="text-sm mb-4">
                            Empowering Ugandans with digital skills through comprehensive tech education and code camps.
                        </p>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-400">📍 Mpererwe, Mugalu Zone, Kampala</p>
                            <p class="text-gray-400">📧 info@codeacademy.ug</p>
                            <p class="text-gray-400">📞 +256 784 781926</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Platform</h4>
                        <ul class="space-y-2 text-sm">
                            @auth
                                <li><a href="{{ route('courses.index') }}" class="hover:text-white transition-colors">Courses</a></li>
                                <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a></li>
                            @else
                                <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Sign Up</a></li>
                                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a></li>
                            @endauth
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Programs</h4>
                        <ul class="space-y-2 text-sm">
                            <li><span class="hover:text-white transition-colors cursor-default">Holiday Code Camps</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Web Development</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Mobile App Development</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">ICDL Certification</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Robotics & STEM</span></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Contact & Support</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="https://codeacademyug.org" target="_blank" class="hover:text-white transition-colors">Official Website</a></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Help Center</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Privacy Policy</span></li>
                            <li><span class="hover:text-white transition-colors cursor-default">Terms of Service</span></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm">
                    <p class="mb-2">&copy; {{ date('Y') }} Code Academy Uganda. All rights reserved.</p>
                    <p class="text-gray-500">Premier computer training institution in Kampala | Accredited ICDL Testing Center</p>
                </div>
            </div>
        </footer>
    </body>
</html>
