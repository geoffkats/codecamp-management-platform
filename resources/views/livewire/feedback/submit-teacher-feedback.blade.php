<div class="p-6">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Submit Teacher Feedback</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Share your experience with your teachers. Your feedback helps improve the learning experience.</p>
        </div>

        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        {{-- Feedback Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <form wire:submit="submit" class="space-y-6">
                {{-- Teacher Selection --}}
                <div>
                    <flux:label>Select Teacher *</flux:label>
                    <select wire:model.live="teacher_id" class="w-full mt-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" required>
                        <option value="">Choose a teacher...</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Course Selection (Optional) --}}
                @if($teacher_id && $courses->count() > 0)
                    <div>
                        <flux:label>Related Course (Optional)</flux:label>
                        <select wire:model="course_id" class="w-full mt-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">General feedback (not course-specific)</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Category --}}
                <div>
                    <flux:label>Feedback Category *</flux:label>
                    <select wire:model="category" class="w-full mt-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" required>
                        <option value="general">General Feedback</option>
                        <option value="teaching_quality">Teaching Quality</option>
                        <option value="communication">Communication</option>
                        <option value="support">Student Support</option>
                        <option value="professionalism">Professionalism</option>
                    </select>
                </div>

                {{-- Rating --}}
                <div>
                    <flux:label>Rating (Optional)</flux:label>
                    <div class="flex gap-2 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('rating', {{ $i }})" class="text-3xl transition-colors {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400">
                                ★
                            </button>
                        @endfor
                        @if($rating)
                            <button type="button" wire:click="$set('rating', null)" class="ml-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Clear</button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">1 = Poor, 5 = Excellent</p>
                </div>

                {{-- Feedback Text --}}
                <div>
                    <flux:label>Your Feedback *</flux:label>
                    <textarea wire:model="feedback" rows="6" class="w-full mt-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Share your experience, suggestions, or concerns..." required></textarea>
                    @error('feedback') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 10 characters, maximum 1000 characters</p>
                </div>

                {{-- Anonymous Option --}}
                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_anonymous" id="anonymous" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="anonymous" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Submit anonymously (Your name will not be shown to the teacher)
                    </label>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p class="font-semibold mb-1">Your feedback matters!</p>
                            <p>Your feedback will be reviewed by administrators and used to improve teaching quality. Be honest and constructive.</p>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" wire:navigate>
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
