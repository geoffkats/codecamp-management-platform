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
        <div class="lg:grid lg:min-h-screen lg:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)]">
            <aside class="relative flex min-h-[32rem] flex-col justify-between overflow-hidden text-white lg:sticky lg:top-0 lg:h-screen lg:min-h-screen">
                <img src="{{ asset('images/membership/membership-camp.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-black/45"></div>

                <div class="relative px-6 py-10 sm:px-10 lg:px-12 lg:py-12">
                    <a href="{{ route('home') }}" class="text-sm font-semibold tracking-wide text-orange-200 hover:text-white">Code Academy Uganda</a>
                    <p class="mt-10 text-xs font-bold uppercase tracking-[0.2em] text-orange-300">Code Camp</p>
                    <h1 class="mt-3 max-w-md text-4xl font-extrabold leading-tight sm:text-5xl">Membership application</h1>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-slate-300">
                        Tell us about your child, who looks after them, and who we should call. Our team confirms the place with you after this form.
                    </p>
                </div>

                <ul class="relative mt-10 space-y-4 px-6 pb-10 text-sm text-slate-200 sm:px-10 lg:mt-0 lg:px-12 lg:pb-12">
                    @foreach([
                        'Child details, school and T-shirt size',
                        'Parents, home address and a second contact',
                        'Emergency contact and who may pick the child up',
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">{{ $loop->iteration }}</span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <main class="px-4 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                <form method="POST" action="{{ route('registration.membership.store') }}" enctype="multipart/form-data" class="mx-auto max-w-3xl space-y-8">
                    @csrf

                    @if($errors->any())
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            Please fix the highlighted fields below.
                        </div>
                    @endif

                    <section class="space-y-5">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">About the child</h2>
                            <p class="text-sm text-slate-500">Use the name that appears on school records.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <label class="{{ $label }}" for="first_name">First name</label>
                                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="{{ $input }}">
                                @error('first_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="middle_name">Middle name <span class="font-normal text-slate-400">(optional)</span></label>
                                <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name') }}" autocomplete="additional-name" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="last_name">Last name</label>
                                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="{{ $input }}">
                                @error('last_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
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
                            <div>
                                <label class="{{ $label }}" for="nationality">Nationality</label>
                                <input id="nationality" type="text" name="nationality" value="{{ old('nationality', 'Uganda') }}" required class="{{ $input }}">
                                @error('nationality')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
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

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="tshirt_size">T-shirt size</label>
                                <select id="tshirt_size" name="tshirt_size" required class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size }}" @selected(old('tshirt_size') === $size)>{{ $size }}</option>
                                    @endforeach
                                </select>
                                @error('tshirt_size')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="laptop">Laptop for class</label>
                                <select id="laptop" name="laptop" required class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach($laptops as $option)
                                        <option value="{{ $option }}" @selected(old('laptop') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('laptop')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="experience">Coding experience</label>
                                <select id="experience" name="experience" required class="{{ $input }}">
                                    <option value="">Select</option>
                                    @foreach($experienceLevels as $option)
                                        <option value="{{ $option }}" @selected(old('experience') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('experience')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="medical_notes">Allergies, medical conditions or learning needs <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea id="medical_notes" name="medical_notes" rows="3" class="{{ $input }}" placeholder="Anything our instructors should know before class.">{{ old('medical_notes') }}</textarea>
                        </div>

                        <div class="space-y-4 border-t border-slate-200 pt-6 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">School reports</h3>
                                <p class="text-sm text-slate-500">Photos of the report cards from the last three school terms. JPG, PNG or WEBP, up to 5 MB each.</p>
                            </div>
                            <div class="grid gap-5 sm:grid-cols-3">
                                @foreach([
                                    'term_report_1' => 'Most recent term',
                                    'term_report_2' => 'Previous term',
                                    'term_report_3' => 'Term before that',
                                ] as $name => $caption)
                                    <div>
                                        <label class="{{ $label }}" for="{{ $name }}">{{ $caption }}</label>
                                        <input id="{{ $name }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" required class="{{ $input }} file:mr-3 file:rounded-lg file:border-0 file:bg-orange-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                                        @error($name)<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8 dark:border-slate-800">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Parents or guardians</h2>
                            <p class="text-sm text-slate-500">The first parent receives the application email and our follow-up call.</p>
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
                                    @foreach($relationships as $option)
                                        <option value="{{ $option }}" @selected(old('parent_relationship', 'Mother') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="email">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $input }}">
                                @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="phone">Phone number</label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="07XX XXX XXX" required autocomplete="tel" class="{{ $input }}">
                                @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="parent_occupation">Occupation <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="parent_occupation" type="text" name="parent_occupation" value="{{ old('parent_occupation') }}" class="{{ $input }}">
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800 sm:p-5">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Second parent or guardian <span class="font-normal text-slate-400">(optional)</span></p>
                            <div class="mt-4 grid gap-5 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="{{ $label }}" for="parent2_name">Full name</label>
                                    <input id="parent2_name" type="text" name="parent2_name" value="{{ old('parent2_name') }}" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}" for="parent2_relationship">Relationship</label>
                                    <select id="parent2_relationship" name="parent2_relationship" class="{{ $input }}">
                                        <option value="">Select</option>
                                        @foreach($relationships as $option)
                                            <option value="{{ $option }}" @selected(old('parent2_relationship') === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="{{ $label }}" for="parent2_phone">Phone number</label>
                                    <input id="parent2_phone" type="tel" name="parent2_phone" value="{{ old('parent2_phone') }}" class="{{ $input }}">
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="address">Home address</label>
                                <input id="address" type="text" name="address" value="{{ old('address') }}" required class="{{ $input }}">
                                @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="area">Area or neighbourhood</label>
                                <input id="area" type="text" name="area" value="{{ old('area') }}" placeholder="e.g. Ntinda, Kampala" required class="{{ $input }}">
                                @error('area')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="how_heard">How did you hear about us? <span class="font-normal text-slate-400">(optional)</span></label>
                            <select id="how_heard" name="how_heard" class="{{ $input }}">
                                <option value="">Select</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" @selected(old('how_heard') === $source)>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8 dark:border-slate-800">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Safety and pickup</h2>
                            <p class="text-sm text-slate-500">Used if we cannot reach the parent above, and at the end of the day.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="emergency_name">Emergency contact name</label>
                                <input id="emergency_name" type="text" name="emergency_name" value="{{ old('emergency_name') }}" required class="{{ $input }}">
                                @error('emergency_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $label }}" for="emergency_phone">Emergency contact phone</label>
                                <input id="emergency_phone" type="tel" name="emergency_phone" value="{{ old('emergency_phone') }}" required class="{{ $input }}">
                                @error('emergency_phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="pickup_person">Who may pick the child up</label>
                            <input id="pickup_person" type="text" name="pickup_person" value="{{ old('pickup_person') }}" placeholder="Name and relationship, or the parent themselves" required class="{{ $input }}">
                            @error('pickup_person')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="photo_consent" value="1" @checked(old('photo_consent')) class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                            <span>Code Academy may take photos or short videos of my child during camp for parent updates and the academy's channels.</span>
                        </label>

                        <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="consent" value="1" @checked(old('consent')) class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                            <span>The details above are correct, and I agree to Code Academy Uganda's camp terms and to be contacted about my child's membership.</span>
                        </label>
                        @error('consent')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-orange-600 px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-orange-700">
                            Submit application
                        </button>
                        <p class="text-center text-xs text-slate-500">We email a copy to the parent address above and follow up by phone.</p>
                    </section>
                </form>
            </main>
        </div>
    </body>
</html>
