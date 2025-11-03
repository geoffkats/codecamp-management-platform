<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        })->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found. Please run UserSeeder first.');
            return;
        }

        $courses = $this->getCoursesData();

        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->get();

        foreach ($courses as $courseData) {
            $teacher = $teachers->random();
            $slug = Str::slug($courseData['title']);
            
            $course = Course::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $courseData['title'],
                    'description' => $courseData['description'],
                    'short_description' => $courseData['short_description'],
                    'instructor_id' => $teacher->id,
                    'difficulty_level' => $courseData['difficulty_level'],
                    'estimated_duration' => $courseData['estimated_duration'],
                    'category' => $courseData['category'],
                    'tags' => $courseData['tags'],
                    'requirements' => $courseData['requirements'],
                    'what_you_learn' => $courseData['what_you_learn'],
                    'price' => 0.00,
                    'is_published' => true,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => 1,
                ]
            );

            // Skip if course already exists and has modules
            if (!$course->wasRecentlyCreated && $course->modules()->exists()) {
                continue;
            }

            // Delete existing modules to recreate
            if (!$course->wasRecentlyCreated) {
                $course->modules()->delete();
            }

            $orderIndex = 0;
            foreach ($courseData['modules'] as $moduleData) {
                $module = CourseModule::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'],
                    'order_index' => $orderIndex++,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]);

                $lessonOrder = 0;
                foreach ($moduleData['lessons'] as $lessonData) {
                    $lesson = Lesson::create([
                        'course_id' => $course->id,
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'slug' => Str::slug($lessonData['title']),
                        'content' => $lessonData['content'],
                        'summary' => $lessonData['summary'],
                        'order_index' => $lessonOrder++,
                        'lesson_type' => $lessonData['lesson_type'],
                        'duration_minutes' => $lessonData['duration_minutes'],
                        'objectives' => $lessonData['objectives'],
                        'is_published' => true,
                        'is_active' => true,
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                    ]);
                }
            }

            // Enroll some students in the course
            $enrollmentCount = rand(1, min(3, $students->count()));
            $selectedStudents = $students->random($enrollmentCount);
            
            foreach ($selectedStudents as $student) {
                CourseEnrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => now()->subDays(rand(1, 30)),
                    'progress_percentage' => rand(0, 100),
                    'lessons_completed' => rand(0, $course->lessons()->count()),
                    'quizzes_completed' => rand(0, 3),
                ]);
            }
        }
    }

    /**
     * Get comprehensive course data for all courses
     */
    private function getCoursesData(): array
    {
        return [
            $this->getWebDevelopmentCourse(),
            $this->getMobileAppDevelopmentCourse(),
            $this->getICDLCertificationCourse(),
            $this->getRoboticsSTEMCourse(),
            $this->getScratchBeginnersCourse(),
            $this->getScratchIntermediateCourse(),
            $this->getScratchAdvancedCourse(),
            $this->getPythonAdvancedCourse(),
        ];
    }

    /**
     * Web Development Course
     */
    private function getWebDevelopmentCourse(): array
    {
        return [
            'title' => 'Web Development',
            'description' => 'Learn to build modern, responsive websites and web applications from scratch. Master HTML, CSS, JavaScript, and modern frameworks.',
            'short_description' => 'Build modern, responsive websites and web applications',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 120,
            'category' => 'Web Development',
            'tags' => ['html', 'css', 'javascript', 'responsive', 'web', 'bootstrap', 'react'],
            'requirements' => ['Basic computer skills', 'Willingness to learn', 'Internet connection'],
            'what_you_learn' => [
                'HTML structure and semantic elements',
                'CSS styling and layout techniques',
                'JavaScript fundamentals and DOM manipulation',
                'Responsive web design',
                'Bootstrap framework',
                'Modern JavaScript frameworks',
                'Building complete web applications'
            ],
            'modules' => [
                [
                    'title' => 'HTML Foundations',
                    'description' => 'Master HTML for building website structures and content',
                    'lessons' => [
                        [
                            'title' => 'Introduction to HTML',
                            'content' => '<h2>What is HTML?</h2><p>HTML (HyperText Markup Language) is the standard markup language for creating web pages. It provides the structure and content of a webpage.</p><h3>Key Concepts:</h3><ul><li>HTML document structure</li><li>HTML elements and tags</li><li>Attributes and their usage</li><li>Headings, paragraphs, and text formatting</li><li>Creating your first HTML page</li></ul><h3>Document Structure:</h3><pre>&lt;!DOCTYPE html&gt;\n&lt;html&gt;\n  &lt;head&gt;\n    &lt;title&gt;Page Title&lt;/title&gt;\n  &lt;/head&gt;\n  &lt;body&gt;\n    &lt;h1&gt;Hello World&lt;/h1&gt;\n  &lt;/body&gt;\n&lt;/html&gt;</pre>',
                            'summary' => 'Learn the basics of HTML structure and create your first web page',
                            'lesson_type' => 'text',
                            'duration_minutes' => 45,
                            'objectives' => 'Understand HTML fundamentals, document structure, and create your first HTML page'
                        ],
                        [
                            'title' => 'HTML Semantic Elements',
                            'content' => '<h2>Semantic HTML</h2><p>Semantic HTML uses elements that clearly describe their meaning in a human and machine-readable way. This improves accessibility and SEO.</p><h3>Key Elements:</h3><ul><li>&lt;header&gt; - Header content</li><li>&lt;nav&gt; - Navigation links</li><li>&lt;main&gt; - Main content</li><li>&lt;article&gt; - Independent content</li><li>&lt;section&gt; - Thematic grouping</li><li>&lt;aside&gt; - Sidebar content</li><li>&lt;footer&gt; - Footer content</li></ul><h3>Benefits:</h3><ul><li>Better accessibility for screen readers</li><li>Improved SEO rankings</li><li>Clearer code structure</li><li>Better maintainability</li></ul>',
                            'summary' => 'Learn semantic HTML elements for better structure and accessibility',
                            'lesson_type' => 'video',
                            'duration_minutes' => 50,
                            'objectives' => 'Use semantic HTML elements to create well-structured, accessible web pages'
                        ],
                        [
                            'title' => 'HTML Forms and Inputs',
                            'content' => '<h2>Creating Forms in HTML</h2><p>Forms allow users to input data that can be sent to a server. Understanding form elements is crucial for interactive web development.</p><h3>Form Elements:</h3><ul><li>&lt;input&gt; - Various input types (text, email, password, etc.)</li><li>&lt;textarea&gt; - Multi-line text input</li><li>&lt;select&gt; - Dropdown lists</li><li>&lt;button&gt; - Submit and action buttons</li><li>&lt;label&gt; - Form field labels</li></ul><h3>Input Types:</h3><ul><li>text, email, password, number, date, checkbox, radio</li><li>file upload, color picker, range slider</li></ul><h3>Best Practices:</h3><ul><li>Always use labels for accessibility</li><li>Use appropriate input types</li><li>Implement form validation</li><li>Group related fields with fieldset</li></ul>',
                            'summary' => 'Create interactive forms with various input types and validation',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Build functional HTML forms with proper input types and accessibility features'
                        ],
                    ]
                ],
                [
                    'title' => 'CSS Essentials',
                    'description' => 'Master CSS for styling and creating beautiful, responsive web designs',
                    'lessons' => [
                        [
                            'title' => 'CSS Fundamentals',
                            'content' => '<h2>Introduction to CSS</h2><p>CSS (Cascading Style Sheets) is used to style HTML elements and create visually appealing web pages.</p><h3>Key Topics:</h3><ul><li>CSS syntax and selectors</li><li>Properties and values</li><li>CSS Box Model</li><li>Colors and typography</li><li>Layout techniques</li></ul><h3>Selectors:</h3><ul><li>Element selectors</li><li>Class and ID selectors</li><li>Attribute selectors</li><li>Pseudo-classes and pseudo-elements</li></ul><h3>Box Model:</h3><p>Understanding margin, padding, border, and content areas is crucial for layout design.</p>',
                            'summary' => 'Learn CSS basics for styling web pages effectively',
                            'lesson_type' => 'text',
                            'duration_minutes' => 55,
                            'objectives' => 'Apply CSS styling to HTML elements using various selectors and understand the box model'
                        ],
                        [
                            'title' => 'CSS Flexbox Layout',
                            'content' => '<h2>CSS Flexbox</h2><p>Flexbox is a one-dimensional layout method for arranging items in rows or columns. It makes responsive design much easier.</p><h3>Key Properties:</h3><ul><li>display: flex</li><li>flex-direction</li><li>justify-content</li><li>align-items</li><li>flex-wrap</li><li>gap</li></ul><h3>Use Cases:</h3><ul><li>Navigation bars</li><li>Card layouts</li><li>Centering content</li><li>Responsive grids</li></ul><h3>Example:</h3><pre>.container {\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  gap: 20px;\n}</pre>',
                            'summary' => 'Master Flexbox for modern, flexible layouts',
                            'lesson_type' => 'video',
                            'duration_minutes' => 60,
                            'objectives' => 'Create flexible, responsive layouts using CSS Flexbox'
                        ],
                        [
                            'title' => 'CSS Grid Layout',
                            'content' => '<h2>CSS Grid</h2><p>CSS Grid is a two-dimensional layout system that allows you to create complex layouts with rows and columns.</p><h3>Key Concepts:</h3><ul><li>Grid container and items</li><li>Grid lines and tracks</li><li>Grid areas</li><li>Template columns and rows</li></ul><h3>Grid Properties:</h3><ul><li>display: grid</li><li>grid-template-columns</li><li>grid-template-rows</li><li>grid-gap</li><li>grid-area</li></ul><h3>Advanced Features:</h3><ul><li>Responsive grid layouts</li><li>Grid auto-fit and auto-fill</li><li>Named grid lines</li><li>Nested grids</li></ul>',
                            'summary' => 'Create complex two-dimensional layouts with CSS Grid',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 65,
                            'objectives' => 'Build complex, responsive layouts using CSS Grid system'
                        ],
                        [
                            'title' => 'Responsive Web Design',
                            'content' => '<h2>Responsive Design Principles</h2><p>Responsive web design ensures your website looks good on all devices - from mobile phones to desktop computers.</p><h3>Key Techniques:</h3><ul><li>Mobile-first approach</li><li>Media queries</li><li>Flexible images and media</li><li>Responsive typography</li><li>Breakpoints</li></ul><h3>Media Queries:</h3><pre>@media (max-width: 768px) {\n  .container {\n    flex-direction: column;\n  }\n}</pre><h3>Best Practices:</h3><ul><li>Start with mobile design</li><li>Use relative units (%, em, rem)</li><li>Test on multiple devices</li><li>Optimize images for different screens</li></ul>',
                            'summary' => 'Create websites that work beautifully on all devices',
                            'lesson_type' => 'text',
                            'duration_minutes' => 70,
                            'objectives' => 'Implement responsive design using media queries and flexible layouts'
                        ],
                    ]
                ],
                [
                    'title' => 'JavaScript Basics',
                    'description' => 'Learn JavaScript fundamentals for adding interactivity to web pages',
                    'lessons' => [
                        [
                            'title' => 'Introduction to JavaScript',
                            'content' => '<h2>JavaScript Fundamentals</h2><p>JavaScript is a programming language that adds interactivity to web pages. It is one of the core technologies of the web.</p><h3>Key Concepts:</h3><ul><li>Variables and data types</li><li>Operators and expressions</li><li>Functions</li><li>Conditional statements</li><li>Loops</li></ul><h3>Data Types:</h3><ul><li>String, Number, Boolean</li><li>Object, Array</li><li>undefined, null</li></ul><h3>Example:</h3><pre>let message = "Hello World";\nconsole.log(message);\n\nfunction greet(name) {\n  return "Hello, " + name;\n}</pre>',
                            'summary' => 'Learn JavaScript syntax, variables, and basic programming concepts',
                            'lesson_type' => 'text',
                            'duration_minutes' => 60,
                            'objectives' => 'Understand JavaScript basics including variables, functions, and control flow'
                        ],
                        [
                            'title' => 'DOM Manipulation',
                            'content' => '<h2>Document Object Model (DOM)</h2><p>The DOM represents the HTML document as a tree of objects. JavaScript can manipulate the DOM to change content, structure, and styling.</p><h3>Key Methods:</h3><ul><li>document.getElementById()</li><li>document.querySelector()</li><li>element.innerHTML</li><li>element.style</li><li>element.addEventListener()</li></ul><h3>Common Operations:</h3><ul><li>Selecting elements</li><li>Changing content</li><li>Modifying styles</li><li>Adding/removing elements</li><li>Handling events</li></ul><h3>Example:</h3><pre>const button = document.getElementById("myButton");\nbutton.addEventListener("click", function() {\n  alert("Button clicked!");\n});</pre>',
                            'summary' => 'Learn to manipulate webpage content and structure with JavaScript',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Manipulate DOM elements to create interactive web pages'
                        ],
                        [
                            'title' => 'JavaScript Events',
                            'content' => '<h2>Event Handling in JavaScript</h2><p>Events are actions that happen in the browser, such as clicks, key presses, or page loads. JavaScript can respond to these events.</p><h3>Common Events:</h3><ul><li>click, dblclick</li><li>keydown, keyup</li><li>mouseover, mouseout</li><li>submit, change</li><li>load, resize</li></ul><h3>Event Listeners:</h3><pre>element.addEventListener("click", function(event) {\n  // Handle the event\n});</pre><h3>Event Object:</h3><ul><li>event.target - The element that triggered the event</li><li>event.preventDefault() - Prevent default behavior</li><li>event.stopPropagation() - Stop event bubbling</li></ul>',
                            'summary' => 'Handle user interactions and events in JavaScript',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Implement event handlers to create interactive user experiences'
                        ],
                    ]
                ],
                [
                    'title' => 'Modern Frameworks',
                    'description' => 'Introduction to modern JavaScript frameworks and libraries',
                    'lessons' => [
                        [
                            'title' => 'Introduction to Bootstrap',
                            'content' => '<h2>Bootstrap Framework</h2><p>Bootstrap is a popular CSS framework that provides pre-designed components and utilities for faster web development.</p><h3>Key Features:</h3><ul><li>Responsive grid system</li><li>Pre-styled components (buttons, forms, cards)</li><li>Utility classes</li><li>JavaScript plugins</li></ul><h3>Getting Started:</h3><pre>&lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"&gt;\n&lt;script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;</pre><h3>Components:</h3><ul><li>Navigation bars</li><li>Cards and modals</li><li>Forms and buttons</li><li>Alerts and badges</li></ul>',
                            'summary' => 'Learn to use Bootstrap for rapid, responsive web development',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 65,
                            'objectives' => 'Use Bootstrap components and utilities to build responsive websites quickly'
                        ],
                        [
                            'title' => 'Introduction to React',
                            'content' => '<h2>React JavaScript Library</h2><p>React is a JavaScript library for building user interfaces, particularly single-page applications.</p><h3>Key Concepts:</h3><ul><li>Components</li><li>Props and State</li><li>JSX syntax</li><li>Virtual DOM</li><li>Hooks</li></ul><h3>Component Example:</h3><pre>function Welcome(props) {\n  return &lt;h1&gt;Hello, {props.name}&lt;/h1&gt;;\n}</pre><h3>Benefits:</h3><ul><li>Reusable components</li><li>Efficient rendering</li><li>Large ecosystem</li><li>Strong community support</li></ul>',
                            'summary' => 'Get started with React for building modern web applications',
                            'lesson_type' => 'video',
                            'duration_minutes' => 75,
                            'objectives' => 'Understand React fundamentals and create your first React component'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Mobile App Development Course
     */
    private function getMobileAppDevelopmentCourse(): array
    {
        return [
            'title' => 'Mobile App Development',
            'description' => 'Create powerful and intuitive applications for Android and iOS devices. Learn modern mobile development frameworks and tools.',
            'short_description' => 'Build mobile apps for Android and iOS',
            'difficulty_level' => 'Intermediate',
            'estimated_duration' => 150,
            'category' => 'Mobile Development',
            'tags' => ['android', 'ios', 'react-native', 'flutter', 'mobile', 'app'],
            'requirements' => ['Programming basics', 'Understanding of JavaScript/Java', 'Computer with development tools'],
            'what_you_learn' => [
                'Mobile app design principles',
                'React Native development',
                'Flutter framework',
                'App deployment to stores',
                'User interface design',
                'State management',
                'API integration'
            ],
            'modules' => [
                [
                    'title' => 'Introduction to Mobile Apps',
                    'description' => 'Understanding mobile app development fundamentals',
                    'lessons' => [
                        [
                            'title' => 'Mobile App Development Overview',
                            'content' => '<h2>Mobile App Development</h2><p>Mobile apps have become essential in our daily lives. Learn the fundamentals of creating apps for Android and iOS platforms.</p><h3>Platforms:</h3><ul><li>Android - Google\'s mobile OS</li><li>iOS - Apple\'s mobile OS</li><li>Cross-platform solutions</li></ul><h3>App Types:</h3><ul><li>Native apps</li><li>Hybrid apps</li><li>Web apps</li><li>Progressive Web Apps (PWA)</li></ul><h3>Development Approaches:</h3><ul><li>Native development</li><li>React Native</li><li>Flutter</li><li>Ionic</li></ul>',
                            'summary' => 'Understand mobile app development ecosystem and platforms',
                            'lesson_type' => 'text',
                            'duration_minutes' => 40,
                            'objectives' => 'Understand different mobile platforms and development approaches'
                        ],
                        [
                            'title' => 'Mobile UI/UX Design Basics',
                            'content' => '<h2>Mobile Interface Design</h2><p>Creating intuitive and user-friendly interfaces is crucial for mobile app success.</p><h3>Design Principles:</h3><ul><li>Touch-friendly elements</li><li>Consistent navigation</li><li>Clear visual hierarchy</li><li>Responsive layouts</li></ul><h3>Tools:</h3><ul><li>Figma for wireframing</li><li>Adobe XD</li><li>Sketch</li><li>Material Design guidelines</li></ul><h3>Best Practices:</h3><ul><li>Minimalistic design</li><li>Fast load times</li><li>Accessibility features</li><li>Platform-specific guidelines</li></ul>',
                            'summary' => 'Learn mobile UI/UX design principles and tools',
                            'lesson_type' => 'video',
                            'duration_minutes' => 50,
                            'objectives' => 'Design user-friendly mobile interfaces following platform guidelines'
                        ],
                    ]
                ],
                [
                    'title' => 'React Native Development',
                    'description' => 'Build cross-platform mobile apps with React Native',
                    'lessons' => [
                        [
                            'title' => 'Getting Started with React Native',
                            'content' => '<h2>React Native Basics</h2><p>React Native lets you build mobile apps using JavaScript and React, sharing code between iOS and Android.</p><h3>Setup:</h3><ul><li>Node.js and npm</li><li>React Native CLI</li><li>Android Studio / Xcode</li><li>Emulator setup</li></ul><h3>Key Components:</h3><ul><li>View, Text, Image</li><li>ScrollView, FlatList</li><li>Button, TextInput</li><li>Navigation components</li></ul><h3>Example:</h3><pre>import React from \'react\';\nimport { View, Text } from \'react-native\';\n\nexport default function App() {\n  return (\n    &lt;View&gt;\n      &lt;Text&gt;Hello React Native!&lt;/Text&gt;\n    &lt;/View&gt;\n  );\n}</pre>',
                            'summary' => 'Set up React Native development environment and create your first app',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Set up React Native and build your first mobile app'
                        ],
                        [
                            'title' => 'React Native Navigation',
                            'content' => '<h2>App Navigation</h2><p>Navigation is essential for multi-screen mobile apps. Learn how to implement navigation in React Native.</p><h3>Navigation Libraries:</h3><ul><li>React Navigation</li><li>React Native Navigation</li><li>NavigationContainer</li></ul><h3>Navigation Types:</h3><ul><li>Stack Navigation</li><li>Tab Navigation</li><li>Drawer Navigation</li></ul><h3>Implementation:</h3><pre>import { NavigationContainer } from \'@react-navigation/native\';\nimport { createStackNavigator } from \'@react-navigation/stack\';\n\nconst Stack = createStackNavigator();</pre>',
                            'summary' => 'Implement navigation in React Native apps',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Create multi-screen apps with proper navigation'
                        ],
                        [
                            'title' => 'State Management in React Native',
                            'content' => '<h2>Managing App State</h2><p>Efficiently managing state is crucial for React Native applications.</p><h3>State Management:</h3><ul><li>React Hooks (useState, useEffect)</li><li>Context API</li><li>Redux</li><li>Zustand</li></ul><h3>Best Practices:</h3><ul><li>Local vs global state</li><li>State lifting</li><li>Performance optimization</li></ul>',
                            'summary' => 'Learn state management techniques in React Native',
                            'lesson_type' => 'text',
                            'duration_minutes' => 65,
                            'objectives' => 'Implement effective state management in mobile apps'
                        ],
                    ]
                ],
                [
                    'title' => 'Flutter Development',
                    'description' => 'Build native mobile apps with Flutter',
                    'lessons' => [
                        [
                            'title' => 'Introduction to Flutter',
                            'content' => '<h2>Flutter Framework</h2><p>Flutter is Google\'s UI toolkit for building natively compiled applications for mobile, web, and desktop.</p><h3>Key Features:</h3><ul><li>Dart programming language</li><li>Hot reload for fast development</li><li>Rich widget library</li><li>Single codebase for multiple platforms</li></ul><h3>Widgets:</h3><ul><li>StatelessWidget</li><li>StatefulWidget</li><li>Material and Cupertino widgets</li></ul><h3>Example:</h3><pre>import \'package:flutter/material.dart\';\n\nvoid main() {\n  runApp(MyApp());\n}\n\nclass MyApp extends StatelessWidget {\n  @override\n  Widget build(BuildContext context) {\n    return MaterialApp(\n      home: Text(\'Hello Flutter!\'),\n    );\n  }\n}</pre>',
                            'summary' => 'Get started with Flutter and Dart programming',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Understand Flutter architecture and build your first Flutter app'
                        ],
                    ]
                ],
                [
                    'title' => 'App Deployment',
                    'description' => 'Publish your apps to Google Play Store and Apple App Store',
                    'lessons' => [
                        [
                            'title' => 'Publishing to App Stores',
                            'content' => '<h2>App Store Deployment</h2><p>Learn how to prepare and publish your mobile apps to Google Play Store and Apple App Store.</p><h3>Google Play Store:</h3><ul><li>Create developer account</li><li>App signing</li><li>Store listing</li><li>Privacy policy</li><li>App bundle creation</li></ul><h3>Apple App Store:</h3><ul><li>Apple Developer account</li><li>App Store Connect</li><li>App Store guidelines</li><li>TestFlight for beta testing</li><li>App submission process</li></ul><h3>Requirements:</h3><ul><li>App icons and screenshots</li><li>App descriptions</li><li>Privacy policy</li><li>Age rating</li></ul>',
                            'summary' => 'Learn to publish mobile apps to app stores',
                            'lesson_type' => 'video',
                            'duration_minutes' => 60,
                            'objectives' => 'Successfully publish your app to Google Play and App Store'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * ICDL Certification Course
     */
    private function getICDLCertificationCourse(): array
    {
        return [
            'title' => 'ICDL Certification',
            'description' => 'Master essential computer skills recognized globally. Earn ICDL certificates from our accredited testing center in Kampala.',
            'short_description' => 'Master essential computer skills - ICDL certified',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 100,
            'category' => 'Computer Skills',
            'tags' => ['icdl', 'computer-essentials', 'office', 'certification', 'spreadsheets', 'word'],
            'requirements' => ['Basic computer knowledge', 'Access to computer with Microsoft Office'],
            'what_you_learn' => [
                'Computer essentials and operating systems',
                'Online essentials and web browsing',
                'Word processing with Microsoft Word',
                'Spreadsheets with Microsoft Excel',
                'Presentations with Microsoft PowerPoint',
                'Database fundamentals',
                'ICDL certification exam preparation'
            ],
            'modules' => [
                [
                    'title' => 'Computer Essentials',
                    'description' => 'Master computer fundamentals and operating system basics',
                    'lessons' => [
                        [
                            'title' => 'Introduction to Computers',
                            'content' => '<h2>Computer Basics</h2><p>Learn the fundamental concepts of computers and how they work.</p><h3>Key Topics:</h3><ul><li>Computer hardware components</li><li>Operating systems</li><li>File management</li><li>Basic computer operations</li></ul><h3>Components:</h3><ul><li>CPU, RAM, Storage</li><li>Input/Output devices</li><li>Software types</li></ul>',
                            'summary' => 'Understand computer hardware, software, and basic operations',
                            'lesson_type' => 'text',
                            'duration_minutes' => 45,
                            'objectives' => 'Identify computer components and understand operating system basics'
                        ],
                        [
                            'title' => 'File Management',
                            'content' => '<h2>Organizing Files and Folders</h2><p>Efficient file management is essential for productivity.</p><h3>Key Concepts:</h3><ul><li>Creating and organizing folders</li><li>File naming conventions</li><li>Copying and moving files</li><li>Searching for files</li><li>File compression</li></ul><h3>Best Practices:</h3><ul><li>Logical folder structure</li><li>Descriptive file names</li><li>Regular backups</li><li>Storage organization</li></ul>',
                            'summary' => 'Master file and folder management techniques',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 40,
                            'objectives' => 'Efficiently organize and manage computer files and folders'
                        ],
                    ]
                ],
                [
                    'title' => 'Online Essentials',
                    'description' => 'Master internet browsing, email, and online communication',
                    'lessons' => [
                        [
                            'title' => 'Web Browsing and Search',
                            'content' => '<h2>Internet Basics</h2><p>Learn to effectively browse the web and find information.</p><h3>Key Topics:</h3><ul><li>Web browsers</li><li>URLs and web addresses</li><li>Search engines</li><li>Bookmarks and favorites</li><li>Browser security</li></ul><h3>Search Techniques:</h3><ul><li>Using keywords</li><li>Advanced search operators</li><li>Evaluating search results</li></ul>',
                            'summary' => 'Master web browsing and search techniques',
                            'lesson_type' => 'text',
                            'duration_minutes' => 50,
                            'objectives' => 'Browse the web effectively and find reliable information'
                        ],
                        [
                            'title' => 'Email Communication',
                            'content' => '<h2>Email Essentials</h2><p>Master professional email communication skills.</p><h3>Key Topics:</h3><ul><li>Email account setup</li><li>Composing and sending emails</li><li>Managing inbox</li><li>Attachments</li><li>Email etiquette</li></ul><h3>Best Practices:</h3><ul><li>Clear subject lines</li><li>Professional formatting</li><li>Organizing folders</li><li>Spam management</li></ul>',
                            'summary' => 'Use email effectively for professional communication',
                            'lesson_type' => 'video',
                            'duration_minutes' => 45,
                            'objectives' => 'Compose, send, and manage professional emails'
                        ],
                    ]
                ],
                [
                    'title' => 'Word Processing',
                    'description' => 'Master Microsoft Word for document creation',
                    'lessons' => [
                        [
                            'title' => 'Word Basics',
                            'content' => '<h2>Microsoft Word Fundamentals</h2><p>Learn to create and format professional documents.</p><h3>Key Features:</h3><ul><li>Document creation</li><li>Text formatting</li><li>Paragraph formatting</li><li>Headers and footers</li><li>Page setup</li></ul><h3>Formatting:</h3><ul><li>Fonts and styles</li><li>Bold, italic, underline</li><li>Alignment options</li><li>Bullets and numbering</li></ul>',
                            'summary' => 'Create and format documents in Microsoft Word',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 55,
                            'objectives' => 'Create professional documents with proper formatting'
                        ],
                        [
                            'title' => 'Advanced Word Features',
                            'content' => '<h2>Advanced Word Techniques</h2><p>Master advanced features for complex documents.</p><h3>Advanced Features:</h3><ul><li>Tables and charts</li><li>Mail merge</li><li>Styles and themes</li><li>Track changes</li><li>Templates</li></ul>',
                            'summary' => 'Use advanced Word features for complex documents',
                            'lesson_type' => 'video',
                            'duration_minutes' => 60,
                            'objectives' => 'Create complex documents using advanced Word features'
                        ],
                    ]
                ],
                [
                    'title' => 'Spreadsheets',
                    'description' => 'Master Microsoft Excel for data analysis',
                    'lessons' => [
                        [
                            'title' => 'Excel Fundamentals',
                            'content' => '<h2>Microsoft Excel Basics</h2><p>Learn spreadsheet fundamentals for data management.</p><h3>Key Concepts:</h3><ul><li>Cells, rows, and columns</li><li>Data entry and formatting</li><li>Basic formulas</li><li>Functions (SUM, AVERAGE, etc.)</li><li>Charts and graphs</li></ul><h3>Formulas:</h3><pre>=SUM(A1:A10)\n=AVERAGE(B1:B20)\n=IF(C1>100, "High", "Low")</pre>',
                            'summary' => 'Master Excel basics for data management and analysis',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 65,
                            'objectives' => 'Create spreadsheets with formulas and basic data analysis'
                        ],
                        [
                            'title' => 'Advanced Excel Functions',
                            'content' => '<h2>Advanced Excel Techniques</h2><p>Master advanced Excel functions for complex data analysis.</p><h3>Advanced Features:</h3><ul><li>VLOOKUP and HLOOKUP</li><li>Pivot tables</li><li>Data validation</li><li>Conditional formatting</li><li>Macros basics</li></ul>',
                            'summary' => 'Use advanced Excel functions for complex data analysis',
                            'lesson_type' => 'video',
                            'duration_minutes' => 70,
                            'objectives' => 'Perform advanced data analysis using Excel functions'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Robotics & STEM Course
     */
    private function getRoboticsSTEMCourse(): array
    {
        return [
            'title' => 'Robotics & STEM',
            'description' => 'Hands-on learning in AI, coding, STEM, and robotics. Perfect for ages 7-19 during our holiday code camps.',
            'short_description' => 'Hands-on AI, coding, STEM, and robotics for ages 7-19',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 140,
            'category' => 'STEM',
            'tags' => ['robotics', 'ai', 'stem', 'coding', 'arduino', 'microbit', 'python'],
            'requirements' => ['Ages 7-19', 'Basic math skills', 'Willingness to experiment'],
            'what_you_learn' => [
                'Basic robotics concepts',
                'Programming robots',
                'Arduino and electronics',
                'Microbit programming',
                'STEM applications',
                'Problem-solving skills',
                'Team collaboration'
            ],
            'modules' => [
                [
                    'title' => 'Introduction to Robotics',
                    'description' => 'Learn the fundamentals of robotics and automation',
                    'lessons' => [
                        [
                            'title' => 'What is a Robot?',
                            'content' => '<h2>Robotics Fundamentals</h2><p>Learn what robots are and how they work. Understand the basic components and concepts of robotics.</p><h3>Key Concepts:</h3><ul><li>Definition of robots</li><li>Robot components (sensors, actuators, controllers)</li><li>Types of robots</li><li>Applications of robotics</li></ul><h3>Robot Components:</h3><ul><li>Sensors - Detect environment</li><li>Actuators - Move and interact</li><li>Controllers - Process information</li><li>Power sources</li></ul>',
                            'summary' => 'Understand basic robotics concepts and components',
                            'lesson_type' => 'text',
                            'duration_minutes' => 45,
                            'objectives' => 'Identify robot components and understand how robots work'
                        ],
                        [
                            'title' => 'Simple Control Systems',
                            'content' => '<h2>Robot Control Basics</h2><p>Learn how to control simple robots and automated systems.</p><h3>Control Concepts:</h3><ul><li>Input-Process-Output cycle</li><li>Basic programming for robots</li><li>Sequential instructions</li><li>Loops and conditionals</li></ul>',
                            'summary' => 'Learn to program and control simple robots',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 50,
                            'objectives' => 'Create basic control programs for robots'
                        ],
                    ]
                ],
                [
                    'title' => 'Microbit Programming',
                    'description' => 'Learn programming with BBC Microbit',
                    'lessons' => [
                        [
                            'title' => 'Getting Started with Microbit',
                            'content' => '<h2>BBC Microbit Basics</h2><p>The Microbit is a small programmable computer perfect for learning coding and creating interactive projects.</p><h3>Features:</h3><ul><li>LED display</li><li>Buttons</li><li>Accelerometer</li><li>Compass</li><li>Bluetooth connectivity</li></ul><h3>Programming:</h3><ul><li>Block-based coding</li><li>Python programming</li><li>JavaScript</li></ul>',
                            'summary' => 'Learn to program the Microbit for interactive projects',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Program Microbit to create interactive displays and sensors'
                        ],
                        [
                            'title' => 'Microbit Robotics Projects',
                            'content' => '<h2>Building with Microbit</h2><p>Use Microbit to control robots and create STEM projects.</p><h3>Projects:</h3><ul><li>Remote-controlled robots</li><li>Weather stations</li><li>Game controllers</li><li>Motion detectors</li></ul>',
                            'summary' => 'Build robotics projects using Microbit',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Create functional robotics projects with Microbit'
                        ],
                    ]
                ],
                [
                    'title' => 'Arduino Robotics',
                    'description' => 'Build robots with Arduino microcontroller',
                    'lessons' => [
                        [
                            'title' => 'Arduino Basics',
                            'content' => '<h2>Arduino Programming</h2><p>Arduino is an open-source electronics platform perfect for building robots and interactive projects.</p><h3>Key Concepts:</h3><ul><li>Arduino board components</li><li>Arduino IDE</li><li>Basic programming syntax</li><li>Digital and analog I/O</li></ul><h3>Programming:</h3><pre>void setup() {\n  pinMode(13, OUTPUT);\n}\n\nvoid loop() {\n  digitalWrite(13, HIGH);\n  delay(1000);\n  digitalWrite(13, LOW);\n  delay(1000);\n}</pre>',
                            'summary' => 'Learn Arduino programming for robotics',
                            'lesson_type' => 'text',
                            'duration_minutes' => 65,
                            'objectives' => 'Program Arduino to control LEDs, motors, and sensors'
                        ],
                        [
                            'title' => 'Building Arduino Robots',
                            'content' => '<h2>Robot Construction</h2><p>Build complete robots using Arduino and electronic components.</p><h3>Components:</h3><ul><li>Motors and servos</li><li>Sensors (ultrasonic, infrared)</li><li>Motor drivers</li><li>Power systems</li></ul><h3>Projects:</h3><ul><li>Line-following robots</li><li>Obstacle-avoiding robots</li><li>Remote-controlled robots</li></ul>',
                            'summary' => 'Build functional robots with Arduino',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Construct and program working robots with Arduino'
                        ],
                    ]
                ],
                [
                    'title' => 'Machine Learning for Kids',
                    'description' => 'Introduction to AI and machine learning concepts',
                    'lessons' => [
                        [
                            'title' => 'Introduction to AI',
                            'content' => '<h2>Artificial Intelligence Basics</h2><p>Learn what AI is and how it works in simple terms suitable for young learners.</p><h3>Key Concepts:</h3><ul><li>What is artificial intelligence?</li><li>How machines learn</li><li>Real-world AI applications</li><li>Ethics in AI</li></ul><h3>Simple ML:</h3><ul><li>Image recognition</li><li>Pattern recognition</li><li>Decision-making</li></ul>',
                            'summary' => 'Understand AI concepts in an age-appropriate way',
                            'lesson_type' => 'video',
                            'duration_minutes' => 50,
                            'objectives' => 'Explain basic AI concepts and their applications'
                        ],
                        [
                            'title' => 'Creating Simple ML Models',
                            'content' => '<h2>Machine Learning Projects</h2><p>Create simple machine learning models using kid-friendly tools.</p><h3>Tools:</h3><ul><li>Machine Learning for Kids</li><li>Teachable Machine</li><li>Scratch with ML extensions</li></ul><h3>Projects:</h3><ul><li>Image classifier</li><li>Voice recognition</li><li>Gesture recognition</li></ul>',
                            'summary' => 'Create simple machine learning models',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Build and train simple machine learning models'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Scratch Programming - Beginners Course (Ages 3-7)
     */
    private function getScratchBeginnersCourse(): array
    {
        return [
            'title' => 'Scratch Programming - Beginners',
            'description' => 'Fun and interactive programming for young children aged 3-7. Build games, animations, and stories while learning coding fundamentals through play.',
            'short_description' => 'Scratch programming for ages 3-7 - Learn coding through play',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 80,
            'category' => 'Programming',
            'tags' => ['scratch', 'beginners', 'kids', 'programming', 'animation', 'games'],
            'requirements' => ['Ages 3-7', 'Basic mouse and keyboard skills', 'Parent supervision recommended'],
            'what_you_learn' => [
                'Navigating Scratch interface',
                'Moving sprites',
                'Basic blocks and commands',
                'Creating simple animations',
                'Making characters talk',
                'Building first games',
                'Coding fundamentals through play'
            ],
            'modules' => [
                [
                    'title' => 'Getting Started with Scratch',
                    'description' => 'Introduction to Scratch interface and basic concepts',
                    'lessons' => [
                        [
                            'title' => 'Welcome to Scratch!',
                            'content' => '<h2>Introduction to Scratch</h2><p>Scratch is a fun programming language where you can create your own interactive stories, games, and animations!</p><h3>What is Scratch?</h3><ul><li>A visual programming language</li><li>Drag and drop blocks to code</li><li>Create sprites (characters)</li><li>Make them move and talk</li></ul><h3>The Scratch Interface:</h3><ul><li>Stage - Where your project appears</li><li>Sprite list - Your characters</li><li>Blocks palette - Your code pieces</li><li>Scripts area - Where you build code</li></ul><h3>First Steps:</h3><ul><li>Click on a sprite</li><li>Drag blocks from the blocks palette</li><li>Snap blocks together</li><li>Click the green flag to run!</li></ul>',
                            'summary' => 'Learn the Scratch interface and create your first project',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 30,
                            'objectives' => 'Navigate Scratch interface and understand basic concepts'
                        ],
                        [
                            'title' => 'Moving Sprites',
                            'content' => '<h2>Making Sprites Move</h2><p>Learn how to make your sprites move around the stage!</p><h3>Motion Blocks:</h3><ul><li>move 10 steps</li><li>turn 15 degrees</li><li>go to x: y:</li><li>glide to position</li></ul><h3>Try This:</h3><ul><li>Click on "Motion" blocks</li><li>Drag "move 10 steps" block</li><li>Click the block to make sprite move</li><li>Change the number to move more!</li></ul><h3>Challenge:</h3><ul><li>Make a sprite walk across the screen</li><li>Make it turn in a circle</li><li>Make it glide smoothly</li></ul>',
                            'summary' => 'Use motion blocks to move sprites around the stage',
                            'lesson_type' => 'video',
                            'duration_minutes' => 35,
                            'objectives' => 'Make sprites move using motion blocks'
                        ],
                    ]
                ],
                [
                    'title' => 'Making Animations',
                    'description' => 'Create simple animations and stories',
                    'lessons' => [
                        [
                            'title' => 'Changing Costumes',
                            'content' => '<h2>Sprite Costumes</h2><p>Sprites can have different costumes (outfits). You can make animations by switching costumes!</p><h3>How to:</h3><ul><li>Click on the "Costumes" tab</li><li>See all the costumes your sprite has</li><li>Click on different costumes</li><li>Use "next costume" block to animate</li></ul><h3>Animation Trick:</h3><ul><li>Use "repeat" block</li><li>Put "next costume" inside</li><li>Add "wait" block to slow it down</li><li>Now your sprite animates!</li></ul>',
                            'summary' => 'Learn to change costumes and create simple animations',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 40,
                            'objectives' => 'Create animations by switching sprite costumes'
                        ],
                        [
                            'title' => 'Making Characters Talk',
                            'content' => '<h2>Say and Think Blocks</h2><p>Make your sprites talk and think using the "say" and "think" blocks!</p><h3>Look Blocks:</h3><ul><li>say "Hello!" for 2 seconds</li><li>think "Hmm..." for 2 seconds</li><li>change size by 10</li><li>show / hide</li></ul><h3>Story Time:</h3><ul><li>Make one sprite say something</li><li>Wait a moment</li><li>Make another sprite respond</li><li>Create a conversation!</li></ul>',
                            'summary' => 'Use say and think blocks to make sprites communicate',
                            'lesson_type' => 'video',
                            'duration_minutes' => 35,
                            'objectives' => 'Create conversations between sprites'
                        ],
                    ]
                ],
                [
                    'title' => 'Simple Games',
                    'description' => 'Build your first interactive games',
                    'lessons' => [
                        [
                            'title' => 'Catching Game',
                            'content' => '<h2>Make a Catching Game</h2><p>Create a simple game where a sprite catches falling objects!</p><h3>Game Steps:</h3><ul><li>Make a sprite move with arrow keys</li><li>Make another sprite fall from the top</li><li>Use "when touching" block</li><li>Add a score when they touch!</li></ul><h3>Blocks You\'ll Need:</h3><ul><li>Events: "when green flag clicked"</li><li>Control: "forever" loop</li><li>Sensing: "touching sprite?"</li><li>Variables: score</li></ul>',
                            'summary' => 'Build a simple catching game with scoring',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 45,
                            'objectives' => 'Create an interactive game with player controls and scoring'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Scratch Programming - Intermediate Course (Ages 8-10)
     */
    private function getScratchIntermediateCourse(): array
    {
        return [
            'title' => 'Scratch Programming - Intermediate',
            'description' => 'Continue your Scratch journey! Learn loops, conditionals, variables, and create more complex games and animations. Perfect for ages 8-10.',
            'short_description' => 'Scratch intermediate for ages 8-10 - Advanced blocks and concepts',
            'difficulty_level' => 'Intermediate',
            'estimated_duration' => 120,
            'category' => 'Programming',
            'tags' => ['scratch', 'intermediate', 'kids', 'programming', 'games', 'loops', 'variables'],
            'requirements' => ['Completed Scratch Beginners', 'Ages 8-10', 'Basic Scratch knowledge'],
            'what_you_learn' => [
                'Loops and conditionals',
                'Variables and data',
                'Event broadcasting',
                'Cloning sprites',
                'Building complex games',
                'Interactive stories',
                'Problem-solving with code'
            ],
            'modules' => [
                [
                    'title' => 'Loops and Repetition',
                    'description' => 'Learn to repeat actions using loops',
                    'lessons' => [
                        [
                            'title' => 'Using Repeat Blocks',
                            'content' => '<h2>Repetition in Scratch</h2><p>Loops help you repeat actions without writing the same code many times!</p><h3>Loop Blocks:</h3><ul><li>repeat 10 - Repeat a set number of times</li><li>forever - Repeat forever</li><li>repeat until - Repeat until a condition is met</li></ul><h3>Examples:</h3><ul><li>Repeat to draw shapes</li><li>Forever loops for continuous actions</li><li>Nested loops for complex patterns</li></ul><h3>Try This:</h3><pre>when green flag clicked\nrepeat 4\n  move 50 steps\n  turn 90 degrees\nend</pre>',
                            'summary' => 'Learn to use repeat blocks for efficient coding',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 45,
                            'objectives' => 'Use repeat blocks to create efficient, reusable code'
                        ],
                        [
                            'title' => 'Conditional Statements',
                            'content' => '<h2>If-Then Logic</h2><p>Make decisions in your code using if-then blocks!</p><h3>Conditional Blocks:</h3><ul><li>if then - Do something if condition is true</li><li>if then else - Do one thing or another</li><li>Operators: &lt;, &gt;, =</li></ul><h3>Examples:</h3><ul><li>If touching edge, bounce</li><li>If score &gt; 100, show "You Win!"</li><li>If key pressed, move</li></ul>',
                            'summary' => 'Use conditionals to make decisions in your code',
                            'lesson_type' => 'video',
                            'duration_minutes' => 50,
                            'objectives' => 'Create interactive programs using conditional statements'
                        ],
                    ]
                ],
                [
                    'title' => 'Variables and Data',
                    'description' => 'Store and use information with variables',
                    'lessons' => [
                        [
                            'title' => 'Creating Variables',
                            'content' => '<h2>Variables in Scratch</h2><p>Variables are containers that store information you can use and change in your projects!</p><h3>Variable Types:</h3><ul><li>Numbers - For scores, timers</li><li>Text/strings - For names, messages</li><li>Boolean - True/false values</li></ul><h3>Using Variables:</h3><ul><li>Create a variable</li><li>Set its value</li><li>Change its value</li><li>Use it in your code</li></ul><h3>Example - Score:</h3><pre>when green flag clicked\nset score to 0\nwhen sprite touched\nchange score by 1</pre>',
                            'summary' => 'Create and use variables to store data',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 55,
                            'objectives' => 'Use variables to track scores, timers, and other data'
                        ],
                        [
                            'title' => 'Lists in Scratch',
                            'content' => '<h2>Storing Multiple Items</h2><p>Lists let you store many items together, like a shopping list!</p><h3>List Operations:</h3><ul><li>Create a list</li><li>Add items to list</li><li>Delete items</li><li>Access specific items</li></ul><h3>Uses:</h3><ul><li>Inventory systems in games</li><li>High scores</li><li>Storing player names</li><li>Question and answer lists</li></ul>',
                            'summary' => 'Use lists to store and manage multiple pieces of data',
                            'lesson_type' => 'video',
                            'duration_minutes' => 50,
                            'objectives' => 'Create and manipulate lists in Scratch projects'
                        ],
                    ]
                ],
                [
                    'title' => 'Advanced Game Development',
                    'description' => 'Build more complex games with multiple levels and features',
                    'lessons' => [
                        [
                            'title' => 'Broadcasting Messages',
                            'content' => '<h2>Sprite Communication</h2><p>Sprites can talk to each other using broadcasts!</p><h3>Broadcasting:</h3><ul><li>broadcast "message" - Send a message</li><li>when I receive "message" - React to message</li><li>broadcast "message" and wait - Wait for response</li></ul><h3>Uses:</h3><ul><li>Start game events</li><li>Level transitions</li><li>Sprite coordination</li><li>Game state changes</li></ul>',
                            'summary' => 'Use broadcasting to coordinate multiple sprites',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Create games with sprite communication using broadcasts'
                        ],
                        [
                            'title' => 'Sprite Cloning',
                            'content' => '<h2>Creating Copies of Sprites</h2><p>Cloning lets you create copies of sprites that run independently!</p><h3>Cloning Blocks:</h3><ul><li>create clone of myself</li><li>when I start as a clone</li><li>delete this clone</li></ul><h3>Uses:</h3><ul><li>Multiple enemies in games</li><li>Particle effects</li><li>Bullets or projectiles</li><li>Falling objects</li></ul>',
                            'summary' => 'Use cloning to create multiple sprite instances',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Implement cloning for dynamic game elements'
                        ],
                        [
                            'title' => 'Building a Platform Game',
                            'content' => '<h2>Complete Platform Game</h2><p>Create a full platform game with jumping, enemies, and levels!</p><h3>Game Features:</h3><ul><li>Player controls (arrow keys, spacebar)</li><li>Gravity and jumping</li><li>Platforms to land on</li><li>Enemies that move</li><li>Lives and scoring</li><li>Level progression</li></ul><h3>Advanced Concepts:</h3><ul><li>Collision detection</li><li>Game states</li><li>Multiple levels</li><li>Win/lose conditions</li></ul>',
                            'summary' => 'Build a complete platform game with advanced features',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Create a complete platform game with all essential features'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Scratch Programming - Advanced Course (Ages 11-12)
     */
    private function getScratchAdvancedCourse(): array
    {
        return [
            'title' => 'Scratch Programming - Advanced',
            'description' => 'Master advanced Scratch concepts! Learn custom blocks, data structures, algorithms, and create sophisticated games and applications. Perfect for ages 11-12.',
            'short_description' => 'Scratch advanced for ages 11-12 - Master advanced programming',
            'difficulty_level' => 'Advanced',
            'estimated_duration' => 160,
            'category' => 'Programming',
            'tags' => ['scratch', 'advanced', 'kids', 'programming', 'algorithms', 'custom-blocks'],
            'requirements' => ['Completed Scratch Intermediate', 'Ages 11-12', 'Strong Scratch foundation'],
            'what_you_learn' => [
                'Custom blocks and functions',
                'Advanced algorithms',
                'Data structures',
                'Complex game mechanics',
                'AI and game AI',
                'Optimization techniques',
                'Project planning and design'
            ],
            'modules' => [
                [
                    'title' => 'Custom Blocks and Functions',
                    'description' => 'Create your own reusable code blocks',
                    'lessons' => [
                        [
                            'title' => 'Creating Custom Blocks',
                            'content' => '<h2>Your Own Code Blocks</h2><p>Custom blocks let you create your own reusable code pieces, like building blocks for your programs!</p><h3>Features:</h3><ul><li>Define your own blocks</li><li>Add parameters (inputs)</li><li>Run without screen refresh</li><li>Organize code better</li></ul><h3>Benefits:</h3><ul><li>Code reusability</li><li>Easier to read</li><li>Faster execution</li><li>Better organization</li></ul><h3>Example - Draw Square:</h3><pre>define draw square (size)\nrepeat 4\n  move (size) steps\n  turn 90 degrees\nend</pre>',
                            'summary' => 'Create custom blocks for reusable, organized code',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Design and use custom blocks to organize and optimize code'
                        ],
                        [
                            'title' => 'Functions with Parameters',
                            'content' => '<h2>Blocks That Take Inputs</h2><p>Make your custom blocks flexible by adding parameters!</p><h3>Parameters:</h3><ul><li>Number parameters</li><li>Text parameters</li><li>Boolean parameters</li><li>Multiple parameters</li></ul><h3>Advanced Uses:</h3><ul><li>Mathematical functions</li><li>Drawing shapes of different sizes</li><li>Reusable game mechanics</li><li>Animation procedures</li></ul>',
                            'summary' => 'Use parameters to create flexible, reusable functions',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Create parameterized functions for flexible code'
                        ],
                    ]
                ],
                [
                    'title' => 'Advanced Algorithms',
                    'description' => 'Learn programming algorithms and problem-solving',
                    'lessons' => [
                        [
                            'title' => 'Sorting and Searching',
                            'content' => '<h2>Organizing Data</h2><p>Learn how to sort and search through data efficiently!</p><h3>Sorting Algorithms:</h3><ul><li>Bubble sort</li><li>Selection sort</li><li>Quick sort basics</li></ul><h3>Searching:</h3><ul><li>Linear search</li><li>Binary search</li></ul><h3>Applications:</h3><ul><li>High score lists</li><li>Inventory management</li><li>Data organization</li></ul>',
                            'summary' => 'Implement sorting and searching algorithms',
                            'lesson_type' => 'text',
                            'duration_minutes' => 65,
                            'objectives' => 'Implement basic sorting and searching algorithms'
                        ],
                        [
                            'title' => 'Pathfinding Algorithms',
                            'content' => '<h2>Finding the Best Path</h2><p>Make sprites find their way through mazes and obstacles!</p><h3>Concepts:</h3><ul><li>Grid-based movement</li><li>Pathfinding basics</li><li>Distance calculations</li><li>Obstacle avoidance</li></ul><h3>Applications:</h3><ul><li>Maze solving</li><li>Enemy AI movement</li><li>Navigation systems</li></ul>',
                            'summary' => 'Implement pathfinding for smart sprite movement',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Create pathfinding algorithms for game characters'
                        ],
                    ]
                ],
                [
                    'title' => 'Complex Game Development',
                    'description' => 'Build sophisticated games with advanced features',
                    'lessons' => [
                        [
                            'title' => 'Game State Management',
                            'content' => '<h2>Managing Game States</h2><p>Learn to organize your game with menus, levels, pause screens, and game over states!</p><h3>Game States:</h3><ul><li>Main menu</li><li>Playing</li><li>Paused</li><li>Game over</li><li>Victory</li></ul><h3>Implementation:</h3><ul><li>State variables</li><li>Broadcasting for state changes</li><li>Showing/hiding sprites</li><li>Code organization</li></ul>',
                            'summary' => 'Implement game state management for professional games',
                            'lesson_type' => 'video',
                            'duration_minutes' => 65,
                            'objectives' => 'Create games with proper state management'
                        ],
                        [
                            'title' => 'AI in Games',
                            'content' => '<h2>Smart Game Characters</h2><p>Make computer-controlled characters that seem intelligent!</p><h3>AI Techniques:</h3><ul><li>Random movement patterns</li><li>Following the player</li><li>Obstacle avoidance</li><li>Decision trees</li><li>Simple machine learning</li></ul><h3>Applications:</h3><ul><li>Enemy behavior</li><li>NPC interactions</li><li>Adaptive difficulty</li></ul>',
                            'summary' => 'Implement AI behaviors for game characters',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 75,
                            'objectives' => 'Create intelligent game characters using AI techniques'
                        ],
                        [
                            'title' => 'Final Project - Complete Game',
                            'content' => '<h2>Build Your Masterpiece</h2><p>Combine everything you\'ve learned to create a complete, polished game!</p><h3>Project Requirements:</h3><ul><li>Multiple levels or modes</li><li>Score and lives system</li><li>AI enemies</li><li>Sound effects and music</li><li>Custom blocks</li><li>Polished graphics</li><li>Clear instructions</li></ul><h3>Planning:</h3><ul><li>Game design document</li><li>Feature list</li><li>Timeline</li><li>Testing plan</li></ul>',
                            'summary' => 'Create a complete, polished game combining all advanced concepts',
                            'lesson_type' => 'assignment',
                            'duration_minutes' => 90,
                            'objectives' => 'Design and build a complete game project from start to finish'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Python & Advanced Programming Course (Ages 13+)
     */
    private function getPythonAdvancedCourse(): array
    {
        return [
            'title' => 'Python & Advanced Programming',
            'description' => 'Learn Python and advanced programming for teens and adults aged 13+. Build real-world projects and applications using modern programming practices.',
            'short_description' => 'Python and advanced programming for ages 13+',
            'difficulty_level' => 'Advanced',
            'estimated_duration' => 200,
            'category' => 'Programming',
            'tags' => ['python', 'programming', 'advanced', 'teens', 'adults', 'projects', 'django', 'flask'],
            'requirements' => ['Ages 13+', 'Basic computer skills', 'Logical thinking', 'Willingness to code'],
            'what_you_learn' => [
                'Python programming fundamentals',
                'Object-oriented programming',
                'Data structures and algorithms',
                'Web development with Django/Flask',
                'Database integration',
                'API development',
                'Real-world project development'
            ],
            'modules' => [
                [
                    'title' => 'Python Fundamentals',
                    'description' => 'Master Python syntax and basic programming concepts',
                    'lessons' => [
                        [
                            'title' => 'Introduction to Python',
                            'content' => '<h2>Python Programming Basics</h2><p>Python is a powerful, easy-to-learn programming language perfect for beginners and experts alike.</p><h3>Why Python?</h3><ul><li>Simple and readable syntax</li><li>Versatile (web, data science, AI)</li><li>Large community and libraries</li><li>Great for beginners</li></ul><h3>First Program:</h3><pre>print("Hello, World!")\n\nname = input("What is your name? ")\nprint(f"Hello, {name}!")</pre><h3>Key Concepts:</h3><ul><li>Variables and data types</li><li>Input and output</li><li>Basic operations</li></ul>',
                            'summary' => 'Get started with Python programming',
                            'lesson_type' => 'text',
                            'duration_minutes' => 60,
                            'objectives' => 'Write your first Python programs and understand basic syntax'
                        ],
                        [
                            'title' => 'Control Flow and Functions',
                            'content' => '<h2>Controlling Program Flow</h2><p>Learn to control how your program executes using conditionals, loops, and functions.</p><h3>Conditionals:</h3><pre>if age >= 18:\n    print("Adult")\nelif age >= 13:\n    print("Teen")\nelse:\n    print("Child")</pre><h3>Loops:</h3><pre>for i in range(10):\n    print(i)\n\nwhile condition:\n    # do something</pre><h3>Functions:</h3><pre>def greet(name):\n    return f"Hello, {name}!"</pre>',
                            'summary' => 'Master control flow and function creation',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 70,
                            'objectives' => 'Use conditionals, loops, and functions effectively'
                        ],
                    ]
                ],
                [
                    'title' => 'Object-Oriented Programming',
                    'description' => 'Learn OOP concepts and design patterns',
                    'lessons' => [
                        [
                            'title' => 'Classes and Objects',
                            'content' => '<h2>OOP Fundamentals</h2><p>Object-Oriented Programming helps organize code and model real-world concepts.</p><h3>Key Concepts:</h3><ul><li>Classes and objects</li><li>Attributes and methods</li><li>Encapsulation</li><li>Inheritance</li><li>Polymorphism</li></ul><h3>Example:</h3><pre>class Dog:\n    def __init__(self, name, breed):\n        self.name = name\n        self.breed = breed\n    \n    def bark(self):\n        return f"{self.name} says Woof!"</pre>',
                            'summary' => 'Understand and implement OOP concepts',
                            'lesson_type' => 'video',
                            'duration_minutes' => 75,
                            'objectives' => 'Create classes and objects to model real-world entities'
                        ],
                        [
                            'title' => 'Inheritance and Polymorphism',
                            'content' => '<h2>Advanced OOP</h2><p>Build on classes using inheritance and polymorphism for code reuse.</p><h3>Inheritance:</h3><pre>class Animal:\n    def speak(self):\n        pass\n\nclass Dog(Animal):\n    def speak(self):\n        return "Woof!"</pre><h3>Polymorphism:</h3><ul><li>Method overriding</li><li>Abstract classes</li><li>Multiple inheritance</li></ul>',
                            'summary' => 'Master inheritance and polymorphism',
                            'lesson_type' => 'text',
                            'duration_minutes' => 65,
                            'objectives' => 'Implement inheritance and polymorphism in Python'
                        ],
                    ]
                ],
                [
                    'title' => 'Web Development with Django',
                    'description' => 'Build web applications with Django framework',
                    'lessons' => [
                        [
                            'title' => 'Django Basics',
                            'content' => '<h2>Getting Started with Django</h2><p>Django is a powerful Python web framework for building web applications quickly.</p><h3>Key Concepts:</h3><ul><li>MVC pattern (Models, Views, Controllers)</li><li>URL routing</li><li>Templates</li><li>Admin panel</li></ul><h3>Setting Up:</h3><pre>pip install django\ndjango-admin startproject myproject\ncd myproject\npython manage.py runserver</pre>',
                            'summary' => 'Set up and create your first Django project',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 80,
                            'objectives' => 'Create a Django project and understand its structure'
                        ],
                        [
                            'title' => 'Django Models and Database',
                            'content' => '<h2>Working with Data</h2><p>Learn to create database models and manage data in Django.</p><h3>Models:</h3><pre>from django.db import models\n\nclass Student(models.Model):\n    name = models.CharField(max_length=100)\n    age = models.IntegerField()\n    email = models.EmailField()</pre><h3>Database Operations:</h3><ul><li>Migrations</li><li>Querying data</li><li>Relationships</li></ul>',
                            'summary' => 'Create database models and manage data',
                            'lesson_type' => 'video',
                            'duration_minutes' => 70,
                            'objectives' => 'Design and implement database models in Django'
                        ],
                        [
                            'title' => 'Building RESTful APIs',
                            'content' => '<h2>API Development</h2><p>Create RESTful APIs using Django REST Framework.</p><h3>API Concepts:</h3><ul><li>HTTP methods (GET, POST, PUT, DELETE)</li><li>Serializers</li><li>ViewSets</li><li>Authentication</li></ul><h3>Example API:</h3><pre>from rest_framework import viewsets\nfrom rest_framework.response import Response\n\nclass StudentViewSet(viewsets.ModelViewSet):\n    queryset = Student.objects.all()\n    serializer_class = StudentSerializer</pre>',
                            'summary' => 'Build RESTful APIs with Django REST Framework',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 85,
                            'objectives' => 'Create functional REST APIs for web applications'
                        ],
                    ]
                ],
                [
                    'title' => 'Real-World Projects',
                    'description' => 'Build complete applications combining all skills',
                    'lessons' => [
                        [
                            'title' => 'Project Planning and Design',
                            'content' => '<h2>Planning Your Project</h2><p>Learn to plan and design applications before coding.</p><h3>Planning Steps:</h3><ul><li>Requirements gathering</li><li>Feature specification</li><li>Database design</li><li>API design</li><li>User interface mockups</li></ul><h3>Tools:</h3><ul><li>Git for version control</li><li>Project management tools</li><li>Design tools</li></ul>',
                            'summary' => 'Plan and design a complete application',
                            'lesson_type' => 'text',
                            'duration_minutes' => 60,
                            'objectives' => 'Create comprehensive project plans and designs'
                        ],
                        [
                            'title' => 'Final Project - Web Application',
                            'content' => '<h2>Build a Complete Web App</h2><p>Combine all your skills to build a complete, deployable web application!</p><h3>Project Requirements:</h3><ul><li>User authentication</li><li>Database integration</li><li>RESTful API</li><li>Frontend interface</li><li>Deployment ready</li></ul><h3>Example Projects:</h3><ul><li>Course management system</li><li>Blog platform</li><li>Task management app</li><li>E-commerce site</li></ul>',
                            'summary' => 'Build a complete, production-ready web application',
                            'lesson_type' => 'assignment',
                            'duration_minutes' => 120,
                            'objectives' => 'Design, build, and deploy a complete web application'
                        ],
                    ]
                ],
            ]
        ];
    }
}
