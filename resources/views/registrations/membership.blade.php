@php
    $input = 'mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900 dark:focus:ring-orange-500/20';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-200';
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Code Camp Membership - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.analytics.head')
    </head>
    <body class="min-h-full bg-white text-slate-900 dark:bg-slate-950 dark:text-white">
        @include('partials.analytics.body')
        <div class="lg:grid lg:min-h-screen lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <aside class="relative overflow-hidden bg-slate-950 px-6 py-10 text-white sm:px-10 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:justify-between lg:px-12 lg:py-12">
                <div class="pointer-events-none absolute -right-16 -top-20 size-72 rounded-full bg-orange-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-10 size-72 rounded-full bg-blue-500/20 blur-3xl"></div>

                <div class="relative">
                    <a href="{{ route('home') }}" class="text-sm font-semibold tracking-wide text-orange-200 hover:text-white">Code Academy Uganda</a>
                    <p class="mt-10 text-xs font-bold uppercase tracking-[0.2em] text-orange-300">Code Camp</p>
                    <h1 class="mt-3 max-w-md text-4xl font-extrabold leading-tight sm:text-5xl">Membership application</h1>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-slate-300">
                        Register your child once. After this form, our team contacts you to confirm the place. There is no online payment on this page.
                    </p>
                </div>

                <ul class="relative mt-10 space-y-4 text-sm text-slate-200 lg:mt-0">
                    @foreach([
                        'One form for the child and the parent or guardian',
                        'Tell us the first camp and the course they want',
                        'We email you a copy and follow up by phone',
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">{{ $loop->iteration }}</span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <main class="px-4 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                <form method="POST" action="{{ route('registration.membership.store') }}" class="mx-auto max-w-2xl space-y-8">
                    @csrf

                    @if($errors->any())
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            Please fix the highlighted fields below.
                        </div>
                    @endif

                    <section class="space-y-5">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">About the child</h2>
                            <p class="text-sm text-slate-500">The student who will attend camp.</p>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="child_name">Child's full name</label>
                            <input id="child_name" type="text" name="child_name" value="{{ old('child_name') }}" required autocomplete="name" class="{{ $input }}">
                            @error('child_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="date_of_birth">Date of birth</label>
                                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subDay()->toDateString() }}" required class="{{ $input }}">
                                @error('date_of_birth')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="gender">Gender</label>
                                <select id="gender" name="gender" required class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach(['Male', 'Female'] as $option)
                                        <option value="{{ $option }}" @selected(old('gender') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('gender')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="school">School</label>
                                <input id="school" type="text" name="school" value="{{ old('school') }}" required class="{{ $input }}">
                                @error('school')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="class_grade">Class</label>
                                <input id="class_grade" type="text" name="class_grade" value="{{ old('class_grade') }}" placeholder="e.g. P.6 or S.2" required class="{{ $input }}">
                                @error('class_grade')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="camp_id">First camp</label>
                                <select id="camp_id" name="camp_id" class="{{ $input }}">
                                    <option value="">Next available camp</option>
                                    @foreach($camps as $camp)
                                        <option value="{{ $camp->id }}" @selected((string) old('camp_id') === (string) $camp->id)>{{ $camp->name }} ({{ $camp->date_range }})</option>
                                    @endforeach
                                </select>
                                @error('camp_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
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
                            <label class="{{ $label }}" for="medical_notes">Medical conditions, allergies or special needs <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea id="medical_notes" name="medical_notes" rows="3" class="{{ $input }}">{{ old('medical_notes') }}</textarea>
                        </div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8 dark:border-slate-800">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Parent or guardian</h2>
                            <p class="text-sm text-slate-500">Who we should call and email.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="parent_name">Full name</label>
                                <input id="parent_name" type="text" name="parent_name" value="{{ old('parent_name') }}" required class="{{ $input }}">
                                @error('parent_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="parent_relationship">Relationship</label>
                                <select id="parent_relationship" name="parent_relationship" required class="{{ $input }}">
                                    @foreach(['Mother', 'Father', 'Guardian'] as $option)
                                        <option value="{{ $option }}" @selected(old('parent_relationship', 'Mother') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="email">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $input }}">
                                <p class="mt-1 text-xs text-slate-500">We send a copy of this application here.</p>
                                @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="phone">Phone number</label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="07XX XXX XXX" required autocomplete="tel" class="{{ $input }}">
                                @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="alt_phone">Other phone <span class="font-normal text-slate-400">(optional)</span></label>
                                <input id="alt_phone" type="tel" name="alt_phone" value="{{ old('alt_phone') }}" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="how_heard">How did you hear about us? <span class="font-normal text-slate-400">(optional)</span></label>
                                <input id="how_heard" type="text" name="how_heard" value="{{ old('how_heard') }}" class="{{ $input }}">
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="address">Home address <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" class="{{ $input }}">
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-slate-200 pt-8 dark:border-slate-800">
                        <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                            <span>The details above are correct, and I agree to Code Academy Uganda's camp terms and to be contacted about my child's membership.</span>
                        </label>
                        @error('consent')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-orange-600 px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-orange-700">
                            Submit application
                        </button>
                        <p class="text-center text-xs text-slate-500">We will email you a copy. Our team follows up by phone. This form does not take a payment.</p>
                    </section>
                </form>
            </main>
        </div>
    </body>
</html>
