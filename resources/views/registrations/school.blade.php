<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>School Partnership Registration - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Zoho PageSense analytics --}}
        <script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js"></script>
        @include('partials.analytics.head')
    </head>
    <body class="bg-white dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-10 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">ICT Schools Program</p>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mt-3">Register Your School</h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        Submit your school details and our partnerships team will follow up. This form does not create platform access yet.
                    </p>
                </div>

                <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-blue-200 dark:border-blue-700 p-8">
                    <form method="POST" action="{{ route('registration.school.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">School Name</label>
                            <input type="text" name="organization_name" value="{{ old('organization_name') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                            @error('organization_name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Contact Person</label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                @error('full_name')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Role/Title</label>
                                <input type="text" name="role_title" value="{{ old('role_title') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                @error('role_title')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                @error('email')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                @error('phone')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">School Level</label>
                                <select name="school_level" required class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
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
                                <input type="number" min="1" name="students_count" value="{{ old('students_count') }}" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                @error('students_count')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Program Interest</label>
                            <select name="program_interest" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">
                                <option value="">Select focus</option>
                                <option value="School-Based ICT Program" @selected(old('program_interest') === 'School-Based ICT Program')>School-Based ICT Program</option>
                                <option value="ICDL Certification Pathway" @selected(old('program_interest') === 'ICDL Certification Pathway')>ICDL Certification Pathway</option>
                                <option value="Teacher Training" @selected(old('program_interest') === 'Teacher Training')>Teacher Training</option>
                                <option value="Full Digital Transformation" @selected(old('program_interest') === 'Full Digital Transformation')>Full Digital Transformation</option>
                            </select>
                            @error('program_interest')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Message (Optional)</label>
                            <textarea name="message" rows="4" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-blue-600 focus:ring-blue-600">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all">
                            Submit School Registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
