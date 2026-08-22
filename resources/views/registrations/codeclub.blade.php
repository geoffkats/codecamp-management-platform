<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CodeClub Registration - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.analytics.head')
    </head>
    <body class="bg-orange-50 dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="mb-10 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">CodeClub</p>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mt-3">Bring CodeClub to Your School</h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        Schools can register to host CodeClub programs for learners. This form does not create platform access yet.
                    </p>
                </div>

                <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-8">
                    <form method="POST" action="{{ route('registration.codeclub.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">School Name</label>
                            <input type="text" name="organization_name" value="{{ old('organization_name') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                            @error('organization_name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Contact Person</label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('full_name')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Role/Title</label>
                                <input type="text" name="role_title" value="{{ old('role_title') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('role_title')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
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
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">School Level</label>
                                <select name="school_level" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select level</option>
                                    <option value="Primary" @selected(old('school_level') === 'Primary')>Primary</option>
                                    <option value="Secondary" @selected(old('school_level') === 'Secondary')>Secondary</option>
                                    <option value="Tertiary" @selected(old('school_level') === 'Tertiary')>Tertiary</option>
                                    <option value="University" @selected(old('school_level') === 'University')>University</option>
                                    <option value="International" @selected(old('school_level') === 'International')>International</option>
                                    <option value="Other" @selected(old('school_level') === 'Other')>Other</option>
                                </select>
                                @error('school_level')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Approx. Students</label>
                                <input type="number" min="1" name="students_count" value="{{ old('students_count') }}" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                @error('students_count')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Target Age Group</label>
                                <select name="age_group" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select age group</option>
                                    <option value="6-9" @selected(old('age_group') === '6-9')>6-9</option>
                                    <option value="10-12" @selected(old('age_group') === '10-12')>10-12</option>
                                    <option value="13-15" @selected(old('age_group') === '13-15')>13-15</option>
                                    <option value="16-18" @selected(old('age_group') === '16-18')>16-18</option>
                                </select>
                                @error('age_group')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Preferred Schedule</label>
                                <select name="preferred_schedule" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Select a schedule</option>
                                    <option value="After School" @selected(old('preferred_schedule') === 'After School')>After School</option>
                                    <option value="Weekend" @selected(old('preferred_schedule') === 'Weekend')>Weekend</option>
                                    <option value="Holiday Program" @selected(old('preferred_schedule') === 'Holiday Program')>Holiday Program</option>
                                </select>
                                @error('preferred_schedule')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">School Location (Optional)</label>
                            <input type="text" name="location" value="{{ old('location') }}" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                            @error('location')
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
                            Submit School Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
