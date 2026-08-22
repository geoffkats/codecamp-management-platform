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

        $module = CourseModule::firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Introduction to React',
            ],
            [
                'description' => 'Learn how React builds interactive user interfaces with components, JSX, props, and state. This module follows Web Development 1 (HTML, CSS, Bootstrap) and JavaScript basics.',
                'order_index' => (int) $course->modules()->max('order_index') + 1,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $course->approved_by ?: $course->instructor_id,
            ]
        );

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
                'time_limit_minutes' => 20,
                'passing_score' => 70,
                'xp_reward' => 50,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => true,
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

        foreach ($quizData['questions'] as $index => $questionData) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order' => $index,
                'explanation' => $questionData['explanation'],
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
                            'explanation' => 'React is a JavaScript library for building user interfaces with components.',
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
                            'explanation' => 'The screen is computed from data. When data changes, React re-renders the UI.',
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
                            'explanation' => 'A function that returns JSX is a React function component.',
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
                            'explanation' => 'False. JSX still uses HTML-like tags and you still write CSS (or Bootstrap).',
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
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
                            'explanation' => 'JSX uses className because class is a reserved word in JavaScript.',
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
                            'explanation' => 'Props are the inputs a parent passes into a child component.',
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
                            'explanation' => 'Keys help React identify which items changed, were added, or were removed.',
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
                            'explanation' => 'False. Props are read-only. Use state in the owner of the data, then pass new props down.',
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
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
                            'explanation' => 'It returns a pair: the current state value and a setter function.',
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
                            'explanation' => 'JSX uses camelCase onClick, not the HTML onclick attribute.',
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
                            'explanation' => 'Direct assignment does not tell React to re-render. Call the setter from useState.',
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
                            'explanation' => 'False. Hooks must run in the same order every render, at the top level of the component.',
                            'options' => [
                                ['option_text' => 'True', 'is_correct' => false],
                                ['option_text' => 'False', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question_text' => 'After setCount(5) runs, what happens next?',
                            'question_type' => 'multiple_choice',
                            'points' => 10,
                            'explanation' => 'React schedules a re-render so the component runs again with count equal to 5.',
                            'options' => [
                                ['option_text' => 'React re-renders the component with the new count', 'is_correct' => true],
                                ['option_text' => 'The page reloads from the server', 'is_correct' => false],
                                ['option_text' => 'Nothing until you save the file', 'is_correct' => false],
                                ['option_text' => 'MySQL stores the number 5', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
