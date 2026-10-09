@php
    $input = 'mt-2 w-full rounded-lg border border-gray-300 dark:border-blue-700 bg-white dark:bg-blue-950 px-4 py-3 text-gray-900 dark:text-white focus:border-orange-500 focus:ring-orange-500';
    $label = 'block text-sm font-semibold text-gray-700 dark:text-gray-200';
    $error = fn ($field) => $errors->first($field);
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Code Camp Membership - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.analytics.head')
    </head>
    <body class="bg-orange-50 dark:bg-blue-950">
        @include('partials.analytics.body')
        <div class="min-h-screen px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="mb-8 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Code Camp</p>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 dark:text-white mt-3">Membership application</h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        Register your child as a Code Camp member so they can join camps without re-registering each time.
                    </p>
                </div>

                <ol class="mb-8 grid grid-cols-3 gap-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 sm:text-sm">
                    <li class="rounded-xl bg-orange-600 px-3 py-2 text-white">1. Fill in the form</li>
                    <li class="rounded-xl bg-white px-3 py-2 dark:bg-blue-900">2. Pay UGX {{ number_format($fee) }}</li>
                    <li class="rounded-xl bg-white px-3 py-2 dark:bg-blue-900">3. We confirm by email</li>
                </ol>

                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please fix the highlighted fields below.
                    </div>
                @endif

                <form method="POST" action="{{ route('registration.membership.store') }}" class="space-y-6">
                    @csrf

                    <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-6 sm:p-8 space-y-6">
                        <h2 class="text-xl font-bold text-blue-900 dark:text-white">About the child</h2>
                        <div>
                            <label class="{{ $label }}" for="child_name">Child's full name</label>
                            <input id="child_name" type="text" name="child_name" value="{{ old('child_name') }}" required class="{{ $input }}">
                            @if($error('child_name'))<p class="text-sm text-red-600 mt-2">{{ $error('child_name') }}</p>@endif
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $label }}" for="date_of_birth">Date of birth</label>
                                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subDay()->toDateString() }}" required class="{{ $input }}">
                                @if($error('date_of_birth'))<p class="text-sm text-red-600 mt-2">{{ $error('date_of_birth') }}</p>@endif
                            </div>
                            <div>
                                <label class="{{ $label }}" for="gender">Gender</label>
                                <select id="gender" name="gender" required class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach(['Male', 'Female'] as $option)
                                        <option value="{{ $option }}" @selected(old('gender') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @if($error('gender'))<p class="text-sm text-red-600 mt-2">{{ $error('gender') }}</p>@endif
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $label }}" for="school">School</label>
                                <input id="school" type="text" name="school" value="{{ old('school') }}" required class="{{ $input }}">
                                @if($error('school'))<p class="text-sm text-red-600 mt-2">{{ $error('school') }}</p>@endif
                            </div>
                            <div>
                                <label class="{{ $label }}" for="class_grade">Class</label>
                                <input id="class_grade" type="text" name="class_grade" value="{{ old('class_grade') }}" placeholder="e.g. P.6 or S.2" required class="{{ $input }}">
                                @if($error('class_grade'))<p class="text-sm text-red-600 mt-2">{{ $error('class_grade') }}</p>@endif
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label class="{{ $label }}" for="camp_id">Camp to attend first</label>
                                <select id="camp_id" name="camp_id" class="{{ $input }}">
                                    <option value="">Next available camp</option>
                                    @foreach($camps as $camp)
                                        <option value="{{ $camp->id }}" @selected((string) old('camp_id') === (string) $camp->id)>{{ $camp->name }} ({{ $camp->date_range }})</option>
                                    @endforeach
                                </select>
                                @if($error('camp_id'))<p class="text-sm text-red-600 mt-2">{{ $error('camp_id') }}</p>@endif
                            </div>
                            <div>
                                <label class="{{ $label }}" for="tshirt_size">T-shirt size</label>
                                <select id="tshirt_size" name="tshirt_size" class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size }}" @selected(old('tshirt_size') === $size)>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}" for="course_interest">Course interest</label>
                            <select id="course_interest" name="course_interest" class="{{ $input }}">
                                <option value="">Select a course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course }}" @selected(old('course_interest') === $course)>{{ $course }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}" for="medical_notes">Medical conditions, allergies or special needs <span class="font-normal text-gray-500">(optional)</span></label>
                            <textarea id="medical_notes" name="medical_notes" rows="3" class="{{ $input }}">{{ old('medical_notes') }}</textarea>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-6 sm:p-8 space-y-6">
                        <h2 class="text-xl font-bold text-blue-900 dark:text-white">Parent or guardian</h2>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label class="{{ $label }}" for="parent_name">Full name</label>
                                <input id="parent_name" type="text" name="parent_name" value="{{ old('parent_name') }}" required class="{{ $input }}">
                                @if($error('parent_name'))<p class="text-sm text-red-600 mt-2">{{ $error('parent_name') }}</p>@endif
                            </div>
                            <div>
                                <label class="{{ $label }}" for="parent_relationship">Relationship</label>
                                <select id="parent_relationship" name="parent_relationship" required class="{{ $input }}">
                                    @foreach(['Mother', 'Father', 'Guardian'] as $option)
                                        <option value="{{ $option }}" @selected(old('parent_relationship') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $label }}" for="email">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="{{ $input }}">
                                <p class="mt-1 text-xs text-gray-500">We'll send the application and payment receipt here.</p>
                                @if($error('email'))<p class="text-sm text-red-600 mt-2">{{ $error('email') }}</p>@endif
                            </div>
                            <div>
                                <label class="{{ $label }}" for="phone">Phone number</label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="07XX XXX XXX" required class="{{ $input }}">
                                @if($error('phone'))<p class="text-sm text-red-600 mt-2">{{ $error('phone') }}</p>@endif
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="{{ $label }}" for="alt_phone">Other phone <span class="font-normal text-gray-500">(optional)</span></label>
                                <input id="alt_phone" type="tel" name="alt_phone" value="{{ old('alt_phone') }}" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="how_heard">How did you hear about us? <span class="font-normal text-gray-500">(optional)</span></label>
                                <input id="how_heard" type="text" name="how_heard" value="{{ old('how_heard') }}" class="{{ $input }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}" for="address">Home address <span class="font-normal text-gray-500">(optional)</span></label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" class="{{ $input }}">
                        </div>
                    </div>

                    <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 p-6 sm:p-8 space-y-4">
                        <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span>The details above are correct, and I agree to Code Academy Uganda's camp terms and to be contacted about my child's membership.</span>
                        </label>
                        @if($error('consent'))<p class="text-sm text-red-600">{{ $error('consent') }}</p>@endif

                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all">
                            Submit and continue to payment
                        </button>
                        <p class="text-center text-xs text-gray-500 dark:text-gray-400">Application fee: UGX {{ number_format($fee) }}, paid on the next screen with Mobile Money or card.</p>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
