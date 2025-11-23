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
            $this->getWebDevelopment1Course(), // 3-week structured course
            $this->getWebDevelopmentCourse(), // Advanced web development
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
     * Web Development 1 - 3-Week Camp Course (HTML, CSS, Bootstrap)
     * Structured for code camps with weekly modules
     */
    private function getWebDevelopment1Course(): array
    {
        return [
            'title' => 'Web Development 1',
            'description' => 'A comprehensive 3-week course covering HTML, CSS, and Bootstrap. Perfect for beginners who want to build modern, responsive websites from scratch. This course is structured for code camps with hands-on projects and real-world applications.',
            'short_description' => '3-week course: Master HTML, CSS, and Bootstrap to build responsive websites',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 90, // 3 weeks × 30 hours
            'category' => 'Web Development',
            'tags' => ['html', 'css', 'bootstrap', 'responsive', 'web', 'beginner', '3-weeks'],
            'requirements' => ['No prior experience needed', 'Basic computer skills', 'Text editor (VS Code recommended)', 'Modern web browser'],
            'what_you_learn' => [
                'HTML5 semantic elements and structure',
                'CSS styling, layout, and responsive design',
                'Bootstrap framework for rapid development',
                'Building complete responsive websites',
                'Best practices for web development',
                'Project-based learning with real examples',
                'Deploying websites online'
            ],
            'modules' => [
                // WEEK 1: HTML Foundations
                [
                    'title' => 'Week 1: HTML Foundations',
                    'description' => 'Master HTML5 fundamentals and build your first web pages. Learn semantic HTML, forms, and proper document structure.',
                    'lessons' => [
                        [
                            'title' => 'Introduction to HTML and Web Development',
                            'content' => '<h2>Welcome to Web Development!</h2><p>HTML (HyperText Markup Language) is the foundation of every website. In this lesson, you\'ll learn what HTML is, how it works, and create your first web page.</p><h3>What You\'ll Learn:</h3><ul><li>What is HTML and why it matters</li><li>How websites work</li><li>Setting up your development environment</li><li>Creating your first HTML document</li><li>Understanding the basic structure</li></ul><h3>HTML Document Structure:</h3><pre>&lt;!DOCTYPE html&gt;\n&lt;html lang="en"&gt;\n&lt;head&gt;\n    &lt;meta charset="UTF-8"&gt;\n    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;\n    &lt;title&gt;My First Web Page&lt;/title&gt;\n&lt;/head&gt;\n&lt;body&gt;\n    &lt;h1&gt;Hello, World!&lt;/h1&gt;\n    &lt;p&gt;This is my first web page.&lt;/p&gt;\n&lt;/body&gt;\n&lt;/html&gt;</pre><h3>Key Concepts:</h3><ul><li><strong>DOCTYPE</strong>: Tells the browser this is an HTML5 document</li><li><strong>html</strong>: Root element of the page</li><li><strong>head</strong>: Contains metadata (not visible on page)</li><li><strong>body</strong>: Contains visible content</li></ul><h3>Practice Exercise:</h3><p>Create a simple HTML page with your name, a heading, and a paragraph about yourself.</p>',
                            'summary' => 'Get started with HTML and create your first web page',
                            'lesson_type' => 'text',
                            'duration_minutes' => 45,
                            'objectives' => 'Understand HTML basics, set up development environment, and create your first web page'
                        ],
                        [
                            'title' => 'HTML Elements: Headings, Paragraphs, and Text Formatting',
                            'content' => '<h2>Working with Text in HTML</h2><p>Learn how to structure text content using headings, paragraphs, and text formatting elements.</p><h3>Headings (h1 to h6):</h3><pre>&lt;h1&gt;Main Heading (Most Important)&lt;/h1&gt;\n&lt;h2&gt;Subheading&lt;/h2&gt;\n&lt;h3&gt;Section Heading&lt;/h3&gt;\n&lt;h4&gt;Subsection Heading&lt;/h4&gt;\n&lt;h5&gt;Minor Heading&lt;/h5&gt;\n&lt;h6&gt;Least Important Heading&lt;/h6&gt;</pre><h3>Text Formatting:</h3><ul><li><strong>&lt;strong&gt;</strong> - Bold, important text</li><li><strong>&lt;em&gt;</strong> - Italic, emphasized text</li><li><strong>&lt;mark&gt;</strong> - Highlighted text</li><li><strong>&lt;small&gt;</strong> - Smaller text</li><li><strong>&lt;del&gt;</strong> - Deleted text</li><li><strong>&lt;ins&gt;</strong> - Inserted text</li><li><strong>&lt;sub&gt;</strong> - Subscript</li><li><strong>&lt;sup&gt;</strong> - Superscript</li></ul><h3>Lists:</h3><pre>&lt;!-- Unordered List --&gt;\n&lt;ul&gt;\n    &lt;li&gt;Item 1&lt;/li&gt;\n    &lt;li&gt;Item 2&lt;/li&gt;\n&lt;/ul&gt;\n\n&lt;!-- Ordered List --&gt;\n&lt;ol&gt;\n    &lt;li&gt;First step&lt;/li&gt;\n    &lt;li&gt;Second step&lt;/li&gt;\n&lt;/ol&gt;</pre><h3>Practice Exercise:</h3><p>Create a page with different heading levels, formatted text, and both ordered and unordered lists.</p>',
                            'summary' => 'Master text elements: headings, paragraphs, and formatting',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 50,
                            'objectives' => 'Use headings, paragraphs, lists, and text formatting to structure content'
                        ],
                        [
                            'title' => 'Links, Images, and Media',
                            'content' => '<h2>Adding Links and Images</h2><p>Learn how to add links to other pages, images, and media to make your pages interactive and visually appealing.</p><h3>Links (Anchor Tags):</h3><pre>&lt;!-- External Link --&gt;\n&lt;a href="https://example.com"&gt;Visit Example&lt;/a&gt;\n\n&lt;!-- Internal Link --&gt;\n&lt;a href="about.html"&gt;About Us&lt;/a&gt;\n\n&lt;!-- Link with Target --&gt;\n&lt;a href="https://example.com" target="_blank"&gt;Open in New Tab&lt;/a&gt;\n\n&lt;!-- Email Link --&gt;\n&lt;a href="mailto:contact@example.com"&gt;Contact Us&lt;/a&gt;</pre><h3>Images:</h3><pre>&lt;!-- Basic Image --&gt;\n&lt;img src="image.jpg" alt="Description of image"&gt;\n\n&lt;!-- Image with Size --&gt;\n&lt;img src="photo.png" alt="Photo" width="300" height="200"&gt;\n\n&lt;!-- Responsive Image --&gt;\n&lt;img src="image.jpg" alt="Description" style="max-width: 100%; height: auto;"&gt;</pre><h3>Best Practices:</h3><ul><li>Always use <strong>alt</strong> attributes for images (accessibility)</li><li>Use descriptive link text</li><li>Optimize images for web (compression)</li><li>Use relative paths for internal resources</li></ul><h3>Practice Exercise:</h3><p>Create a page with multiple links (internal, external, email) and several images with proper alt text.</p>',
                            'summary' => 'Add links, images, and media to your web pages',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Create links, embed images, and understand best practices for media'
                        ],
                        [
                            'title' => 'HTML Forms and Input Elements',
                            'content' => '<h2>Creating Interactive Forms</h2><p>Forms allow users to input data. Learn how to create functional forms with various input types.</p><h3>Basic Form Structure:</h3><pre>&lt;form action="/submit" method="POST"&gt;\n    &lt;label for="name"&gt;Name:&lt;/label&gt;\n    &lt;input type="text" id="name" name="name" required&gt;\n    \n    &lt;label for="email"&gt;Email:&lt;/label&gt;\n    &lt;input type="email" id="email" name="email" required&gt;\n    \n    &lt;button type="submit"&gt;Submit&lt;/button&gt;\n&lt;/form&gt;</pre><h3>Input Types:</h3><ul><li><strong>text</strong> - Single-line text input</li><li><strong>email</strong> - Email validation</li><li><strong>password</strong> - Password field (hidden)</li><li><strong>number</strong> - Numeric input</li><li><strong>date</strong> - Date picker</li><li><strong>checkbox</strong> - Checkboxes</li><li><strong>radio</strong> - Radio buttons</li><li><strong>textarea</strong> - Multi-line text</li><li><strong>select</strong> - Dropdown menu</li></ul><h3>Form Attributes:</h3><ul><li><strong>required</strong> - Field must be filled</li><li><strong>placeholder</strong> - Hint text</li><li><strong>pattern</strong> - Validation pattern</li><li><strong>min/max</strong> - Value constraints</li></ul><h3>Practice Exercise:</h3><p>Create a contact form with name, email, message, and a submit button.</p>',
                            'summary' => 'Build interactive forms with various input types',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Create functional HTML forms with proper input types and validation'
                        ],
                        [
                            'title' => 'HTML5 Semantic Elements',
                            'content' => '<h2>Modern HTML5 Semantic Elements</h2><p>Semantic HTML uses elements that clearly describe their meaning, improving accessibility and SEO.</p><h3>Semantic Elements:</h3><pre>&lt;header&gt;\n    &lt;h1&gt;Website Title&lt;/h1&gt;\n    &lt;nav&gt;Navigation links&lt;/nav&gt;\n&lt;/header&gt;\n\n&lt;main&gt;\n    &lt;article&gt;\n        &lt;h2&gt;Article Title&lt;/h2&gt;\n        &lt;p&gt;Article content...&lt;/p&gt;\n    &lt;/article&gt;\n    \n    &lt;section&gt;\n        &lt;h2&gt;Section Title&lt;/h2&gt;\n        &lt;p&gt;Section content...&lt;/p&gt;\n    &lt;/section&gt;\n&lt;/main&gt;\n\n&lt;aside&gt;\n    &lt;h3&gt;Sidebar Content&lt;/h3&gt;\n&lt;/aside&gt;\n\n&lt;footer&gt;\n    &lt;p&gt;Copyright &copy; 2024&lt;/p&gt;\n&lt;/footer&gt;</pre><h3>Benefits:</h3><ul><li>Better accessibility for screen readers</li><li>Improved SEO (Search Engine Optimization)</li><li>Clearer code structure</li><li>Easier maintenance</li></ul><h3>Common Semantic Elements:</h3><ul><li><strong>&lt;header&gt;</strong> - Page or section header</li><li><strong>&lt;nav&gt;</strong> - Navigation links</li><li><strong>&lt;main&gt;</strong> - Main content</li><li><strong>&lt;article&gt;</strong> - Independent content</li><li><strong>&lt;section&gt;</strong> - Thematic grouping</li><li><strong>&lt;aside&gt;</strong> - Sidebar content</li><li><strong>&lt;footer&gt;</strong> - Page footer</li></ul><h3>Practice Exercise:</h3><p>Restructure a webpage using semantic HTML5 elements.</p>',
                            'summary' => 'Use semantic HTML5 elements for better structure and accessibility',
                            'lesson_type' => 'text',
                            'duration_minutes' => 50,
                            'objectives' => 'Implement semantic HTML5 elements to create well-structured, accessible pages'
                        ],
                        [
                            'title' => 'Week 1 Project: Build a Personal Portfolio Page',
                            'content' => '<h2>Your First Complete Website Project</h2><p>Combine everything you\'ve learned in Week 1 to build a complete personal portfolio page.</p><h3>Project Requirements:</h3><ul><li>Use semantic HTML5 structure (header, nav, main, footer)</li><li>Include an "About Me" section with headings and paragraphs</li><li>Add a photo of yourself</li><li>Create a contact form</li><li>Add links to social media or projects</li><li>Use proper headings hierarchy (h1, h2, h3)</li><li>Include lists (skills, hobbies, etc.)</li></ul><h3>Structure:</h3><pre>&lt;header&gt;\n    &lt;h1&gt;Your Name&lt;/h1&gt;\n    &lt;nav&gt;...&lt;/nav&gt;\n&lt;/header&gt;\n&lt;main&gt;\n    &lt;section id="about"&gt;...&lt;/section&gt;\n    &lt;section id="skills"&gt;...&lt;/section&gt;\n    &lt;section id="contact"&gt;...&lt;/section&gt;\n&lt;/main&gt;\n&lt;footer&gt;...&lt;/footer&gt;</pre><h3>Tips:</h3><ul><li>Plan your structure before coding</li><li>Use comments to organize sections</li><li>Test in different browsers</li><li>Validate your HTML</li></ul>',
                            'summary' => 'Build a complete personal portfolio page using all HTML concepts',
                            'lesson_type' => 'assignment',
                            'duration_minutes' => 90,
                            'objectives' => 'Create a complete, well-structured HTML portfolio page demonstrating all learned concepts'
                        ],
                    ]
                ],
                // WEEK 2: CSS Styling
                [
                    'title' => 'Week 2: CSS Styling and Layout',
                    'description' => 'Master CSS to style your HTML pages. Learn colors, typography, layout techniques, and responsive design.',
                    'lessons' => [
                        [
                            'title' => 'Introduction to CSS',
                            'content' => '<h2>What is CSS?</h2><p>CSS (Cascading Style Sheets) controls the visual appearance of your HTML pages. Learn how to add styles and make your pages beautiful.</p><h3>Three Ways to Add CSS:</h3><pre>&lt;!-- 1. Inline CSS --&gt;\n&lt;p style="color: blue;"&gt;Blue text&lt;/p&gt;\n\n&lt;!-- 2. Internal CSS (in &lt;head&gt;) --&gt;\n&lt;style&gt;\n    p { color: blue; }\n&lt;/style&gt;\n\n&lt;!-- 3. External CSS (Recommended) --&gt;\n&lt;link rel="stylesheet" href="styles.css"&gt;</pre><h3>CSS Syntax:</h3><pre>selector {\n    property: value;\n    property: value;\n}\n\n/* Example */\nh1 {\n    color: blue;\n    font-size: 32px;\n    text-align: center;\n}</pre><h3>Selectors:</h3><ul><li><strong>Element</strong>: <code>p { }</code> - All paragraphs</li><li><strong>Class</strong>: <code>.my-class { }</code> - Elements with class</li><li><strong>ID</strong>: <code>#my-id { }</code> - Element with ID</li><li><strong>Descendant</strong>: <code>div p { }</code> - Paragraphs inside divs</li></ul><h3>Practice Exercise:</h3><p>Create an external CSS file and style your HTML page with colors, fonts, and spacing.</p>',
                            'summary' => 'Learn CSS basics: syntax, selectors, and how to add styles',
                            'lesson_type' => 'text',
                            'duration_minutes' => 50,
                            'objectives' => 'Understand CSS syntax, selectors, and how to apply styles to HTML elements'
                        ],
                        [
                            'title' => 'Colors, Typography, and Text Styling',
                            'content' => '<h2>Styling Text and Colors</h2><p>Learn how to use colors, fonts, and text properties to create visually appealing content.</p><h3>Colors in CSS:</h3><pre>/* Named Colors */\ncolor: red;\ncolor: blue;\n\n/* Hex Colors */\ncolor: #FF5733;\ncolor: #33FF57;\n\n/* RGB Colors */\ncolor: rgb(255, 87, 51);\ncolor: rgba(255, 87, 51, 0.5); /* with transparency */</pre><h3>Typography:</h3><pre>font-family: "Arial", sans-serif;\nfont-size: 16px;\nfont-weight: bold; /* normal, bold, 100-900 */\nfont-style: italic;\nline-height: 1.6;\ntext-align: center; /* left, right, center, justify */\ntext-decoration: underline; /* none, underline, line-through */</pre><h3>Text Properties:</h3><pre>text-transform: uppercase; /* lowercase, capitalize */\nletter-spacing: 2px;\nword-spacing: 5px;\ntext-shadow: 2px 2px 4px rgba(0,0,0,0.3);</pre><h3>Google Fonts:</h3><pre>&lt;!-- In HTML head --&gt;\n&lt;link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"&gt;\n\n/* In CSS */\nfont-family: \'Roboto\', sans-serif;</pre><h3>Practice Exercise:</h3><p>Style a page with custom fonts, colors, and text effects.</p>',
                            'summary' => 'Master colors, fonts, and text styling in CSS',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 55,
                            'objectives' => 'Apply colors, typography, and text styling to create visually appealing content'
                        ],
                        [
                            'title' => 'CSS Box Model: Margin, Padding, and Borders',
                            'content' => '<h2>Understanding the Box Model</h2><p>Every HTML element is a box. Understanding margin, padding, and borders is crucial for layout.</p><h3>The Box Model:</h3><pre>┌─────────────────────────┐\n│      Margin             │\n│  ┌───────────────────┐ │\n│  │     Border          │ │\n│  │  ┌───────────────┐ │ │\n│  │  │   Padding     │ │ │\n│  │  │  ┌─────────┐  │ │ │\n│  │  │  │ Content │  │ │ │\n│  │  │  └─────────┘  │ │ │\n│  │  └───────────────┘ │ │\n│  └───────────────────┘ │\n└─────────────────────────┘</pre><h3>Properties:</h3><pre>/* Padding - Space inside element */\npadding: 20px; /* all sides */\npadding: 10px 20px; /* top/bottom left/right */\npadding: 10px 20px 15px 25px; /* top right bottom left */\n\n/* Margin - Space outside element */\nmargin: 20px;\nmargin: 10px auto; /* center horizontally */\n\n/* Border */\nborder: 2px solid black;\nborder-radius: 10px; /* rounded corners */</pre><h3>Box-Sizing:</h3><pre>/* Default - width includes padding and border */\nbox-sizing: content-box;\n\n/* Better - width is total width */\nbox-sizing: border-box;</pre><h3>Practice Exercise:</h3><p>Create cards with different padding, margins, and borders.</p>',
                            'summary' => 'Master the CSS box model: margin, padding, and borders',
                            'lesson_type' => 'video',
                            'duration_minutes' => 60,
                            'objectives' => 'Understand and apply the box model to control spacing and layout'
                        ],
                        [
                            'title' => 'CSS Flexbox Layout',
                            'content' => '<h2>Flexbox for Modern Layouts</h2><p>Flexbox makes it easy to create flexible, responsive layouts. It\'s perfect for navigation bars, cards, and centering content.</p><h3>Flexbox Container:</h3><pre>.container {\n    display: flex;\n    flex-direction: row; /* row, column, row-reverse, column-reverse */\n    justify-content: center; /* flex-start, flex-end, center, space-between, space-around */\n    align-items: center; /* flex-start, flex-end, center, stretch, baseline */\n    flex-wrap: wrap; /* nowrap, wrap, wrap-reverse */\n    gap: 20px; /* space between items */\n}</pre><h3>Flexbox Items:</h3><pre>.item {\n    flex: 1; /* grow, shrink, basis */\n    flex-grow: 1;\n    flex-shrink: 1;\n    flex-basis: 200px;\n    align-self: center; /* override container alignment */\n}</pre><h3>Common Patterns:</h3><pre>/* Centering Content */\n.container {\n    display: flex;\n    justify-content: center;\n    align-items: center;\n    height: 100vh;\n}\n\n/* Navigation Bar */\n.nav {\n    display: flex;\n    justify-content: space-between;\n    align-items: center;\n}\n\n/* Card Layout */\n.cards {\n    display: flex;\n    flex-wrap: wrap;\n    gap: 20px;\n}</pre><h3>Practice Exercise:</h3><p>Create a navigation bar, card layout, and centered content using Flexbox.</p>',
                            'summary' => 'Master Flexbox for creating flexible, responsive layouts',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 65,
                            'objectives' => 'Use Flexbox to create modern, responsive layouts'
                        ],
                        [
                            'title' => 'CSS Grid Layout',
                            'content' => '<h2>Grid for Complex Layouts</h2><p>CSS Grid is a powerful 2D layout system perfect for complex page layouts with rows and columns.</p><h3>Basic Grid:</h3><pre>.container {\n    display: grid;\n    grid-template-columns: 1fr 1fr 1fr; /* 3 equal columns */\n    grid-template-rows: auto;\n    gap: 20px;\n}</pre><h3>Grid Areas:</h3><pre>.container {\n    display: grid;\n    grid-template-areas:\n        "header header header"\n        "sidebar main main"\n        "footer footer footer";\n    grid-template-columns: 200px 1fr 1fr;\n    grid-template-rows: auto 1fr auto;\n    gap: 20px;\n}\n\n.header { grid-area: header; }\n.sidebar { grid-area: sidebar; }\n.main { grid-area: main; }\n.footer { grid-area: footer; }</pre><h3>Responsive Grid:</h3><pre>/* Auto-fit columns */\n.container {\n    display: grid;\n    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));\n    gap: 20px;\n}</pre><h3>Practice Exercise:</h3><p>Create a complete page layout using CSS Grid with header, sidebar, main content, and footer.</p>',
                            'summary' => 'Create complex layouts with CSS Grid',
                            'lesson_type' => 'video',
                            'duration_minutes' => 70,
                            'objectives' => 'Build complex, responsive layouts using CSS Grid'
                        ],
                        [
                            'title' => 'Responsive Web Design with Media Queries',
                            'content' => '<h2>Making Websites Work on All Devices</h2><p>Responsive design ensures your website looks great on phones, tablets, and desktops.</p><h3>Media Queries:</h3><pre>/* Mobile First Approach */\n.container {\n    width: 100%;\n    padding: 10px;\n}\n\n/* Tablet */\n@media (min-width: 768px) {\n    .container {\n        width: 750px;\n        margin: 0 auto;\n    }\n}\n\n/* Desktop */\n@media (min-width: 1024px) {\n    .container {\n        width: 1200px;\n    }\n}</pre><h3>Common Breakpoints:</h3><pre>/* Mobile: up to 767px */\n/* Tablet: 768px - 1023px */\n/* Desktop: 1024px and above */\n\n@media (max-width: 767px) { /* Mobile styles */ }\n@media (min-width: 768px) and (max-width: 1023px) { /* Tablet */ }\n@media (min-width: 1024px) { /* Desktop */ }</pre><h3>Responsive Images:</h3><pre>img {\n    max-width: 100%;\n    height: auto;\n}</pre><h3>Viewport Meta Tag:</h3><pre>&lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;</pre><h3>Practice Exercise:</h3><p>Make your portfolio page responsive for mobile, tablet, and desktop.</p>',
                            'summary' => 'Create responsive websites that work on all devices',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 65,
                            'objectives' => 'Implement responsive design using media queries'
                        ],
                        [
                            'title' => 'Week 2 Project: Style Your Portfolio with CSS',
                            'content' => '<h2>Transform Your Portfolio with CSS</h2><p>Apply all CSS concepts to style your Week 1 portfolio page beautifully.</p><h3>Project Requirements:</h3><ul><li>Create an external CSS file</li><li>Use a color scheme (choose 2-3 main colors)</li><li>Style typography with Google Fonts</li><li>Use Flexbox for navigation and card layouts</li><li>Use CSS Grid for main layout (optional)</li><li>Make it fully responsive (mobile, tablet, desktop)</li><li>Add hover effects on links and buttons</li><li>Use box model properly (margin, padding, borders)</li></ul><h3>Design Tips:</h3><ul><li>Keep it simple and clean</li><li>Use consistent spacing</li><li>Choose readable fonts</li><li>Ensure good contrast for text</li><li>Test on different screen sizes</li></ul><h3>Advanced Features (Optional):</h3><ul><li>CSS animations</li><li>Transitions on hover</li><li>Box shadows</li><li>Gradient backgrounds</li></ul>',
                            'summary' => 'Style your portfolio page with CSS, making it responsive and beautiful',
                            'lesson_type' => 'assignment',
                            'duration_minutes' => 90,
                            'objectives' => 'Apply all CSS concepts to create a fully styled, responsive portfolio'
                        ],
                    ]
                ],
                // WEEK 3: Bootstrap Framework
                [
                    'title' => 'Week 3: Bootstrap Framework',
                    'description' => 'Learn Bootstrap to build professional, responsive websites quickly. Master components, utilities, and the grid system.',
                    'lessons' => [
                        [
                            'title' => 'Getting Started with Bootstrap',
                            'content' => '<h2>Introduction to Bootstrap</h2><p>Bootstrap is the most popular CSS framework. It provides pre-built components and utilities to build responsive websites faster.</p><h3>Why Bootstrap?</h3><ul><li>Responsive grid system</li><li>Pre-styled components</li><li>Utility classes</li><li>Cross-browser compatibility</li><li>Mobile-first approach</li></ul><h3>Installation:</h3><pre>&lt;!-- CDN Method (Recommended for learning) --&gt;\n&lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"&gt;\n&lt;script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;</pre><h3>Bootstrap Grid System:</h3><pre>&lt;div class="container"&gt;\n    &lt;div class="row"&gt;\n        &lt;div class="col-md-6"&gt;Column 1&lt;/div&gt;\n        &lt;div class="col-md-6"&gt;Column 2&lt;/div&gt;\n    &lt;/div&gt;\n&lt;/div&gt;</pre><h3>Breakpoints:</h3><ul><li><strong>xs</strong>: &lt;576px (default, no prefix)</li><li><strong>sm</strong>: ≥576px</li><li><strong>md</strong>: ≥768px</li><li><strong>lg</strong>: ≥992px</li><li><strong>xl</strong>: ≥1200px</li><li><strong>xxl</strong>: ≥1400px</li></ul><h3>Practice Exercise:</h3><p>Set up Bootstrap and create a simple grid layout.</p>',
                            'summary' => 'Get started with Bootstrap: installation and grid system',
                            'lesson_type' => 'text',
                            'duration_minutes' => 50,
                            'objectives' => 'Install Bootstrap and understand the grid system'
                        ],
                        [
                            'title' => 'Bootstrap Components: Buttons, Cards, and Forms',
                            'content' => '<h2>Using Bootstrap Components</h2><p>Bootstrap provides ready-to-use components that look professional and are fully responsive.</p><h3>Buttons:</h3><pre>&lt;button class="btn btn-primary"&gt;Primary&lt;/button&gt;\n&lt;button class="btn btn-secondary"&gt;Secondary&lt;/button&gt;\n&lt;button class="btn btn-success"&gt;Success&lt;/button&gt;\n&lt;button class="btn btn-danger"&gt;Danger&lt;/button&gt;\n&lt;button class="btn btn-outline-primary"&gt;Outline&lt;/button&gt;\n&lt;button class="btn btn-lg"&gt;Large Button&lt;/button&gt;</pre><h3>Cards:</h3><pre>&lt;div class="card" style="width: 18rem;"&gt;\n    &lt;img src="image.jpg" class="card-img-top" alt="..."&gt;\n    &lt;div class="card-body"&gt;\n        &lt;h5 class="card-title"&gt;Card Title&lt;/h5&gt;\n        &lt;p class="card-text"&gt;Card content goes here.&lt;/p&gt;\n        &lt;a href="#" class="btn btn-primary"&gt;Go somewhere&lt;/a&gt;\n    &lt;/div&gt;\n&lt;/div&gt;</pre><h3>Forms:</h3><pre>&lt;form&gt;\n    &lt;div class="mb-3"&gt;\n        &lt;label for="email" class="form-label"&gt;Email&lt;/label&gt;\n        &lt;input type="email" class="form-control" id="email"&gt;\n    &lt;/div&gt;\n    &lt;div class="mb-3"&gt;\n        &lt;label for="password" class="form-label"&gt;Password&lt;/label&gt;\n        &lt;input type="password" class="form-control" id="password"&gt;\n    &lt;/div&gt;\n    &lt;button type="submit" class="btn btn-primary"&gt;Submit&lt;/button&gt;\n&lt;/form&gt;</pre><h3>Practice Exercise:</h3><p>Create a page with Bootstrap buttons, cards, and a styled form.</p>',
                            'summary' => 'Use Bootstrap components: buttons, cards, and forms',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 60,
                            'objectives' => 'Implement Bootstrap components to build professional interfaces'
                        ],
                        [
                            'title' => 'Bootstrap Navigation and Navbar',
                            'content' => '<h2>Creating Navigation with Bootstrap</h2><p>Learn how to create responsive navigation bars and navigation components.</p><h3>Basic Navbar:</h3><pre>&lt;nav class="navbar navbar-expand-lg navbar-light bg-light"&gt;\n    &lt;div class="container-fluid"&gt;\n        &lt;a class="navbar-brand" href="#"&gt;My Website&lt;/a&gt;\n        &lt;button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"&gt;\n            &lt;span class="navbar-toggler-icon"&gt;&lt;/span&gt;\n        &lt;/button&gt;\n        &lt;div class="collapse navbar-collapse" id="navbarNav"&gt;\n            &lt;ul class="navbar-nav"&gt;\n                &lt;li class="nav-item"&gt;\n                    &lt;a class="nav-link active" href="#"&gt;Home&lt;/a&gt;\n                &lt;/li&gt;\n                &lt;li class="nav-item"&gt;\n                    &lt;a class="nav-link" href="#"&gt;About&lt;/a&gt;\n                &lt;/li&gt;\n                &lt;li class="nav-item"&gt;\n                    &lt;a class="nav-link" href="#"&gt;Contact&lt;/a&gt;\n                &lt;/li&gt;\n            &lt;/ul&gt;\n        &lt;/div&gt;\n    &lt;/div&gt;\n&lt;/nav&gt;</pre><h3>Navbar Variants:</h3><pre>&lt;nav class="navbar navbar-dark bg-dark"&gt;...&lt;/nav&gt;\n&lt;nav class="navbar navbar-light bg-light"&gt;...&lt;/nav&gt;\n&lt;nav class="navbar navbar-dark bg-primary"&gt;...&lt;/nav&gt;</pre><h3>Nav Components:</h3><pre>&lt;!-- Tabs --&gt;\n&lt;ul class="nav nav-tabs"&gt;\n    &lt;li class="nav-item"&gt;\n        &lt;a class="nav-link active" href="#"&gt;Tab 1&lt;/a&gt;\n    &lt;/li&gt;\n    &lt;li class="nav-item"&gt;\n        &lt;a class="nav-link" href="#"&gt;Tab 2&lt;/a&gt;\n    &lt;/li&gt;\n&lt;/ul&gt;\n\n&lt;!-- Pills --&gt;\n&lt;ul class="nav nav-pills"&gt;...&lt;/ul&gt;</pre><h3>Practice Exercise:</h3><p>Create a responsive navbar with a logo, menu items, and a mobile hamburger menu.</p>',
                            'summary' => 'Create responsive navigation bars with Bootstrap',
                            'lesson_type' => 'video',
                            'duration_minutes' => 55,
                            'objectives' => 'Build responsive navigation components using Bootstrap'
                        ],
                        [
                            'title' => 'Bootstrap Utilities and Helpers',
                            'content' => '<h2>Bootstrap Utility Classes</h2><p>Bootstrap provides utility classes for spacing, colors, display, and more. These save time and keep your code clean.</p><h3>Spacing Utilities:</h3><pre>&lt;div class="m-3"&gt;Margin all sides&lt;/div&gt;\n&lt;div class="mt-2"&gt;Margin top&lt;/div&gt;\n&lt;div class="mb-4"&gt;Margin bottom&lt;/div&gt;\n&lt;div class="p-3"&gt;Padding all sides&lt;/div&gt;\n&lt;div class="px-4"&gt;Padding horizontal&lt;/div&gt;\n&lt;div class="py-2"&gt;Padding vertical&lt;/div&gt;\n\n/* Sizes: 0, 1, 2, 3, 4, 5 */</pre><h3>Text Utilities:</h3><pre>&lt;p class="text-center"&gt;Centered text&lt;/p&gt;\n&lt;p class="text-primary"&gt;Primary color text&lt;/p&gt;\n&lt;p class="fw-bold"&gt;Bold text&lt;/p&gt;\n&lt;p class="text-uppercase"&gt;Uppercase text&lt;/p&gt;\n&lt;p class="text-muted"&gt;Muted text&lt;/p&gt;</pre><h3>Display Utilities:</h3><pre>&lt;div class="d-none"&gt;Hidden&lt;/div&gt;\n&lt;div class="d-block"&gt;Block&lt;/div&gt;\n&lt;div class="d-flex"&gt;Flexbox&lt;/div&gt;\n&lt;div class="d-md-none"&gt;Hidden on medium+ screens&lt;/div&gt;</pre><h3>Color Utilities:</h3><pre>&lt;div class="bg-primary"&gt;Primary background&lt;/div&gt;\n&lt;div class="bg-light"&gt;Light background&lt;/div&gt;\n&lt;div class="border border-primary"&gt;Primary border&lt;/div&gt;</pre><h3>Practice Exercise:</h3><p>Use utility classes to quickly style a page without writing custom CSS.</p>',
                            'summary' => 'Master Bootstrap utility classes for rapid development',
                            'lesson_type' => 'interactive',
                            'duration_minutes' => 50,
                            'objectives' => 'Use Bootstrap utilities to style pages efficiently'
                        ],
                        [
                            'title' => 'Bootstrap Modals, Alerts, and More Components',
                            'content' => '<h2>Advanced Bootstrap Components</h2><p>Learn more Bootstrap components to create interactive and engaging websites.</p><h3>Modals (Pop-ups):</h3><pre>&lt;button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal"&gt;\n    Open Modal\n&lt;/button&gt;\n\n&lt;div class="modal fade" id="myModal" tabindex="-1"&gt;\n    &lt;div class="modal-dialog"&gt;\n        &lt;div class="modal-content"&gt;\n            &lt;div class="modal-header"&gt;\n                &lt;h5 class="modal-title"&gt;Modal Title&lt;/h5&gt;\n                &lt;button type="button" class="btn-close" data-bs-dismiss="modal"&gt;&lt;/button&gt;\n            &lt;/div&gt;\n            &lt;div class="modal-body"&gt;\n                &lt;p&gt;Modal content...&lt;/p&gt;\n            &lt;/div&gt;\n            &lt;div class="modal-footer"&gt;\n                &lt;button type="button" class="btn btn-secondary" data-bs-dismiss="modal"&gt;Close&lt;/button&gt;\n                &lt;button type="button" class="btn btn-primary"&gt;Save&lt;/button&gt;\n            &lt;/div&gt;\n        &lt;/div&gt;\n    &lt;/div&gt;\n&lt;/div&gt;</pre><h3>Alerts:</h3><pre>&lt;div class="alert alert-success" role="alert"&gt;\n    Success! Your action was completed.\n&lt;/div&gt;\n&lt;div class="alert alert-danger" role="alert"&gt;\n    Error! Something went wrong.\n&lt;/div&gt;\n&lt;div class="alert alert-warning" role="alert"&gt;\n    Warning! Please check your input.\n&lt;/div&gt;</pre><h3>Badges:</h3><pre>&lt;span class="badge bg-primary"&gt;New&lt;/span&gt;\n&lt;span class="badge bg-success"&gt;Active&lt;/span&gt;\n&lt;span class="badge rounded-pill bg-danger"&gt;3&lt;/span&gt;</pre><h3>Progress Bars:</h3><pre>&lt;div class="progress"&gt;\n    &lt;div class="progress-bar" role="progressbar" style="width: 75%"&gt;75%&lt;/div&gt;\n&lt;/div&gt;</pre><h3>Practice Exercise:</h3><p>Add modals, alerts, and other components to your website.</p>',
                            'summary' => 'Use advanced Bootstrap components: modals, alerts, badges, and more',
                            'lesson_type' => 'video',
                            'duration_minutes' => 60,
                            'objectives' => 'Implement interactive Bootstrap components in your projects'
                        ],
                        [
                            'title' => 'Customizing Bootstrap: Themes and Overrides',
                            'content' => '<h2>Making Bootstrap Your Own</h2><p>Learn how to customize Bootstrap to match your brand while keeping its benefits.</p><h3>CSS Variables (Custom Properties):</h3><pre>:root {\n    --bs-primary: #0d6efd;\n    --bs-secondary: #6c757d;\n    --bs-success: #198754;\n    --bs-border-radius: 0.375rem;\n    --bs-font-family: \'Roboto\', sans-serif;\n}</pre><h3>Overriding Bootstrap Styles:</h3><pre>/* Method 1: More Specific Selector */\n.btn-primary {\n    background-color: #ff5733;\n    border-color: #ff5733;\n}\n\n/* Method 2: Using !important (use sparingly) */\n.btn-primary {\n    background-color: #ff5733 !important;\n}\n\n/* Method 3: Custom CSS after Bootstrap */\n&lt;link href="bootstrap.min.css" rel="stylesheet"&gt;\n&lt;link href="custom.css" rel="stylesheet"&gt; /* Load after Bootstrap */</pre><h3>Creating Custom Components:</h3><pre>.my-custom-card {\n    @extend .card;\n    border: 2px solid var(--bs-primary);\n    box-shadow: 0 4px 6px rgba(0,0,0,0.1);\n}</pre><h3>Best Practices:</h3><ul><li>Don\'t modify Bootstrap files directly</li><li>Create a separate custom CSS file</li><li>Use CSS variables when possible</li><li>Keep Bootstrap classes, add your own</li></ul><h3>Practice Exercise:</h3><p>Customize Bootstrap colors and create a custom theme for your website.</p>',
                            'summary' => 'Customize Bootstrap to match your brand and design',
                            'lesson_type' => 'text',
                            'duration_minutes' => 55,
                            'objectives' => 'Customize Bootstrap styles while maintaining its functionality'
                        ],
                        [
                            'title' => 'Week 3 Final Project: Complete Responsive Website with Bootstrap',
                            'content' => '<h2>Your Final Project: A Complete Website</h2><p>Combine HTML, CSS, and Bootstrap to build a complete, professional website.</p><h3>Project Requirements:</h3><ul><li>Use Bootstrap grid system for layout</li><li>Create a responsive navbar with mobile menu</li><li>Build a hero section (jumbotron)</li><li>Create a services/features section with cards</li><li>Add a contact form using Bootstrap forms</li><li>Include a footer with social links</li><li>Make it fully responsive (mobile, tablet, desktop)</li><li>Use Bootstrap components (buttons, cards, modals, alerts)</li><li>Customize Bootstrap colors to match your theme</li><li>Add smooth scrolling and hover effects</li></ul><h3>Suggested Project Ideas:</h3><ul><li>Business landing page</li><li>Portfolio website</li><li>Restaurant website</li><li>Event website</li><li>Product showcase</li></ul><h3>Structure:</h3><pre>&lt;nav&gt;...&lt;/nav&gt;\n&lt;section class="hero"&gt;...&lt;/section&gt;\n&lt;section class="about"&gt;...&lt;/section&gt;\n&lt;section class="services"&gt;...&lt;/section&gt;\n&lt;section class="contact"&gt;...&lt;/section&gt;\n&lt;footer&gt;...&lt;/footer&gt;</pre><h3>Tips for Success:</h3><ul><li>Plan your layout first</li><li>Start with mobile design (mobile-first)</li><li>Test on different devices</li><li>Keep it clean and professional</li><li>Use Bootstrap utilities efficiently</li></ul>',
                            'summary' => 'Build a complete, professional website using HTML, CSS, and Bootstrap',
                            'lesson_type' => 'assignment',
                            'duration_minutes' => 120,
                            'objectives' => 'Create a complete, responsive website demonstrating mastery of all concepts'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Web Development Course (Advanced)
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
