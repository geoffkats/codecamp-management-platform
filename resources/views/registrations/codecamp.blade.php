<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CodeCamp Registration - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Zoho PageSense analytics --}}
        <script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js"></script>
        @include('partials.analytics.head')
    </head>
    <body class="bg-orange-50 dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="mb-10 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">CodeCamp</p>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mt-3">Student & Individual Registration</h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        Complete this form to register your interest. This does not create platform access yet. Our team will contact you to complete enrollment.
                    </p>
                </div>

                <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-8">
                    <form method="POST" action="{{ route('registration.codecamp.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                            @error('full_name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('email')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('phone')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Course Interest</label>
                                <select name="course_interest" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select a course</option>
                                    <option value="Full-Stack Development" @selected(old('course_interest') === 'Full-Stack Development')>Full-Stack Development</option>
                                    <option value="Web Development Fundamentals" @selected(old('course_interest') === 'Web Development Fundamentals')>Web Development Fundamentals</option>
                                    <option value="Mobile App Development" @selected(old('course_interest') === 'Mobile App Development')>Mobile App Development</option>
                                    <option value="Python Programming" @selected(old('course_interest') === 'Python Programming')>Python Programming</option>
                                    <option value="Database Design & SQL" @selected(old('course_interest') === 'Database Design & SQL')>Database Design & SQL</option>
                                </select>
                                @error('course_interest')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Preferred Schedule</label>
                                <select name="preferred_schedule" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select a schedule</option>
                                    <option value="Morning" @selected(old('preferred_schedule') === 'Morning')>Morning</option>
                                    <option value="Evening" @selected(old('preferred_schedule') === 'Evening')>Evening</option>
                                    <option value="Weekend" @selected(old('preferred_schedule') === 'Weekend')>Weekend</option>
                                    <option value="Flexible" @selected(old('preferred_schedule') === 'Flexible')>Flexible</option>
                                </select>
                                @error('preferred_schedule')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Message (Optional)</label>
                            <textarea name="message" rows="4" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                            Submit Registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
