<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReactWeb2LessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = $this->findWeb2Course();

        if (! $course) {
            $this->command->error('Could not find a Web Development 2 course.');
            $this->command->line('Existing course titles:');
            Course::query()->orderBy('title')->pluck('title')->each(function ($title) {
                $this->command->line('  - '.$title);
            });

            return;
        }

        $this->command->info("Using course: {$course->title} (ID {$course->id})");

        $module = CourseModule::updateOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Introduction to React',
            ],
            [
                'description' => 'Ten lessons from your first React component through a small club-directory project. Covers JSX, props, state, lists, forms, useEffect, lifting state, and Bootstrap in React. Follows Web Development 1 (HTML, CSS, Bootstrap) and JavaScript basics.',
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $course->approved_by ?: $course->instructor_id,
            ]
        );

        if (! $module->order_index) {
            $module->order_index = (int) $course->modules()->max('order_index') + 1;
            $module->save();
        }

        foreach ($this->lessons() as $index => $lessonData) {
            $lesson = $this->seedLesson($course, $module, $index, $lessonData);
            $this->seedQuiz($lesson, $lessonData['quiz']);
            $this->command->info("Seeded lesson: {$lesson->title}");
        }

        $this->command->info('React module is ready in '.$course->title.'.');
    }

    private function findWeb2Course(): ?Course
    {
        $exact = [
            'Web Development 2',
            'Web 2 Development',
            'Web2 Development',
            'Web Dev 2',
        ];

        foreach ($exact as $title) {
            $course = Course::where('title', $title)->first();
            if ($course) {
                return $course;
            }
        }

        return Course::query()
            ->where('title', 'like', '%Web%2%')
            ->orWhere('title', 'like', '%web development 2%')
            ->orWhere('slug', 'like', '%web-development-2%')
            ->orderBy('id')
            ->first();
    }

    private function seedLesson(Course $course, CourseModule $module, int $index, array $lessonData): Lesson
    {
        $slug = Str::slug($lessonData['title']);

        return Lesson::updateOrCreate(
            [
                'course_id' => $course->id,
                'slug' => $slug,
            ],
            [
                'module_id' => $module->id,
                'title' => $lessonData['title'],
                'content' => $lessonData['content'],
                'summary' => $lessonData['summary'],
                'objectives' => $lessonData['objectives'],
                'implementation_guidance' => $lessonData['guidance'],
                'lesson_type' => 'text',
                'difficulty_level' => 'intermediate',
                'duration_minutes' => $lessonData['duration'],
                'order' => $index + 1,
                'order_index' => $index + 1,
                'is_published' => true,
                'is_free_preview' => $index === 0,
                'is_locked' => false,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $course->approved_by ?: $course->instructor_id,
            ]
        );
    }

    private function seedQuiz(Lesson $lesson, array $quizData): void
    {
        $assessment = Assessment::updateOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $quizData['title'],
            ],
            [
                'assessment_type' => 'quiz',
                'description' => $quizData['description'],
                'max_attempts' => 3,
                'time_limit_minutes' => 25,
                'passing_score' => 70,
                'xp_reward' => 50,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => false,
                'allow_review' => true,
                'is_randomized' => false,
                'shuffle_options' => true,
                'is_locked' => false,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        Question::where('assessment_id', $assessment->id)->each(function (Question $question) {
            $question->options()->delete();
            $question->delete();
        });

        foreach (array_slice($quizData['questions'], 0, 10) as $index => $questionData) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order' => $index,
                'explanation' => null,
            ]);

            foreach ($questionData['options'] as $optIndex => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $optIndex,
                ]);
            }
        }
    }

    private function lessons(): array
    {
        return [
            [
                'title' => 'What is React and Why We Use It',
                'summary' => 'See how React differs from plain HTML and JavaScript, and set up your first component.',
                'duration' => 40,
                'objectives' => "Explain what React is and why teams use it\nCompare a React component with a plain HTML page\nCreate a simple function component\nRender that component on the page",
                'guidance' => 'Have students compare a static HTML page from Web Development 1 with a React component. Use the Code Academy laptops with VS Code and a browser. Do not skip the mental model: UI = function of data.',
                'content' => <<<'HTML'
<h2>From HTML pages to React apps</h2>
<p>In Web Development 1 you built pages with HTML, CSS, and Bootstrap. The browser showed whatever you wrote in the file. If a student name changed, you had to edit the HTML by hand.</p>
<p><strong>React</strong> is a JavaScript library for building user interfaces. Instead of rewriting HTML every time data changes, you write <em>components</em> — small functions that describe the UI. When data changes, React updates the screen for you.</p>

<h3>The big idea</h3>
<pre>UI = f(data)

If data changes, React calls your function again
and updates only the parts of the page that need to change.</pre>

<h3>A first component</h3>
<pre>function Welcome() {
  return &lt;h1&gt;Hello, Code Academy!&lt;/h1&gt;;
}</pre>
<p><code>Welcome</code> is a <strong>function component</strong>. It returns JSX (HTML-like syntax inside JavaScript). You use it like a custom tag:</p>
<pre>function App() {
  return (
    &lt;main&gt;
      &lt;Welcome /&gt;
    &lt;/main&gt;
  );
}</pre>

<h3>Why camps use React</h3>
<ul>
  <li>Reuse one button, card, or navbar instead of copying HTML</li>
  <li>Keep JavaScript and markup together</li>
  <li>Build interactive UIs (counters, forms, quizzes) without messy DOM code</li>
  <li>It is the same library used by many real products</li>
</ul>

<h3>What you still need</h3>
<p>React does not replace HTML or CSS. You still use tags, classes, and Bootstrap (or Tailwind) inside JSX. JavaScript skills from Web 2 still apply: variables, functions, arrays, and events.</p>

<h3>Try this</h3>
<ol>
  <li>Create a component called <code>CampBanner</code> that returns a heading with your camp name.</li>
  <li>Render it twice on the page. Notice you write the heading once.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'What is React? — Quiz',
                    'description' => 'Check that you understand why we use React and what a component is.',
                    'questions' => [
                        [
                            'question_text' => 'What is React?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A JavaScript library for building user interfaces', 'is_correct' => true],
                                ['option_text' => 'A replacement for HTML that browsers read instead of .html files', 'is_correct' => false],
                                ['option_text' => 'A CSS framework like Bootstrap', 'is_correct' => false],
                                ['option_text' => 'A database for storing student records', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'In React, UI = f(data) means:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'The screen is produced from data; when data changes, the UI updates', 'is_correct' => true],
                                ['option_text' => 'You must refresh the browser after every JavaScript change', 'is_correct' => false],
                                ['option_text' => 'CSS functions replace HTML', 'is_correct' => false],
                                ['option_text' => 'React only runs on the server', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A function component is:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A JavaScript function that returns JSX', 'is_correct' => true],
                                ['option_text' => 'A CSS class that styles a page', 'is_correct' => false],
                                ['option_text' => 'An HTML file inside the public folder', 'is_correct' => false],
                                ['option_text' => 'A MySQL table', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: React replaces the need to learn HTML and CSS.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'Which is a valid way to use a component called Welcome?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => '<Welcome />', 'is_correct' => true],
                                ['option_text' => '<welcome.html>', 'is_correct' => false],
                                ['option_text' => 'include Welcome.css', 'is_correct' => false],
                                ['option_text' => 'SELECT * FROM welcome', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What language do you write React components in?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'JavaScript (with JSX)', 'is_correct' => true],
                                ['option_text' => 'PHP', 'is_correct' => false],
                                ['option_text' => 'SQL', 'is_correct' => false],
                                ['option_text' => 'Python', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: A component should usually do one job, like a banner or a card.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: React is mainly used so you can update the page without rewriting HTML files by hand.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which of these do you still need when using React?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'HTML tags, CSS, and JavaScript', 'is_correct' => true],
                                ['option_text' => 'Only MySQL', 'is_correct' => false],
                                ['option_text' => 'Only Photoshop', 'is_correct' => false],
                                ['option_text' => 'Nothing — React writes the whole website for you', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'To show a component on the screen you should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Return JSX from the component and render it in App', 'is_correct' => true],
                                ['option_text' => 'Save it as a .docx file', 'is_correct' => false],
                                ['option_text' => 'Upload it to Gmail', 'is_correct' => false],
                                ['option_text' => 'Put it only in a CSS comment', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'JSX, Components, and Props',
                'summary' => 'Write JSX correctly and pass data into components with props.',
                'duration' => 45,
                'objectives' => "Write valid JSX with one parent element\nCreate reusable components\nPass props into a child component\nDisplay lists with map() and a key",
                'guidance' => 'Watch for class vs className and for wrapping adjacent tags in a parent. Have students build a StudentCard that receives name and club as props.',
                'content' => <<<'HTML'
<h2>JSX looks like HTML, but it is JavaScript</h2>
<p>JSX lets you write tags inside JS files. The build tool turns JSX into <code>React.createElement</code> calls. A few rules are different from HTML:</p>
<ul>
  <li>Use <code>className</code> instead of <code>class</code></li>
  <li>Use <code>htmlFor</code> instead of <code>for</code> on labels</li>
  <li>All tags must be closed: <code>&lt;img /&gt;</code>, <code>&lt;br /&gt;</code></li>
  <li>A component must return <strong>one parent</strong> (or a fragment <code>&lt;&gt;...&lt;/&gt;</code>)</li>
</ul>

<h3>Inserting JavaScript</h3>
<p>Curly braces evaluate JavaScript inside JSX:</p>
<pre>function Greeting({ name }) {
  return &lt;p&gt;Hello, {name}!&lt;/p&gt;;
}</pre>

<h3>Props are inputs</h3>
<p>Props are like HTML attributes, but they pass data into your component:</p>
<pre>function StudentCard({ name, club }) {
  return (
    &lt;article className="card"&gt;
      &lt;h3&gt;{name}&lt;/h3&gt;
      &lt;p&gt;Club: {club}&lt;/p&gt;
    &lt;/article&gt;
  );
}

function App() {
  return (
    &lt;section&gt;
      &lt;StudentCard name="Amina" club="Scratch" /&gt;
      &lt;StudentCard name="Joel" club="Web" /&gt;
    &lt;/section&gt;
  );
}</pre>
<p>The parent chooses the data. The child only displays it. Do not change props inside the child — treat them as read-only.</p>

<h3>Lists</h3>
<pre>const clubs = ['Scratch', 'Web', 'Robotics'];

function ClubList() {
  return (
    &lt;ul&gt;
      {clubs.map((club) =&gt; (
        &lt;li key={club}&gt;{club}&lt;/li&gt;
      ))}
    &lt;/ul&gt;
  );
}</pre>
<p>Every item in a list needs a <code>key</code> so React can track which row changed.</p>

<h3>Try this</h3>
<ol>
  <li>Build <code>StudentCard</code> with props <code>name</code> and <code>track</code>.</li>
  <li>Render three cards from an array using <code>map</code>.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'JSX and Props — Quiz',
                    'description' => 'Test JSX rules, props, and rendering lists.',
                    'questions' => [
                        [
                            'question_text' => 'In JSX, which attribute sets a CSS class?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'className', 'is_correct' => true],
                                ['option_text' => 'class', 'is_correct' => false],
                                ['option_text' => 'css', 'is_correct' => false],
                                ['option_text' => 'styleClass', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What are props?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Data passed from a parent component into a child', 'is_correct' => true],
                                ['option_text' => 'CSS files imported into index.html', 'is_correct' => false],
                                ['option_text' => 'Secret environment variables', 'is_correct' => false],
                                ['option_text' => 'SQL columns on the users table', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why does each item in a React list need a key?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'So React can track which item changed', 'is_correct' => true],
                                ['option_text' => 'So the browser can apply Bootstrap', 'is_correct' => false],
                                ['option_text' => 'So MySQL can index the row', 'is_correct' => false],
                                ['option_text' => 'Keys are optional and only used for styling', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: A child component should change its props to update the UI.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: Adjacent JSX tags must be wrapped in one parent (or a fragment).',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'In <StudentCard name="Amina" />, what is "Amina"?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A prop value passed into StudentCard', 'is_correct' => true],
                                ['option_text' => 'A CSS class', 'is_correct' => false],
                                ['option_text' => 'A database password', 'is_correct' => false],
                                ['option_text' => 'A PHP route', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What is map() used for in React lists?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'To turn an array into a list of JSX elements', 'is_correct' => true],
                                ['option_text' => 'To connect to MySQL', 'is_correct' => false],
                                ['option_text' => 'To style Bootstrap cards', 'is_correct' => false],
                                ['option_text' => 'To rename a file', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'On a JSX label, which attribute links to an input id?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'htmlFor', 'is_correct' => true],
                                ['option_text' => 'for', 'is_correct' => false],
                                ['option_text' => 'labelFor', 'is_correct' => false],
                                ['option_text' => 'inputId', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'How do you put JavaScript inside JSX?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'With curly braces { }', 'is_correct' => true],
                                ['option_text' => 'With square brackets [ ]', 'is_correct' => false],
                                ['option_text' => 'With PHP tags <?php ?>', 'is_correct' => false],
                                ['option_text' => 'With a SQL comment --', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which JSX tag is written correctly?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => '<img src="photo.jpg" />', 'is_correct' => true],
                                ['option_text' => '<img src="photo.jpg">', 'is_correct' => false],
                                ['option_text' => '<image src photo>', 'is_correct' => false],
                                ['option_text' => '<img src="photo.jpg"></picture>', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'State, Events, and a Mini Counter App',
                'summary' => 'Use useState and click handlers to make a component interactive.',
                'duration' => 50,
                'objectives' => "Call useState to store changing values\nUpdate state with the setter function\nHandle a button click in JSX\nBuild a small counter as a mini project",
                'guidance' => 'Live-code the counter. Emphasize: never assign to count directly; call setCount. Wrap up by adding a Reset button.',
                'content' => <<<'HTML'
<h2>State is data that can change</h2>
<p>Props come from the parent. <strong>State</strong> lives inside the component and can change when the user clicks, types, or submits a form.</p>
<p>The hook <code>useState</code> gives you the current value and a function to update it:</p>
<pre>import { useState } from 'react';

function Counter() {
  const [count, setCount] = useState(0);

  return (
    &lt;div&gt;
      &lt;p&gt;You clicked {count} times.&lt;/p&gt;
      &lt;button onClick={() =&gt; setCount(count + 1)}&gt;
        Add one
      &lt;/button&gt;
    &lt;/div&gt;
  );
}</pre>

<h3>Rules that matter</h3>
<ul>
  <li>Call hooks at the top of the component, not inside loops or if statements</li>
  <li>Do not write <code>count = count + 1</code>. That will not re-render. Use <code>setCount</code>.</li>
  <li>After <code>setCount</code>, React renders the component again with the new value</li>
</ul>

<h3>Events in JSX</h3>
<p>HTML used <code>onclick</code>. JSX uses camelCase: <code>onClick</code>, <code>onChange</code>, <code>onSubmit</code>.</p>
<pre>function NameForm() {
  const [name, setName] = useState('');

  return (
    &lt;form onSubmit={(event) =&gt; event.preventDefault()}&gt;
      &lt;input
        value={name}
        onChange={(event) =&gt; setName(event.target.value)}
        placeholder="Your name"
      /&gt;
      &lt;p&gt;Hello, {name || 'friend'}&lt;/p&gt;
    &lt;/form&gt;
  );
}</pre>

<h3>Mini project: Camp clicker</h3>
<ol>
  <li>Start count at 0.</li>
  <li>Add buttons: <em>Add 1</em>, <em>Add 5</em>, <em>Reset</em>.</li>
  <li>If count reaches 10, show: “Great streak — keep going!”</li>
</ol>
<pre>function CampClicker() {
  const [count, setCount] = useState(0);

  return (
    &lt;section&gt;
      &lt;h2&gt;Camp clicker&lt;/h2&gt;
      &lt;p&gt;{count}&lt;/p&gt;
      &lt;button onClick={() =&gt; setCount(count + 1)}&gt;+1&lt;/button&gt;
      &lt;button onClick={() =&gt; setCount(count + 5)}&gt;+5&lt;/button&gt;
      &lt;button onClick={() =&gt; setCount(0)}&gt;Reset&lt;/button&gt;
      {count &gt;= 10 &amp;&amp; &lt;p&gt;Great streak — keep going!&lt;/p&gt;}
    &lt;/section&gt;
  );
}</pre>

<h3>What to remember</h3>
<p>Props in, state inside, events update state, React redraws the UI. That loop is the core of almost every React screen you will build next: quizzes, attendance widgets, and dashboards.</p>
HTML,
                'quiz' => [
                    'title' => 'State and Events — Quiz',
                    'description' => 'Check useState, events, and why you must use the setter.',
                    'questions' => [
                        [
                            'question_text' => 'What does useState(0) return?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'The current value and a function to update it', 'is_correct' => true],
                                ['option_text' => 'Only the number 0 forever', 'is_correct' => false],
                                ['option_text' => 'A CSS variable', 'is_correct' => false],
                                ['option_text' => 'A database connection', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which click handler is valid in JSX?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'onClick={() => setCount(count + 1)}', 'is_correct' => true],
                                ['option_text' => 'onclick="setCount(count + 1)"', 'is_correct' => false],
                                ['option_text' => 'on-click={setCount}', 'is_correct' => false],
                                ['option_text' => 'click=addOne', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why is count = count + 1 the wrong way to update state?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'React will not re-render; you must call the setter', 'is_correct' => true],
                                ['option_text' => 'JavaScript does not allow adding numbers', 'is_correct' => false],
                                ['option_text' => 'It only works in CSS', 'is_correct' => false],
                                ['option_text' => 'It works, but it is slower than Bootstrap', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: You should call useState inside an if statement that sometimes skips it.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'After setCount(5) runs, what happens next?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'React re-renders the component with the new count', 'is_correct' => true],
                                ['option_text' => 'The page reloads from the server', 'is_correct' => false],
                                ['option_text' => 'Nothing until you save the file', 'is_correct' => false],
                                ['option_text' => 'MySQL stores the number 5', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which event is used when a student types in an input?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'onChange', 'is_correct' => true],
                                ['option_text' => 'onchangehtml', 'is_correct' => false],
                                ['option_text' => 'onType', 'is_correct' => false],
                                ['option_text' => 'onDatabase', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Where do you import useState from?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => "from 'react'", 'is_correct' => true],
                                ['option_text' => "from 'bootstrap'", 'is_correct' => false],
                                ['option_text' => "from 'mysql'", 'is_correct' => false],
                                ['option_text' => "from 'laravel'", 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A Reset button that sends the counter back to 0 should call:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'setCount(0)', 'is_correct' => true],
                                ['option_text' => 'count = 0', 'is_correct' => false],
                                ['option_text' => 'location.reload()', 'is_correct' => false],
                                ['option_text' => 'DELETE FROM counts', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What does {count >= 10 && <p>Great streak</p>} do?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Shows the extra message only when count is 10 or more', 'is_correct' => true],
                                ['option_text' => 'Always shows the message', 'is_correct' => false],
                                ['option_text' => 'Deletes the component', 'is_correct' => false],
                                ['option_text' => 'Saves the score to MySQL', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'In const [count, setCount] = useState(0), what is count?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'The current state value', 'is_correct' => true],
                                ['option_text' => 'A CSS class name', 'is_correct' => false],
                                ['option_text' => 'A database table', 'is_correct' => false],
                                ['option_text' => 'The HTML file name', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Conditional Rendering',
                'summary' => 'Show or hide parts of the UI with if statements, &&, and ternary operators.',
                'duration' => 40,
                'objectives' => "Show different JSX based on a true/false value\nUse && to display extra content\nUse a ternary to pick between two views\nHide a message when a list is empty",
                'guidance' => 'Live-code a login banner and an empty-state for a club list. Students should see the screen change when you flip a boolean in useState.',
                'content' => <<<'HTML'
<h2>Not every screen shows the same thing</h2>
<p>A welcome banner should appear only after a student logs in. An empty club list should say “No clubs yet.” In React you choose what to return based on data.</p>

<h3>If statements</h3>
<pre>function Banner({ isLoggedIn }) {
  if (!isLoggedIn) {
    return &lt;p&gt;Please log in.&lt;/p&gt;;
  }

  return &lt;p&gt;Welcome back to camp.&lt;/p&gt;;
}</pre>

<h3>&& means “show this only if true”</h3>
<pre>function Notice({ count }) {
  return (
    &lt;section&gt;
      &lt;p&gt;Clicks: {count}&lt;/p&gt;
      {count &gt;= 10 &amp;&amp; &lt;p&gt;Great streak!&lt;/p&gt;}
    &lt;/section&gt;
  );
}</pre>

<h3>Ternary: pick A or B</h3>
<pre>function Door({ isOpen }) {
  return &lt;p&gt;{isOpen ? 'Open' : 'Closed'}&lt;/p&gt;;
}</pre>

<h3>Empty lists</h3>
<pre>function ClubList({ clubs }) {
  if (clubs.length === 0) {
    return &lt;p&gt;No clubs yet. Ask your facilitator.&lt;/p&gt;;
  }

  return (
    &lt;ul&gt;
      {clubs.map((club) =&gt; (
        &lt;li key={club}&gt;{club}&lt;/li&gt;
      ))}
    &lt;/ul&gt;
  );
}</pre>

<h3>Try this</h3>
<ol>
  <li>Store <code>isMember</code> with <code>useState(false)</code>.</li>
  <li>A button toggles membership.</li>
  <li>Show “Join the club” or “You are a member” based on that value.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Conditional Rendering — Quiz',
                    'description' => 'Check if, &&, and ternary rendering.',
                    'questions' => [
                        [
                            'question_text' => 'What does conditional rendering mean in React?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Showing different UI based on data', 'is_correct' => true],
                                ['option_text' => 'Always printing every tag in the file', 'is_correct' => false],
                                ['option_text' => 'Connecting to MySQL', 'is_correct' => false],
                                ['option_text' => 'Renaming a CSS file', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What does {isOpen && <p>Open</p>} do?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Shows the paragraph only when isOpen is true', 'is_correct' => true],
                                ['option_text' => 'Always shows the paragraph', 'is_correct' => false],
                                ['option_text' => 'Deletes the component', 'is_correct' => false],
                                ['option_text' => 'Saves isOpen to a database', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which is a ternary in JSX?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => "{isOpen ? 'Open' : 'Closed'}", 'is_correct' => true],
                                ['option_text' => 'if isOpen then Open else Closed', 'is_correct' => false],
                                ['option_text' => 'SELECT Open FROM doors', 'is_correct' => false],
                                ['option_text' => 'className="open closed"', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: A component can return early with if (!ready) { return <p>Loading</p>; }',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'If clubs.length is 0, what should ClubList usually show?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'An empty-state message like “No clubs yet”', 'is_correct' => true],
                                ['option_text' => 'A MySQL error', 'is_correct' => false],
                                ['option_text' => 'A blank CSS file', 'is_correct' => false],
                                ['option_text' => 'Ten fake clubs', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which value is treated as false in {count && <p>Hi</p>} so the paragraph is hidden?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => '0, false, null, or an empty string', 'is_correct' => true],
                                ['option_text' => 'Only the number 10', 'is_correct' => false],
                                ['option_text' => 'Only CSS classes', 'is_correct' => false],
                                ['option_text' => 'Only Bootstrap buttons', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: You can put a full if/else inside the return’s JSX curly braces.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'A toggle button that flips isMember should call:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'setIsMember(!isMember)', 'is_correct' => true],
                                ['option_text' => 'isMember = !isMember', 'is_correct' => false],
                                ['option_text' => 'location.reload()', 'is_correct' => false],
                                ['option_text' => 'DELETE FROM members', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What is a good use of a ternary in a camp app?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Show “Present” or “Absent” from one boolean', 'is_correct' => true],
                                ['option_text' => 'Replace HTML with PHP', 'is_correct' => false],
                                ['option_text' => 'Create a MySQL table', 'is_correct' => false],
                                ['option_text' => 'Install Bootstrap', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'If isLoggedIn is false, Banner should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Show a “Please log in” message', 'is_correct' => true],
                                ['option_text' => 'Crash the browser', 'is_correct' => false],
                                ['option_text' => 'Always show “Welcome back”', 'is_correct' => false],
                                ['option_text' => 'Delete the React app', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Lists, Keys, and Rendering Arrays',
                'summary' => 'Turn arrays of students or clubs into JSX lists with map() and stable keys.',
                'duration' => 45,
                'objectives' => "Render an array with map()\nGive each item a unique key\nBuild a card for each student\nAvoid using the array index as a key when items can move",
                'guidance' => 'Have students start from a JavaScript array of club names, then map it into Bootstrap cards. Show what happens when two items share the same key.',
                'content' => <<<'HTML'
<h2>Most camp screens are lists</h2>
<p>Students, clubs, scores, and attendance rows all start as arrays. React does not loop with a <code>for</code> tag. You use <code>map()</code> and return JSX for each item.</p>

<h3>map() returns elements</h3>
<pre>const clubs = ['Scratch', 'Web', 'Robotics'];

function ClubList() {
  return (
    &lt;ul&gt;
      {clubs.map((club) =&gt; (
        &lt;li key={club}&gt;{club}&lt;/li&gt;
      ))}
    &lt;/ul&gt;
  );
}</pre>

<h3>Keys</h3>
<p>A <code>key</code> is a string React uses to know which row changed. Prefer a real id from your data. Avoid <code>key={index}</code> if the list can be reordered or filtered.</p>
<pre>const students = [
  { id: 1, name: 'Amina' },
  { id: 2, name: 'Joel' },
];

function Roster() {
  return students.map((student) =&gt; (
    &lt;article key={student.id} className="card p-3 mb-2"&gt;
      &lt;h3&gt;{student.name}&lt;/h3&gt;
    &lt;/article&gt;
  ));
}</pre>

<h3>Empty and loading</h3>
<p>If the array is empty, return a message. Do not call <code>map</code> on <code>undefined</code> — start with <code>const [students, setStudents] = useState([])</code>.</p>

<h3>Try this</h3>
<ol>
  <li>Create an array of 5 camp clubs with <code>id</code> and <code>name</code>.</li>
  <li>Render a Bootstrap card for each club.</li>
  <li>Add a button that removes one club with <code>filter</code> and <code>setClubs</code>.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Lists and Keys — Quiz',
                    'description' => 'Check map(), keys, and array state.',
                    'questions' => [
                        [
                            'question_text' => 'Which array method is used to render a list in React?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'map()', 'is_correct' => true],
                                ['option_text' => 'alert()', 'is_correct' => false],
                                ['option_text' => 'console.log()', 'is_correct' => false],
                                ['option_text' => 'document.write()', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why does each list item need a key?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'So React can track which item changed', 'is_correct' => true],
                                ['option_text' => 'So Bootstrap can color the row', 'is_correct' => false],
                                ['option_text' => 'So MySQL can index the row', 'is_correct' => false],
                                ['option_text' => 'Keys are only for CSS animations', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What is the best key for a student object with an id?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'student.id', 'is_correct' => true],
                                ['option_text' => 'The word “key”', 'is_correct' => false],
                                ['option_text' => 'Math.random()', 'is_correct' => false],
                                ['option_text' => 'The student’s password', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: You should start list state as undefined, then map it immediately.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'How do you remove a club with id 2 from state?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'setClubs(clubs.filter((club) => club.id !== 2))', 'is_correct' => true],
                                ['option_text' => 'clubs.delete(2)', 'is_correct' => false],
                                ['option_text' => 'DELETE FROM clubs WHERE id = 2 inside JSX', 'is_correct' => false],
                                ['option_text' => 'clubs.pop() only', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: Using the array index as a key is risky if items can be reordered.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What should you show when the clubs array is empty?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A short “No clubs yet” message', 'is_correct' => true],
                                ['option_text' => 'A PHP error page', 'is_correct' => false],
                                ['option_text' => 'Nothing — crash is fine', 'is_correct' => false],
                                ['option_text' => 'The entire Bootstrap docs', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'map() must return:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'JSX (or null) for each item', 'is_correct' => true],
                                ['option_text' => 'A CSS file', 'is_correct' => false],
                                ['option_text' => 'A MySQL row', 'is_correct' => false],
                                ['option_text' => 'A Laravel route', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which starting state is safest for a list?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'useState([])', 'is_correct' => true],
                                ['option_text' => 'useState(undefined)', 'is_correct' => false],
                                ['option_text' => 'useState(null) then map immediately', 'is_correct' => false],
                                ['option_text' => 'useState("list")', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Two list items with the same key will:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Confuse React and can cause bugs', 'is_correct' => true],
                                ['option_text' => 'Make CSS load faster', 'is_correct' => false],
                                ['option_text' => 'Create a database backup', 'is_correct' => false],
                                ['option_text' => 'Automatically merge the students', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Forms and Controlled Inputs',
                'summary' => 'Keep input values in state and update them with onChange.',
                'duration' => 45,
                'objectives' => "Bind an input value to state\nUpdate state with onChange\nPrevent a form’s default submit\nBuild a short add-student form",
                'guidance' => 'Build a form that adds a name to a list. Emphasize value={name} plus onChange. Without both, the input is not controlled.',
                'content' => <<<'HTML'
<h2>The input is driven by React state</h2>
<p>In plain HTML the input keeps its own text. In React we usually store the text in <code>useState</code> and pass it back with <code>value</code>. That is a <strong>controlled input</strong>.</p>

<h3>One field</h3>
<pre>function NameForm() {
  const [name, setName] = useState('');

  return (
    &lt;input
      value={name}
      onChange={(event) =&gt; setName(event.target.value)}
      placeholder="Student name"
    /&gt;
  );
}</pre>

<h3>Submit without a page reload</h3>
<pre>function AddStudent({ onAdd }) {
  const [name, setName] = useState('');

  function handleSubmit(event) {
    event.preventDefault();
    if (!name.trim()) {
      return;
    }
    onAdd(name.trim());
    setName('');
  }

  return (
    &lt;form onSubmit={handleSubmit}&gt;
      &lt;input value={name} onChange={(e) =&gt; setName(e.target.value)} /&gt;
      &lt;button type="submit"&gt;Add&lt;/button&gt;
    &lt;/form&gt;
  );
}</pre>
<p><code>event.preventDefault()</code> stops the browser from reloading the page — the same idea as in Web 1 forms, but now the list lives in React state.</p>

<h3>Checkboxes</h3>
<pre>&lt;input
  type="checkbox"
  checked={isPresent}
  onChange={(e) =&gt; setIsPresent(e.target.checked)}
/&gt;</pre>
<p>Checkboxes use <code>checked</code> and <code>e.target.checked</code>, not <code>value</code>.</p>

<h3>Try this</h3>
<ol>
  <li>Form with name and club fields.</li>
  <li>On submit, push a new object into a <code>students</code> array in state.</li>
  <li>Clear the inputs after a successful add.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Forms and Inputs — Quiz',
                    'description' => 'Check controlled inputs, onChange, and submit.',
                    'questions' => [
                        [
                            'question_text' => 'What makes an input “controlled” in React?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Its value comes from state and updates with onChange', 'is_correct' => true],
                                ['option_text' => 'It is written only in a CSS file', 'is_correct' => false],
                                ['option_text' => 'It posts straight to MySQL', 'is_correct' => false],
                                ['option_text' => 'It has no attributes', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Which event updates a text field as the student types?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'onChange', 'is_correct' => true],
                                ['option_text' => 'onDatabase', 'is_correct' => false],
                                ['option_text' => 'onCss', 'is_correct' => false],
                                ['option_text' => 'onPhp', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Where does the typed text live in event.target.value?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'On the input element that changed', 'is_correct' => true],
                                ['option_text' => 'In a Laravel controller', 'is_correct' => false],
                                ['option_text' => 'In a CSS variable', 'is_correct' => false],
                                ['option_text' => 'In localStorage only', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why call event.preventDefault() on submit?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'To stop the browser from reloading the page', 'is_correct' => true],
                                ['option_text' => 'To install Bootstrap', 'is_correct' => false],
                                ['option_text' => 'To delete the form', 'is_correct' => false],
                                ['option_text' => 'To create a MySQL user', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: A checkbox uses checked and e.target.checked.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'After adding a student, a good form should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Clear the input with setName("")', 'is_correct' => true],
                                ['option_text' => 'Reload the whole website from GitHub', 'is_correct' => false],
                                ['option_text' => 'Delete React', 'is_correct' => false],
                                ['option_text' => 'Leave the old name stuck in the box forever', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: value={name} without onChange will make typing feel broken.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A submit button inside a form should usually have:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'type="submit"', 'is_correct' => true],
                                ['option_text' => 'type="database"', 'is_correct' => false],
                                ['option_text' => 'type="css"', 'is_correct' => false],
                                ['option_text' => 'type="php"', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'If name.trim() is empty, handleSubmit should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Return without adding a blank student', 'is_correct' => true],
                                ['option_text' => 'Always add an empty card', 'is_correct' => false],
                                ['option_text' => 'Drop the database', 'is_correct' => false],
                                ['option_text' => 'Install npm again', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'The form’s onSubmit receives:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'The submit event', 'is_correct' => true],
                                ['option_text' => 'A MySQL connection', 'is_correct' => false],
                                ['option_text' => 'A CSS stylesheet', 'is_correct' => false],
                                ['option_text' => 'The GitHub token', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'useEffect and Side Effects',
                'summary' => 'Run code after render for timers, document titles, and loading data.',
                'duration' => 45,
                'objectives' => "Import and call useEffect\nExplain that effects run after paint\nUse a dependency array\nClean up a timer with return () => clearInterval",
                'guidance' => 'Keep this practical: change document.title, then a countdown timer. Skip Redux and data libraries. If you fetch JSON, use a public sample URL and handle loading.',
                'content' => <<<'HTML'
<h2>Some work is not “draw the UI”</h2>
<p>Setting the browser tab title, starting a timer, or fetching JSON are <strong>side effects</strong>. They do not belong in the middle of your JSX. <code>useEffect</code> runs them after React has drawn the screen.</p>

<h3>Change the tab title</h3>
<pre>import { useState, useEffect } from 'react';

function Clicker() {
  const [count, setCount] = useState(0);

  useEffect(() =&gt; {
    document.title = 'Clicks: ' + count;
  }, [count]);

  return (
    &lt;button onClick={() =&gt; setCount(count + 1)}&gt;{count}&lt;/button&gt;
  );
}</pre>
<p>The second argument <code>[count]</code> is the <strong>dependency array</strong>. The effect re-runs when <code>count</code> changes. Use <code>[]</code> if it should run only once after the first render.</p>

<h3>Timers need cleanup</h3>
<pre>useEffect(() =&gt; {
  const id = setInterval(() =&gt; {
    setSeconds((s) =&gt; s + 1);
  }, 1000);

  return () =&gt; clearInterval(id);
}, []);</pre>
<p>The function you return from <code>useEffect</code> runs when the component unmounts (or before the effect runs again). Always clear intervals so they do not keep firing off-screen.</p>

<h3>Loading flag</h3>
<pre>const [clubs, setClubs] = useState([]);
const [loading, setLoading] = useState(true);

useEffect(() =&gt; {
  setLoading(true);
  fetch('/clubs.json')
    .then((res) =&gt; res.json())
    .then((data) =&gt; setClubs(data))
    .finally(() =&gt; setLoading(false));
}, []);</pre>

<h3>Try this</h3>
<ol>
  <li>Show a 10-second countdown with <code>setInterval</code>.</li>
  <li>When it hits 0, show “Time’s up!”</li>
  <li>Clear the interval in the cleanup function.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'useEffect — Quiz',
                    'description' => 'Check effects, dependency arrays, and cleanup.',
                    'questions' => [
                        [
                            'question_text' => 'What is useEffect for?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Running side effects after the UI renders', 'is_correct' => true],
                                ['option_text' => 'Replacing CSS', 'is_correct' => false],
                                ['option_text' => 'Creating MySQL tables', 'is_correct' => false],
                                ['option_text' => 'Styling Bootstrap navbars only', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Where do you import useEffect from?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => "from 'react'", 'is_correct' => true],
                                ['option_text' => "from 'bootstrap'", 'is_correct' => false],
                                ['option_text' => "from 'jquery'", 'is_correct' => false],
                                ['option_text' => "from 'laravel'", 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What does an empty dependency array [] mean?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Run the effect once after the first render', 'is_correct' => true],
                                ['option_text' => 'Run the effect on every keystroke in the world', 'is_correct' => false],
                                ['option_text' => 'Never run the effect', 'is_correct' => false],
                                ['option_text' => 'Delete the component', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'useEffect(() => { ... }, [count]) re-runs when:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'count changes', 'is_correct' => true],
                                ['option_text' => 'The CSS file is renamed', 'is_correct' => false],
                                ['option_text' => 'MySQL restarts', 'is_correct' => false],
                                ['option_text' => 'GitHub gets a new star', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: You should start a setInterval inside JSX, not in useEffect.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'How do you stop a timer when the component unmounts?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Return a cleanup function that calls clearInterval', 'is_correct' => true],
                                ['option_text' => 'Close the laptop lid only', 'is_correct' => false],
                                ['option_text' => 'Delete index.html', 'is_correct' => false],
                                ['option_text' => 'Use a PHP die() in JSX', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'document.title belongs in useEffect because:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'It is a side effect on the browser, not UI markup', 'is_correct' => true],
                                ['option_text' => 'It is a CSS class', 'is_correct' => false],
                                ['option_text' => 'It is a SQL command', 'is_correct' => false],
                                ['option_text' => 'React forbids buttons', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: fetch() of JSON is a common useEffect task.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A loading flag should be set to false:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'After the fetch finishes (success or fail)', 'is_correct' => true],
                                ['option_text' => 'Only in a CSS animation', 'is_correct' => false],
                                ['option_text' => 'Never — keep spinning forever', 'is_correct' => false],
                                ['option_text' => 'Before the component exists', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'setSeconds((s) => s + 1) inside an interval is useful because:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'It uses the latest seconds value', 'is_correct' => true],
                                ['option_text' => 'It writes to MySQL', 'is_correct' => false],
                                ['option_text' => 'It reloads Nginx', 'is_correct' => false],
                                ['option_text' => 'It changes the PHP version', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Lifting State and Sharing Data',
                'summary' => 'Move shared state to a parent and pass values and callbacks down as props.',
                'duration' => 45,
                'objectives' => "Decide which component should own a piece of state\nPass data down as props\nPass a setter or handler down so a child can update the parent\nKeep a single source of truth",
                'guidance' => 'Demo two sibling counters that must share one total. Students feel the bug, then you lift count into App and pass props down.',
                'content' => <<<'HTML'
<h2>Siblings cannot see each other’s useState</h2>
<p>If <code>ClubList</code> and <code>ClubForm</code> both need the same array, that array cannot live in two places. Put it in the parent. That is <strong>lifting state up</strong>.</p>

<h3>Parent owns the data</h3>
<pre>function App() {
  const [clubs, setClubs] = useState(['Scratch', 'Web']);

  function addClub(name) {
    setClubs([...clubs, name]);
  }

  return (
    &lt;main&gt;
      &lt;ClubForm onAdd={addClub} /&gt;
      &lt;ClubList clubs={clubs} /&gt;
    &lt;/main&gt;
  );
}</pre>

<h3>Child calls the parent</h3>
<pre>function ClubForm({ onAdd }) {
  const [name, setName] = useState('');

  return (
    &lt;form
      onSubmit={(e) =&gt; {
        e.preventDefault();
        onAdd(name);
        setName('');
      }}
    &gt;
      &lt;input value={name} onChange={(e) =&gt; setName(e.target.value)} /&gt;
      &lt;button type="submit"&gt;Add club&lt;/button&gt;
    &lt;/form&gt;
  );
}</pre>
<p>The form does not store the full list. It only stores the draft input. The list lives in <code>App</code>.</p>

<h3>One source of truth</h3>
<p>If two components each have <code>useState</code> for the same clubs, they will go out of sync. Lift the state. Pass <code>clubs</code> down. Pass <code>onAdd</code> or <code>onDelete</code> down.</p>

<h3>Try this</h3>
<ol>
  <li>Parent holds <code>students</code>.</li>
  <li><code>AddStudent</code> calls <code>onAdd</code>.</li>
  <li><code>StudentList</code> receives <code>students</code> and an <code>onRemove</code> callback.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Lifting State — Quiz',
                    'description' => 'Check props, callbacks, and a single source of truth.',
                    'questions' => [
                        [
                            'question_text' => 'What does lifting state up mean?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Moving shared data into the closest parent', 'is_correct' => true],
                                ['option_text' => 'Putting CSS in the HTML head', 'is_correct' => false],
                                ['option_text' => 'Uploading the app to GitHub Pages only', 'is_correct' => false],
                                ['option_text' => 'Deleting child components', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Two sibling components need the same list. Where should the list live?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'In the parent', 'is_correct' => true],
                                ['option_text' => 'Copied in both siblings with no connection', 'is_correct' => false],
                                ['option_text' => 'Only in a CSS file', 'is_correct' => false],
                                ['option_text' => 'Only in Nginx', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'How does a child tell the parent to add a club?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'It calls a function the parent passed as a prop', 'is_correct' => true],
                                ['option_text' => 'It edits the parent file at runtime', 'is_correct' => false],
                                ['option_text' => 'It writes SQL in the input', 'is_correct' => false],
                                ['option_text' => 'It changes document.cookie only', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: A child should change props.clubs directly with clubs.push().',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'setClubs([...clubs, name]) is used to:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Create a new array with the extra name', 'is_correct' => true],
                                ['option_text' => 'Restart MySQL', 'is_correct' => false],
                                ['option_text' => 'Reload Nginx', 'is_correct' => false],
                                ['option_text' => 'Install Bootstrap', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'What is a single source of truth?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'One place that owns the real data', 'is_correct' => true],
                                ['option_text' => 'Five copies of the same list', 'is_correct' => false],
                                ['option_text' => 'A random CSS class', 'is_correct' => false],
                                ['option_text' => 'The PHP version number', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: ClubForm can keep local state for the input box even if the list lives in App.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Passing onRemove to StudentList lets the list:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Ask the parent to delete an item', 'is_correct' => true],
                                ['option_text' => 'Change the Linux password', 'is_correct' => false],
                                ['option_text' => 'Rewrite the seeder', 'is_correct' => false],
                                ['option_text' => 'Turn off Redis', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Data usually flows:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Down as props; events go up as callbacks', 'is_correct' => true],
                                ['option_text' => 'Sideways between random files', 'is_correct' => false],
                                ['option_text' => 'Only through CSS', 'is_correct' => false],
                                ['option_text' => 'Only through email', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'If two components show different club counts, the likely bug is:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Each one has its own copy of the list', 'is_correct' => true],
                                ['option_text' => 'Bootstrap is missing a comma', 'is_correct' => false],
                                ['option_text' => 'The VPS has no RAM', 'is_correct' => false],
                                ['option_text' => 'JSX cannot show numbers', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Styling React with Bootstrap',
                'summary' => 'Reuse Web Development 1 Bootstrap classes inside JSX with className.',
                'duration' => 40,
                'objectives' => "Use className instead of class\nApply Bootstrap grid and cards in JSX\nToggle classes from state\nKeep styles consistent across components",
                'guidance' => 'Students already know Bootstrap. The only new rule is className. Build a responsive card grid of clubs. Show btn-primary vs btn-outline based on state.',
                'content' => <<<'HTML'
<h2>Your CSS skills still count</h2>
<p>React does not replace Bootstrap. You still use <code>container</code>, <code>row</code>, <code>col</code>, <code>card</code>, and <code>btn</code>. In JSX the attribute is <code>className</code> because <code>class</code> is reserved in JavaScript.</p>

<h3>A Bootstrap card in JSX</h3>
<pre>function ClubCard({ name, track }) {
  return (
    &lt;article className="card h-100 shadow-sm"&gt;
      &lt;div className="card-body"&gt;
        &lt;h3 className="card-title"&gt;{name}&lt;/h3&gt;
        &lt;p className="card-text"&gt;{track}&lt;/p&gt;
        &lt;button className="btn btn-primary"&gt;View&lt;/button&gt;
      &lt;/div&gt;
    &lt;/article&gt;
  );
}</pre>

<h3>Grid</h3>
<pre>&lt;div className="container py-4"&gt;
  &lt;div className="row g-3"&gt;
    {clubs.map((club) =&gt; (
      &lt;div className="col-12 col-md-6 col-lg-4" key={club.id}&gt;
        &lt;ClubCard name={club.name} track={club.track} /&gt;
      &lt;/div&gt;
    ))}
  &lt;/div&gt;
&lt;/div&gt;</pre>

<h3>Classes from state</h3>
<pre>function AttendanceButton({ present, onToggle }) {
  const classes = present
    ? 'btn btn-success'
    : 'btn btn-outline-secondary';

  return (
    &lt;button className={classes} onClick={onToggle}&gt;
      {present ? 'Present' : 'Mark present'}
    &lt;/button&gt;
  );
}</pre>

<h3>Inline style (use sparingly)</h3>
<pre>&lt;p style={{ color: '#0d6efd', fontWeight: 700 }}&gt;Code Academy&lt;/p&gt;</pre>
<p>The style prop takes a JavaScript object. CSS properties become camelCase: <code>fontWeight</code>, not <code>font-weight</code>.</p>

<h3>Try this</h3>
<ol>
  <li>Rebuild a Web 1 club page as React cards.</li>
  <li>Toggle a button between <code>btn-success</code> and <code>btn-outline-secondary</code>.</li>
  <li>Keep spacing with Bootstrap utilities, not random inline styles.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Styling React — Quiz',
                    'description' => 'Check className, Bootstrap in JSX, and style objects.',
                    'questions' => [
                        [
                            'question_text' => 'In JSX, which attribute sets a CSS class?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'className', 'is_correct' => true],
                                ['option_text' => 'class', 'is_correct' => false],
                                ['option_text' => 'cssClass', 'is_correct' => false],
                                ['option_text' => 'bootstrap', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: You can still use Bootstrap cards and buttons inside React.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A three-column grid on large screens uses:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'col-lg-4 inside a row', 'is_correct' => true],
                                ['option_text' => 'mysql-3', 'is_correct' => false],
                                ['option_text' => 'php-column', 'is_correct' => false],
                                ['option_text' => 'nginx-grid', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'How do you switch a button from outline to solid green when present is true?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Pick the className from state with a ternary', 'is_correct' => true],
                                ['option_text' => 'Restart the VPS', 'is_correct' => false],
                                ['option_text' => 'Change the PHP version', 'is_correct' => false],
                                ['option_text' => 'Edit .env', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'The style prop in JSX expects:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A JavaScript object', 'is_correct' => true],
                                ['option_text' => 'A .sql file', 'is_correct' => false],
                                ['option_text' => 'A Laravel route', 'is_correct' => false],
                                ['option_text' => 'An Nginx site', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'font-weight in CSS becomes which style key?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'fontWeight', 'is_correct' => true],
                                ['option_text' => 'font-weight', 'is_correct' => false],
                                ['option_text' => 'FONT_WEIGHT', 'is_correct' => false],
                                ['option_text' => 'weightFont', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: className="card-body" is valid in a React component.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'btn btn-primary is:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'A Bootstrap button class list', 'is_correct' => true],
                                ['option_text' => 'A SQL query', 'is_correct' => false],
                                ['option_text' => 'A React hook', 'is_correct' => false],
                                ['option_text' => 'An Nginx command', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why is class invalid as a JSX attribute?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'class is a reserved word in JavaScript', 'is_correct' => true],
                                ['option_text' => 'Bootstrap forbids classes', 'is_correct' => false],
                                ['option_text' => 'Nginx blocks the word class', 'is_correct' => false],
                                ['option_text' => 'HTML never used class', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'A good default for camp UIs is:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Bootstrap utilities, with little inline style', 'is_correct' => true],
                                ['option_text' => 'Only inline styles on every tag', 'is_correct' => false],
                                ['option_text' => 'No classes at all', 'is_correct' => false],
                                ['option_text' => 'Random colors in PHP', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Mini Project: Club Directory App',
                'summary' => 'Combine components, state, forms, lists, and Bootstrap into one small camp app.',
                'duration' => 60,
                'objectives' => "Plan a parent App with shared state\nAdd, list, and remove clubs\nFilter the list from a search box\nStyle the UI with Bootstrap cards",
                'guidance' => 'This is a studio session. Pair students. Do not introduce Redux or a backend. A JavaScript array in useState is enough. End with a 2-minute demo per pair.',
                'content' => <<<'HTML'
<h2>Put the pieces together</h2>
<p>You now have components, props, state, lists, forms, and Bootstrap. Build a <strong>Club Directory</strong> that a facilitator could actually use on a projector.</p>

<h3>Features</h3>
<ul>
  <li>List clubs as cards (name, track, room)</li>
  <li>Add a club with a form</li>
  <li>Remove a club</li>
  <li>Search by name</li>
  <li>Show “No clubs match” when the filter is empty</li>
</ul>

<h3>Suggested file split</h3>
<pre>App.jsx          — clubs state, search state, handlers
ClubForm.jsx     — controlled inputs, calls onAdd
ClubList.jsx     — maps filtered clubs to ClubCard
ClubCard.jsx     — one card + Remove button</pre>

<h3>Filter in the parent</h3>
<pre>const visible = clubs.filter((club) =&gt;
  club.name.toLowerCase().includes(search.toLowerCase())
);</pre>
<p>Pass <code>visible</code> into <code>ClubList</code>. Keep the full array in <code>clubs</code> so clearing search brings everything back.</p>

<h3>Add with a new id</h3>
<pre>function addClub(data) {
  const id = Date.now();
  setClubs([...clubs, { id, ...data }]);
}</pre>

<h3>Remove</h3>
<pre>function removeClub(id) {
  setClubs(clubs.filter((club) =&gt; club.id !== id));
}</pre>

<h3>Definition of done</h3>
<ol>
  <li>The page does not reload on submit.</li>
  <li>Search updates as you type.</li>
  <li>Remove only deletes one card.</li>
  <li>Layout uses Bootstrap grid and cards.</li>
</ol>
<p>Next courses can save this list to an API. For Web Development 2, state in the browser is the goal.</p>
HTML,
                'quiz' => [
                    'title' => 'Club Directory Project — Quiz',
                    'description' => 'Check that you can combine React ideas in one small app.',
                    'questions' => [
                        [
                            'question_text' => 'In the club directory, who should own the clubs array?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'App (the parent)', 'is_correct' => true],
                                ['option_text' => 'Every card with its own full copy', 'is_correct' => false],
                                ['option_text' => 'Nginx', 'is_correct' => false],
                                ['option_text' => 'The CSS file', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'How do you search clubs by name?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'filter() the array using the search string', 'is_correct' => true],
                                ['option_text' => 'Delete Bootstrap', 'is_correct' => false],
                                ['option_text' => 'Restart MySQL', 'is_correct' => false],
                                ['option_text' => 'Change APP_KEY', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'Why keep both clubs and visible lists?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'clubs is the full data; visible is the filtered view', 'is_correct' => true],
                                ['option_text' => 'React requires two databases', 'is_correct' => false],
                                ['option_text' => 'Bootstrap needs two HTML files', 'is_correct' => false],
                                ['option_text' => 'PHP cannot show lists', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: Date.now() can be used as a simple unique id in a camp demo.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => true],
                                ['option_text' => 'False', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'removeClub should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'setClubs with filter so that id is gone', 'is_correct' => true],
                                ['option_text' => 'location.reload()', 'is_correct' => false],
                                ['option_text' => 'DROP TABLE clubs', 'is_correct' => false],
                                ['option_text' => 'Uninstall Node', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'ClubForm should call onAdd and then:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Clear the form fields', 'is_correct' => true],
                                ['option_text' => 'Wipe the entire clubs array', 'is_correct' => false],
                                ['option_text' => 'Close the laptop', 'is_correct' => false],
                                ['option_text' => 'Push to GitHub automatically', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'True or false: The mini project should reload the page on every Add click.',
                            'question_type' => 'true_false',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'ClubCard is a good component because:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'It does one job: display one club', 'is_correct' => true],
                                ['option_text' => 'It contains the whole application’s state', 'is_correct' => false],
                                ['option_text' => 'It talks to Nginx directly', 'is_correct' => false],
                                ['option_text' => 'It replaces JavaScript', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'When search matches nothing, the UI should:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'Show an empty-state message', 'is_correct' => true],
                                ['option_text' => 'Crash with a white screen', 'is_correct' => false],
                                ['option_text' => 'Drop the production database', 'is_correct' => false],
                                ['option_text' => 'Disable CSS forever', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question_text' => 'This Web 2 project stores clubs:',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'options' => [
                                ['option_text' => 'In React state in the browser', 'is_correct' => true],
                                ['option_text' => 'Only on a printed paper', 'is_correct' => false],
                                ['option_text' => 'Inside the Nginx binary', 'is_correct' => false],
                                ['option_text' => 'As a Laravel migration you must run', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
