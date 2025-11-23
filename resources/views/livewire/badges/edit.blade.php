<div class="min-h-screen bg-gradient-to-br from-gray-50 via-yellow-50 to-orange-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 rounded-2xl shadow-2xl p-8 text-white">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl">
                            {{ $badge->icon ?? '🏆' }}
                        </div>
                        <h1 class="text-4xl font-bold">Edit Badge</h1>
                    </div>
                    <p class="text-yellow-100 text-lg">Update badge details and criteria</p>
                </div>
                <flux:button href="{{ route('badges.index') }}" icon="arrow-left" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30" wire:navigate>
                    Back to Badges
                </flux:button>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <form wire:submit="save">
                <div class="space-y-6">
                    {{-- Basic Information --}}
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Basic Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <flux:label for="name" value="Badge Name" />
                                <flux:input id="name" type="text" wire:model="name" placeholder="e.g., Course Completion Master" />
                                <flux:error name="name" />
                            </div>

                            <div class="md:col-span-2">
                                <flux:label for="description" value="Description" />
                                <flux:textarea id="description" wire:model="description" rows="3" placeholder="Describe what this badge represents..." />
                                <flux:error name="description" />
                            </div>

                            <div>
                                <flux:label for="icon" value="Icon (Emoji)" />
                                <flux:input id="icon" type="text" wire:model="icon" placeholder="🏆" maxlength="10" />
                                <flux:error name="icon" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter an emoji or icon character</p>
                            </div>

                            <div>
                                <flux:label for="color" value="Color Theme" />
                                <flux:select id="color" wire:model="color">
                                    <option value="blue">Blue</option>
                                    <option value="green">Green</option>
                                    <option value="yellow">Yellow</option>
                                    <option value="red">Red</option>
                                    <option value="purple">Purple</option>
                                    <option value="orange">Orange</option>
                                    <option value="pink">Pink</option>
                                    <option value="indigo">Indigo</option>
                                </flux:select>
                                <flux:error name="color" />
                            </div>

                            <div>
                                <flux:label for="points_reward" value="Points Reward" />
                                <flux:input id="points_reward" type="number" wire:model="points_reward" min="0" max="10000" />
                                <flux:error name="points_reward" />
                            </div>

                            <div class="flex items-center">
                                <flux:checkbox id="is_active" wire:model="is_active" />
                                <flux:label for="is_active" value="Active Badge" class="ml-2" />
                                <flux:error name="is_active" />
                            </div>
                        </div>
                    </div>

                    {{-- Criteria --}}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Award Criteria
                            </h2>
                            <flux:button type="button" wire:click="addCriteria" size="sm" icon="plus">
                                Add Criterion
                            </flux:button>
                        </div>

                        @if(count($criteria) > 0)
                            <div class="space-y-3">
                                @foreach($criteria as $index => $criterion)
                                    <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex-1 grid grid-cols-2 gap-3">
                                            <div>
                                                <flux:label value="Type" />
                                                <flux:select wire:model="criteria.{{ $index }}.type">
                                                    <option value="complete_course">Complete Course</option>
                                                    <option value="complete_lessons">Complete Lessons</option>
                                                    <option value="earn_points">Earn Points</option>
                                                    <option value="complete_challenges">Complete Challenges</option>
                                                    <option value="earn_badges">Earn Other Badges</option>
                                                </flux:select>
                                            </div>
                                            <div>
                                                <flux:label value="Value" />
                                                <flux:input type="number" wire:model="criteria.{{ $index }}.value" placeholder="e.g., 5" min="0" />
                                            </div>
                                        </div>
                                        <flux:button type="button" wire:click="removeCriteria({{ $index }})" size="sm" variant="ghost" color="red" icon="trash" />
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                                <p class="text-gray-500 dark:text-gray-400">No criteria set. This badge will be manually awarded.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Preview --}}
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </h2>
                        <div class="flex justify-center">
                            @php
                                $colorClasses = [
                                    'blue' => 'from-blue-400 to-blue-600',
                                    'green' => 'from-green-400 to-green-600',
                                    'yellow' => 'from-yellow-400 to-yellow-600',
                                    'red' => 'from-red-400 to-red-600',
                                    'purple' => 'from-purple-400 to-purple-600',
                                    'orange' => 'from-orange-400 to-orange-600',
                                    'pink' => 'from-pink-400 to-pink-600',
                                    'indigo' => 'from-indigo-400 to-indigo-600',
                                ];
                                $gradientClass = $colorClasses[$color] ?? 'from-yellow-400 to-yellow-600';
                            @endphp
                            <div class="relative w-48 h-48 rounded-full bg-gradient-to-br {{ $gradientClass }} flex items-center justify-center shadow-2xl border-4 border-white dark:border-gray-800">
                                <div class="text-6xl">{{ $icon ?? '🏆' }}</div>
                                @if(!$is_active)
                                    <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold">Inactive</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $name ?? 'Badge Name' }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $description ?? 'No description' }}</p>
                            <div class="mt-2">
                                <flux:badge size="lg" color="{{ $color }}">
                                    {{ number_format($points_reward) }} Points
                                </flux:badge>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <flux:button href="{{ route('badges.index') }}" variant="ghost" wire:navigate>
                            Cancel
                        </flux:button>
                        <flux:button type="submit" icon="check">
                            Save Changes
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>
