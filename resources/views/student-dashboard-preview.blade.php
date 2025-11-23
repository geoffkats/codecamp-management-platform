<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Student Dashboard Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto p-6 space-y-8">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-8 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome Back, Student! 👋</h1>
                    <p class="text-blue-100">Continue your coding journey</p>
                </div>
                <div class="flex gap-4">
                    <x-streak-counter :days="7" />
                    <x-xp-display :points="350" size="lg" />
                </div>
            </div>
        </div>

        {{-- Progress Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Course Progress</h3>
                <x-progress-bar :percent="65" label="Scratch Basics" color="orange" />
                <div class="mt-4">
                    <x-progress-bar :percent="40" label="Python Fundamentals" color="blue" />
                </div>
                <div class="mt-4">
                    <x-progress-bar :percent="20" label="Web Development" color="green" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Achievements</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">First Project</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Completed your first Scratch project</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <span class="text-2xl">⚡</span>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">Quick Learner</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Completed 5 lessons in one day</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Subject Icons</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center">
                        <x-subject-icon subject="scratch" size="md" />
                        <p class="text-xs mt-2 text-gray-600 dark:text-gray-400">Scratch</p>
                    </div>
                    <div class="text-center">
                        <x-subject-icon subject="python" size="md" />
                        <p class="text-xs mt-2 text-gray-600 dark:text-gray-400">Python</p>
                    </div>
                    <div class="text-center">
                        <x-subject-icon subject="web" size="md" />
                        <p class="text-xs mt-2 text-gray-600 dark:text-gray-400">Web Dev</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lesson Cards Grid --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Continue Learning</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-lesson-card 
                    title="Make Your Sprite Move"
                    description="Learn how to control sprite movement in Scratch using motion blocks"
                    difficulty="beginner"
                    duration="30"
                    icon="🟦"
                    :progress="75"
                />
                
                <x-lesson-card 
                    title="Python Variables"
                    description="Understand how to store and use data in Python programs"
                    difficulty="beginner"
                    duration="25"
                    icon="🐍"
                    :progress="0"
                />
                
                <x-lesson-card 
                    title="HTML Basics"
                    description="Create your first web page with HTML tags and structure"
                    difficulty="beginner"
                    duration="40"
                    icon="🌐"
                    :progress="100"
                />
            </div>
        </div>

        {{-- Achievement Badges --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Your Badges</h2>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <x-achievement-badge 
                        title="First Steps"
                        description="Complete your first lesson"
                        icon="👣"
                        :earned="true"
                        date="2 days ago"
                    />
                    
                    <x-achievement-badge 
                        title="Scratch Master"
                        description="Complete 10 Scratch lessons"
                        icon="🟦"
                        :earned="true"
                        date="1 week ago"
                    />
                    
                    <x-achievement-badge 
                        title="Code Warrior"
                        description="Write 100 lines of code"
                        icon="⚔️"
                        :earned="false"
                    />
                    
                    <x-achievement-badge 
                        title="Perfect Week"
                        description="Complete lessons 7 days in a row"
                        icon="📅"
                        :earned="false"
                    />
                    
                    <x-achievement-badge 
                        title="Bug Hunter"
                        description="Fix 5 coding errors"
                        icon="🐛"
                        :earned="false"
                    />
                    
                    <x-achievement-badge 
                        title="Team Player"
                        description="Help 3 classmates"
                        icon="🤝"
                        :earned="false"
                    />
                </div>
            </div>
        </div>

        {{-- Code Editors --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Code Editors</h2>
            
            {{-- Python Editor --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Python Editor</h3>
                <x-code-editor 
                    language="python"
                    code="# Python Code Editor
print('Hello, World!')

# Try your own code:
for i in range(5):
    print(f'Number: {i}')"
                    title="Python Practice"
                />
            </div>

            {{-- JavaScript Editor --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">JavaScript Editor</h3>
                <x-code-editor 
                    language="javascript"
                    code="// JavaScript Code Editor
console.log('Hello, JavaScript!');

// Try your own code:
const numbers = [1, 2, 3, 4, 5];
const sum = numbers.reduce((a, b) => a + b, 0);
console.log('Sum:', sum);"
                    title="JavaScript Practice"
                />
            </div>

            {{-- Web Development Editor --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Web Development Editor</h3>
                <x-web-editor 
                    html="<h1>Hello World!</h1>
<p>Welcome to web development!</p>
<button id='myButton'>Click Me</button>"
                    css="body {
  font-family: Arial, sans-serif;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
}

h1 {
  font-size: 3em;
  margin-bottom: 20px;
}

button {
  background: white;
  color: #667eea;
  padding: 15px 30px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.2em;
  transition: transform 0.2s;
}

button:hover {
  transform: scale(1.1);
}"
                    javascript="// Add interactivity
document.addEventListener('DOMContentLoaded', function() {
  const button = document.getElementById('myButton');
  let clicks = 0;
  
  button.addEventListener('click', function() {
    clicks++;
    alert('You clicked ' + clicks + ' times!');
  });
});"
                    title="Web Development Playground"
                />
            </div>
        </div>

        {{-- Visual Components Showcase --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Scratch Lesson Components</h2>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-6">
                {{-- Scratch Blocks --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Scratch Blocks</h3>
                    <div class="flex flex-wrap gap-3">
                        <x-scratch-block type="motion" text="move (10) steps" />
                        <x-scratch-block type="looks" text="say [Hello!] for (2) seconds" />
                        <x-scratch-block type="sound" text="play sound [meow]" />
                        <x-scratch-block type="events" text="when 🏴 clicked" />
                        <x-scratch-block type="control" text="repeat (10)" />
                    </div>
                </div>

                {{-- Interactive Steps --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Interactive Steps</h3>
                    <x-lesson-step 
                        :number="1"
                        title="Add a Sprite"
                        tryItUrl="https://scratch.mit.edu"
                    >
                        <p>Click on the "Choose a Sprite" button and select your favorite character. You can choose from cats, dogs, dinosaurs, and many more!</p>
                    </x-lesson-step>
                    
                    <x-lesson-step 
                        :number="2"
                        title="Drag the Move Block"
                    >
                        <p>Find the blue "move 10 steps" block in the Motion category and drag it to the coding area. This block will make your sprite move across the stage.</p>
                    </x-lesson-step>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
