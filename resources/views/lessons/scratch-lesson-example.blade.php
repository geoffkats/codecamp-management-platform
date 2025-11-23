<x-layouts.app>
    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-pink-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
        {{-- Hero Section --}}
        <div class="bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-6 py-12">
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('courses.learn', $lesson->course_id) }}" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <span class="text-white/80 text-sm">Back to Course</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold mb-4">🎨 Drawing Shapes with the Pen Tool</h1>
                <p class="text-xl text-white/90 mb-6">Learn how to create amazing drawings using Scratch's Pen extension!</p>
                
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">30 minutes</span>
                    </div>
                    
                    <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-semibold">Beginner</span>
                    </div>

                    <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span class="font-semibold">1,234 students</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Learning Objectives --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">What You'll Learn</h2>
                        </div>
                        
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">How to add and use the Pen extension in Scratch</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Draw basic shapes like squares, triangles, and circles</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Use repeat loops to create patterns</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Change pen colors and sizes to make creative drawings</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Scratch Project Preview --}}
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">🎮 Try the Final Project</h2>
                        <x-scratch-embed projectId="10128067" title="Drawing Shapes - Final Project" />
                    </div>

                    {{-- Step-by-Step Instructions --}}
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">📚 Step-by-Step Instructions</h2>
                        
                        <x-lesson-step number="1" title="Add the Pen Extension">
                            <p class="mb-4">First, we need to add the Pen extension to our Scratch project. This gives us special blocks for drawing!</p>
                            
                            <ol class="list-decimal list-inside space-y-2 mb-4">
                                <li>Click the <strong>Extensions</strong> button (bottom left corner)</li>
                                <li>Select the <strong>Pen</strong> extension</li>
                                <li>You'll see new green Pen blocks appear!</li>
                            </ol>

                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    💡 <strong>Tip:</strong> The Pen extension lets your sprite draw lines as it moves around the stage!
                                </p>
                            </div>
                        </x-lesson-step>

                        <x-lesson-step number="2" title="Set Up Your Sprite" tryItUrl="https://scratch.mit.edu/projects/10128067">
                            <p class="mb-4">Let's prepare our sprite to start drawing. We'll use these blocks:</p>
                            
                            <div class="space-y-2 my-4">
                                <x-scratch-block type="events" text="when green flag clicked" />
                                <x-scratch-block type="motion" text="go to x: 0 y: 0" />
                                <x-scratch-block type="looks" text="pen down" />
                                <x-scratch-block type="looks" text="set pen color to blue" />
                                <x-scratch-block type="looks" text="set pen size to 3" />
                            </div>

                            <p class="mt-4">This code will:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Start when you click the green flag</li>
                                <li>Move the sprite to the center of the stage</li>
                                <li>Put the pen down (ready to draw)</li>
                                <li>Set the pen color to blue</li>
                                <li>Make the pen line 3 pixels thick</li>
                            </ul>
                        </x-lesson-step>

                        <x-lesson-step number="3" title="Draw a Square">
                            <p class="mb-4">Now let's draw a square! A square has 4 equal sides and 4 right angles (90 degrees).</p>
                            
                            <div class="space-y-2 my-4">
                                <x-scratch-block type="control" text="repeat 4" />
                                <div class="ml-8 space-y-2">
                                    <x-scratch-block type="motion" text="move 100 steps" />
                                    <x-scratch-block type="motion" text="turn right 90 degrees" />
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 p-4 rounded mt-4">
                                <p class="text-sm text-purple-800 dark:text-purple-200">
                                    🎯 <strong>Challenge:</strong> Try changing the number of steps to make bigger or smaller squares!
                                </p>
                            </div>
                        </x-lesson-step>

                        <x-lesson-step number="4" title="Draw a Triangle">
                            <p class="mb-4">A triangle has 3 sides. To draw it, we need to turn 120 degrees at each corner!</p>
                            
                            <div class="space-y-2 my-4">
                                <x-scratch-block type="control" text="repeat 3" />
                                <div class="ml-8 space-y-2">
                                    <x-scratch-block type="motion" text="move 100 steps" />
                                    <x-scratch-block type="motion" text="turn right 120 degrees" />
                                </div>
                            </div>

                            <p class="mt-4">Why 120 degrees? Because 360 ÷ 3 = 120! 🤓</p>
                        </x-lesson-step>

                        <x-lesson-step number="5" title="Create Colorful Patterns">
                            <p class="mb-4">Let's make it more interesting by changing colors as we draw!</p>
                            
                            <div class="space-y-2 my-4">
                                <x-scratch-block type="control" text="repeat 36" />
                                <div class="ml-8 space-y-2">
                                    <x-scratch-block type="looks" text="change pen color by 10" />
                                    <x-scratch-block type="motion" text="move 100 steps" />
                                    <x-scratch-block type="motion" text="turn right 170 degrees" />
                                </div>
                            </div>

                            <p class="mt-4">This creates a beautiful spiral pattern with rainbow colors! 🌈</p>
                        </x-lesson-step>
                    </div>

                    {{-- Practice Challenge --}}
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl shadow-lg p-8 text-white">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <h2 class="text-2xl font-bold">Your Challenge!</h2>
                        </div>
                        
                        <p class="text-lg mb-6">Now it's your turn to create something amazing! Try these challenges:</p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                                <p class="font-semibold mb-1">🟢 Easy: Draw a hexagon (6 sides)</p>
                                <p class="text-sm text-white/90">Hint: 360 ÷ 6 = 60 degrees</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                                <p class="font-semibold mb-1">🟡 Medium: Create a star pattern</p>
                                <p class="text-sm text-white/90">Hint: Use repeat 5 and turn 144 degrees</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                                <p class="font-semibold mb-1">🔴 Hard: Make a spiral that changes size</p>
                                <p class="text-sm text-white/90">Hint: Use a variable to increase the move distance each time</p>
                            </div>
                        </div>

                        <a href="https://scratch.mit.edu/projects/10128067/remix" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-8 py-4 bg-white text-green-600 font-bold rounded-xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Start Your Project!
                        </a>
                    </div>

                    {{-- Reflection --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">💭 Reflection</h2>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">Think about what you learned today:</p>
                        
                        <div class="space-y-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="font-semibold text-gray-900 dark:text-white mb-2">What was the most interesting thing you learned?</p>
                                <textarea class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" rows="3" placeholder="Share your thoughts..."></textarea>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="font-semibold text-gray-900 dark:text-white mb-2">What would you like to create next?</p>
                                <textarea class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" rows="3" placeholder="Describe your ideas..."></textarea>
                            </div>

                            <button class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all">
                                Submit Reflection
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Progress Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Your Progress</h3>
                        
                        <div class="mb-6">
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span>Lesson Progress</span>
                                <span class="font-bold text-purple-600 dark:text-purple-400">60%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: 60%"></div>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Watched video</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Completed 3 of 5 steps</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Submit challenge</span>
                            </div>
                        </div>

                        <button class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all">
                            Mark as Complete
                        </button>
                    </div>

                    {{-- Next Lesson --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Up Next</h3>
                        <div class="space-y-3">
                            <a href="#" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Animating Sprites</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">25 minutes</p>
                            </a>
                            <a href="#" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Adding Sound Effects</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">20 minutes</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
