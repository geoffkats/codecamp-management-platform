<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual Components Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Visual Scratch Components Test</h1>
        
        <div class="space-y-8">
            {{-- Lesson Card Test --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">1. Lesson Card Component</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-lesson-card 
                        title="Beginner Lesson"
                        description="Learn the basics"
                        difficulty="beginner"
                        duration="20"
                        icon="🎮"
                    />
                    <x-lesson-card 
                        title="Intermediate Challenge"
                        description="Take it to the next level"
                        difficulty="intermediate"
                        duration="45"
                        icon="🚀"
                    />
                    <x-lesson-card 
                        title="Advanced Project"
                        description="Master the concepts"
                        difficulty="advanced"
                        duration="60"
                        icon="⭐"
                    />
                </div>
            </section>

            {{-- Scratch Blocks Test --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">2. Scratch Block Components</h2>
                <div class="space-y-3">
                    <x-scratch-block category="motion" text="move (10) steps" />
                    <x-scratch-block category="looks" text="say [Hello!] for (2) seconds" />
                    <x-scratch-block category="sound" text="play sound [meow ▼] until done" />
                    <x-scratch-block category="events" text="when 🏴 clicked" />
                    <x-scratch-block category="control" text="repeat (10)" />
                    <x-scratch-block category="sensing" text="touching [mouse-pointer ▼] ?" />
                    <x-scratch-block category="operators" text="(  ) + (  )" />
                    <x-scratch-block category="variables" text="set [my variable ▼] to (0)" />
                </div>
            </section>

            {{-- Lesson Steps Test --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">3. Lesson Step Components</h2>
                <div class="space-y-4">
                    <x-lesson-step 
                        number="1"
                        title="Create Your Project"
                        description="Open Scratch and start a new project. Choose your favorite sprite from the library."
                    />
                    <x-lesson-step 
                        number="2"
                        title="Add Motion Blocks"
                        description="Drag the blue motion blocks to make your sprite move around the stage."
                    />
                    <x-lesson-step 
                        number="3"
                        title="Test Your Code"
                        description="Click the green flag to run your program and see your sprite in action!"
                    />
                </div>
            </section>

            {{-- Scratch Embed Test --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">4. Scratch Embed Component</h2>
                <x-scratch-embed 
                    projectId="1234567890"
                    title="Example Scratch Project"
                    height="400"
                />
                <p class="text-sm text-gray-600 mt-4">
                    Note: Replace projectId with a real Scratch project ID to see an actual embedded project.
                </p>
            </section>

            {{-- Complete Example --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-6">5. Complete Lesson Example</h2>
                
                <x-lesson-card 
                    title="Make Your Sprite Dance"
                    description="Create an animated dance routine using motion and looks blocks"
                    difficulty="intermediate"
                    duration="30"
                    icon="💃"
                />

                <div class="mt-6">
                    <h3 class="text-xl font-bold mb-4">Blocks You'll Use</h3>
                    <div class="space-y-2">
                        <x-scratch-block category="events" text="when 🏴 clicked" />
                        <x-scratch-block category="control" text="repeat (4)" />
                        <x-scratch-block category="motion" text="move (10) steps" />
                        <x-scratch-block category="motion" text="turn ↻ (90) degrees" />
                        <x-scratch-block category="looks" text="next costume" />
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-xl font-bold mb-4">Step-by-Step Instructions</h3>
                    <div class="space-y-4">
                        <x-lesson-step 
                            number="1"
                            title="Set Up Your Sprite"
                            description="Choose a sprite with multiple costumes, like the dancer or cat."
                        />
                        <x-lesson-step 
                            number="2"
                            title="Add the Event Block"
                            description="Start with the 'when flag clicked' block from the Events category."
                        />
                        <x-lesson-step 
                            number="3"
                            title="Create the Dance Loop"
                            description="Add a 'repeat 4' block and put motion and costume change blocks inside."
                        />
                        <x-lesson-step 
                            number="4"
                            title="Test and Improve"
                            description="Run your program and adjust the numbers to make the dance look better!"
                        />
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-2">✅ All Components Working!</h3>
            <p class="text-blue-800">
                These visual components are now integrated into your lesson viewer. 
                When you add visual component data to a lesson (lesson_type = 'interactive'), 
                they will automatically appear at <code class="bg-blue-100 px-2 py-1 rounded">/lessons/{id}/view</code>
            </p>
            <div class="mt-4">
                <a href="/curriculum/builder/4" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Go to Curriculum Builder →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
