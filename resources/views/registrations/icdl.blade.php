<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ICDL Exam Registration - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.analytics.head')
    </head>
    <body class="bg-orange-50 dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-10 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">ICDL Exam Center</p>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mt-3">ICDL Exam Registration</h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        Register for ICDL testing. This form does not provide immediate platform access. We will confirm your exam schedule.
                    </p>
                </div>

                <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-8">
                    <form method="POST" action="{{ route('registration.icdl.store') }}" class="space-y-6">
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
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">National ID / Passport</label>
                                <input type="text" name="national_id" value="{{ old('national_id') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('national_id')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Date of Birth (Optional)</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('date_of_birth')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Gender (Optional)</label>
                                <select name="gender" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select</option>
                                    <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                                    <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                                </select>
                                @error('gender')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Preferred Exam Date (Optional)</label>
                                <input type="date" name="preferred_exam_date" value="{{ old('preferred_exam_date') }}" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('preferred_exam_date')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">ICDL Modules (Select at least one)</label>
                            <div class="mt-3 grid sm:grid-cols-2 gap-3">
                                @php
                                    $modules = [
                                        'Computer Essentials',
                                        'Online Essentials',
                                        'Word Processing',
                                        'Spreadsheets',
                                        'Presentations',
                                        'IT Security',
                                        'Online Collaboration',
                                    ];
                                @endphp
                                @foreach($modules as $module)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="icdl_modules[]" value="{{ $module }}" @checked(is_array(old('icdl_modules')) && in_array($module, old('icdl_modules', []))) class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                        <span>{{ $module }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('icdl_modules')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Message (Optional)</label>
                            <textarea name="message" rows="4" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                            Submit ICDL Registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
