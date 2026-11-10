<?php

$b = require __DIR__.'/blocks.php';
extract($b);

$mdn = ['MDN: HTML basics' => 'https://developer.mozilla.org/en-US/docs/Learn/Getting_started_with_the_web/HTML_basics'];

return [
    [
        'title' => 'Day 1: How the Web Works and Your First HTML Page',
        'summary' => 'Open a browser, meet HTML, and publish a first page that says who you are.',
        'question_of_day' => 'When you type a website address, what is the computer actually showing you?',
        'objectives' => "Explain what a browser, a server, and HTML each do.\nWrite a complete HTML page with doctype, html, head, and body.\nSave a .html file and open it in the browser.\nChange the page and refresh to see the update.",
        'guidance' => "0–15 min: show codeacademyug.org and ask what they see. 15–40: draw browser → internet → server → HTML file. 40–70: everyone types the skeleton together, save as index.html, open it. 70–100: personalise the page (name, camp, city). 100–115: quiz. 115–120: upload the file. Walk the room during the build. Common mistake: saving as index.html.txt.",
        'content' => $hero(1, 'How the web works and your first page', 'HTML is the language that tells the browser what is on the page.')
            .$plan([
                ['0:00 – 0:15', 'Look at a real website and name the parts you can see'],
                ['0:15 – 0:45', 'Browser, server, and the HTML skeleton'],
                ['0:45 – 1:40', 'Build and refresh your first page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit today\'s page'],
            ])
            .$h2('Three jobs')
            .$table(['Part', 'Job', 'Example'], [
                ['Browser', 'Asks for a page and draws it', 'Chrome, Edge, Firefox'],
                ['Server', 'Stores the files and sends them', 'The computer that hosts the site'],
                ['HTML file', 'Describes the content', 'index.html'],
            ])
            .$box('key', 'Key idea', '<p>HTML is not a picture. It is a set of <strong>tags</strong> that label content: this is a heading, this is a paragraph. The browser reads the tags and draws the page.</p>')
            .$h2('The smallest real page')
            .$p('Every HTML page starts the same way. Type this exactly, then change the words inside the tags.')
            .$pre("<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <title>My first page</title>\n</head>\n<body>\n  <h1>Hello, I am Amina</h1>\n  <p>I am at Code Academy Uganda, learning to build websites.</p>\n</body>\n</html>")
            .$h3('What each line does')
            .$table(['Line', 'Meaning'], [
                ['&lt;!DOCTYPE html&gt;', 'Tells the browser this is modern HTML'],
                ['&lt;html lang="en"&gt;', 'The page starts here. lang says the language is English'],
                ['&lt;head&gt;', 'Information about the page, not the visible content'],
                ['&lt;title&gt;', 'The name on the browser tab'],
                ['&lt;body&gt;', 'Everything the visitor sees'],
                ['&lt;h1&gt;', 'The main heading. A page should have one'],
                ['&lt;p&gt;', 'A paragraph'],
            ])
            .$box('tip', 'Tip', '<p>Most tags come in pairs: an opening tag <code>&lt;p&gt;</code> and a closing tag <code>&lt;/p&gt;</code>. The slash means “stop”.</p>')
            .$box('try', 'Try it', $steps([
                'Create a folder called <strong>web1-day1</strong> on the desktop.',
                'Open it in your editor and create <strong>index.html</strong>.',
                'Paste the skeleton. Put your own name in the heading.',
                'Save. Open the file in the browser (double-click, or right-click → Open with).',
                'Change the paragraph, save, and press <strong>F5</strong> to refresh.',
            ]))
            .$box('warn', 'Watch out', '<p>If the page looks like code instead of a heading, the file was saved as <code>index.html.txt</code>. In the save box, set the type to “All files” and the name to <code>index.html</code>.</p>')
            .$check(['Save a file that ends in .html', 'See your name as a large heading', 'Change a sentence and refresh', 'Point to the title on the browser tab'])
            .$sources($mdn),
        'assignment' => [
            'title' => 'Day 1 project: My first page',
            'description' => '<p>Submit the HTML page you built in class. It must open in a browser and show your name.</p>',
            'instructions' => "Upload index.html. The page must have a doctype, a title, one h1 with your name, and one paragraph about you or the camp. A screenshot of the page in the browser is welcome but the HTML file is required.",
            'prompt_question' => 'What is one tag you used today, and what does it do?',
        ],
    ],
    [
        'title' => 'Day 2: Headings, Paragraphs, and Lists',
        'summary' => 'Structure a page so a reader can scan it: headings, paragraphs, and lists.',
        'question_of_day' => 'Why do books use chapters and bullet points instead of one long block of text?',
        'objectives' => "Choose the right heading level from h1 to h3.\nWrite paragraphs and line breaks on purpose.\nBuild a bulleted list and a numbered list.\nUse strong and em without faking a heading.",
        'guidance' => "Start with a bad page: one giant paragraph. Ask students to find the title, the steps, and the list of tools. Then rebuild it with headings and lists. 40 minutes of teaching, 50 minutes building an About Me page, then quiz and submit. Do not let them use h1 for every line.",
        'content' => $hero(2, 'Headings, paragraphs, and lists', 'A page should be easy to scan, not a wall of text.')
            .$plan([
                ['0:00 – 0:15', 'Read a messy paragraph and say what is missing'],
                ['0:15 – 0:50', 'Headings, paragraphs, bold, italic, and lists'],
                ['0:50 – 1:40', 'Build an About Me page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$h2('Headings make an outline')
            .$p('<code>h1</code> is the title of the page. <code>h2</code> is a section. <code>h3</code> is a smaller part inside that section. Do not skip levels, and do not use a heading just to make text big.')
            .$pre("<h1>Amina Nakato</h1>\n<h2>About me</h2>\n<p>I am 13 and I live in Kampala. I like drawing and football.</p>\n<h2>What I want to build</h2>\n<p>A page for my school coding club.</p>")
            .$h2('Two kinds of lists')
            .$table(['Tag', 'Use it when', 'Example'], [
                ['ul + li', 'The order does not matter', 'Favourite foods'],
                ['ol + li', 'The order matters', 'Steps in a recipe'],
            ])
            .$pre("<h2>Tools I am using</h2>\n<ul>\n  <li>A browser</li>\n  <li>A code editor</li>\n  <li>HTML</li>\n</ul>\n\n<h2>How I open my page</h2>\n<ol>\n  <li>Save the file.</li>\n  <li>Open it in the browser.</li>\n  <li>Press F5 after each change.</li>\n</ol>")
            .$box('key', 'Key idea', '<p><code>strong</code> means important. <code>em</code> means emphasis. They are not the same as a heading. A heading starts a section. Strong and em sit inside a sentence.</p>')
            .$pre("<p>I am learning <strong>HTML</strong> at Code Academy. My favourite tag so far is <em>the list</em>.</p>")
            .$box('warn', 'Watch out', '<p><code>&lt;br&gt;</code> is a line break. It does not make a new paragraph. Use a new <code>&lt;p&gt;</code> when the idea changes. Use <code>&lt;br&gt;</code> only for something like an address.</p>')
            .$box('try', 'Try it', '<p>Build a page titled with your name. Include an About section (two paragraphs), a “Three things I like” bullet list, and a numbered list of three goals for this camp.</p>')
            .$check(['One h1 only', 'At least two h2 sections', 'One ul and one ol', 'strong or em used inside a paragraph, not as a heading'])
            .$sources(['MDN: HTML text fundamentals' => 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/HTML_text_fundamentals']),
        'assignment' => [
            'title' => 'Day 2 project: About me',
            'description' => '<p>An About Me page that uses headings and both kinds of lists.</p>',
            'instructions' => "Upload the HTML file. Required: one h1, at least two h2 headings, two paragraphs, one bulleted list, and one numbered list. The lists must be real ul/ol tags, not dashes typed by hand.",
            'prompt_question' => 'When would you use a numbered list instead of a bulleted list?',
        ],
    ],
    [
        'title' => 'Day 3: Links and Images',
        'summary' => 'Connect pages with links and add pictures that still make sense if they do not load.',
        'question_of_day' => 'How does a word on one page take you to a different page?',
        'objectives' => "Write a link to another website and a link to another file in your folder.\nAdd an image with a useful alt text.\nExplain the difference between an absolute and a relative URL.\nOpen a link in a new tab only when you mean to.",
        'guidance' => "Bring 2 or 3 photos students can use (camp, logo, a simple icon) so nobody is stuck searching the web. Teach alt text with the screen-reader test: if you cannot see the image, does the alt still tell the truth? Build time is a small gallery page. Check that image paths match the folder.",
        'content' => $hero(3, 'Links and images', 'A website is pages joined together, with pictures that have a purpose.')
            .$plan([
                ['0:00 – 0:15', 'Click around a site and notice what is a link'],
                ['0:15 – 0:50', 'The a tag, URLs, and the img tag'],
                ['0:50 – 1:40', 'Build a small gallery page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the gallery'],
            ])
            .$h2('The link tag')
            .$pre("<a href=\"https://codeacademyug.org\">Code Academy Uganda</a>\n\n<a href=\"about.html\">About me</a>")
            .$table(['href value', 'Where it goes'], [
                ['https://…', 'Another website. This is an absolute URL'],
                ['about.html', 'A file in the same folder. This is a relative URL'],
                ['#projects', 'A spot on the same page with id="projects"'],
            ])
            .$box('warn', 'Watch out', '<p><code>target="_blank"</code> opens a new tab. Use it for links that leave your site. Do not use it for every link. Add <code>rel="noopener"</code> when you use it.</p>')
            .$pre("<a href=\"https://developer.mozilla.org\" target=\"_blank\" rel=\"noopener\">MDN Web Docs</a>")
            .$h2('Images')
            .$pre("<img src=\"images/camp.jpg\" alt=\"Students at Code Academy sitting at laptops\">")
            .$box('key', 'Key idea', '<p><code>src</code> is where the file is. <code>alt</code> is the text that replaces the image if it cannot load, and the text a screen reader speaks. Alt should describe the picture, not say “image of”.</p>')
            .$box('try', 'Try it', $steps([
                'Make a folder <strong>images</strong> next to your HTML file.',
                'Put two pictures in it. Use simple names: <code>me.jpg</code>, <code>camp.jpg</code>.',
                'Show both pictures with honest alt text.',
                'Link your name to <code>about.html</code> (create that second file with one heading).',
                'Link the words “Code Academy” to the real website.',
            ]))
            .$box('tip', 'Tip', '<p>If you see a broken-image icon, the <code>src</code> path is wrong. Check the folder name, the file name, and capital letters. <code>Camp.jpg</code> and <code>camp.jpg</code> are not the same on every computer.</p>')
            .$check(['Two images with alt text', 'One link to another website', 'One link to another HTML file in your folder', 'Both pages open without a broken image'])
            .$sources(['MDN: Creating links' => 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/Creating_hyperlinks', 'MDN: Images' => 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Images_in_HTML']),
        'assignment' => [
            'title' => 'Day 3 project: Photo gallery',
            'description' => '<p>A page with pictures and working links. Zip the HTML and the images folder together.</p>',
            'instructions' => "Upload a zip that contains your HTML file and the images folder. The page needs at least two images with alt text, one external link, and one link to a second HTML page. Do not submit only a screenshot.",
            'prompt_question' => 'What does alt text do, in your own words?',
        ],
    ],
    [
        'title' => 'Day 4: Forms that Collect Information',
        'summary' => 'Build a form that asks for a name, a choice, and a message, with labels the user can click.',
        'question_of_day' => 'When a website asks for your name, what is the page actually collecting?',
        'objectives' => "Build a form with labels connected to inputs.\nUse text, email, number, select, textarea, radio, and checkbox.\nExplain why a label matters.\nName the fields so a later program could read them.",
        'guidance' => "The form does not need a backend. Submitting can show the values in the address bar with method get, which is a good demo. Spend time on label-for and id matching. Walk around and click the label: the cursor should land in the box. Project is a camp registration form.",
        'content' => $hero(4, 'Forms that collect information', 'A form is a set of questions. Labels tell the user, and the browser, what each box is for.')
            .$plan([
                ['0:00 – 0:15', 'Look at a real sign-up form and list the questions it asks'],
                ['0:15 – 0:55', 'form, label, input, select, textarea'],
                ['0:55 – 1:40', 'Build a camp registration form'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the form page'],
            ])
            .$h2('A form is a list of named answers')
            .$pre("<form action=\"#\" method=\"get\">\n  <label for=\"student-name\">Full name</label>\n  <input id=\"student-name\" name=\"student_name\" type=\"text\" required>\n\n  <label for=\"email\">Email</label>\n  <input id=\"email\" name=\"email\" type=\"email\" required>\n\n  <button type=\"submit\">Register</button>\n</form>")
            .$box('key', 'Key idea', '<p>The <code>for</code> on the label must match the <code>id</code> on the input. Then clicking the words “Full name” focuses the box. The <code>name</code> is the key that would be sent with the answer.</p>')
            .$h2('Pick the right input')
            .$table(['Question', 'Type', 'Why'], [
                ['Name', 'text', 'Any short words'],
                ['Email', 'email', 'The browser checks for @'],
                ['Age', 'number', 'Only digits'],
                ['Course', 'select', 'One choice from a list'],
                ['Track', 'radio', 'One choice, all options visible'],
                ['I agree to the rules', 'checkbox', 'Yes or no'],
                ['Why do you want to join?', 'textarea', 'A longer answer'],
            ])
            .$pre("<label for=\"course\">Course</label>\n<select id=\"course\" name=\"course\">\n  <option value=\"\">Choose one</option>\n  <option value=\"web1\">Web Development 1</option>\n  <option value=\"python\">Python</option>\n</select>\n\n<fieldset>\n  <legend>I can attend</legend>\n  <label><input type=\"radio\" name=\"days\" value=\"weekdays\"> Weekdays</label>\n  <label><input type=\"radio\" name=\"days\" value=\"saturday\"> Saturday</label>\n</fieldset>")
            .$box('warn', 'Watch out', '<p>Radio buttons that belong together must share the same <code>name</code>. If each radio has a different name, the user can select all of them.</p>')
            .$box('try', 'Try it', '<p>Build a “Join Code Camp” form: name, email, age, a course dropdown, a weekday/Saturday choice, a checkbox for “I will bring a notebook”, a textarea for a question, and a submit button. Use a label for every field.</p>')
            .$check(['Every input has a label', 'Email field uses type email', 'Radios share one name', 'The submit button is inside the form'])
            .$sources(['MDN: Web forms' => 'https://developer.mozilla.org/en-US/docs/Learn/Forms']),
        'assignment' => [
            'title' => 'Day 4 project: Camp registration form',
            'description' => '<p>A registration form with labels, several input types, and a submit button.</p>',
            'instructions' => "Upload the HTML file. Required fields: text, email, a select or radios, one checkbox, a textarea, and a submit button. Every control needs a working label. You do not need a database.",
            'prompt_question' => 'Why do we connect a label to an input with for and id?',
        ],
    ],
    [
        'title' => 'Day 5: Semantic HTML and the Week 1 Page',
        'summary' => 'Replace a pile of divs with header, nav, main, and footer, then finish a one-page profile.',
        'question_of_day' => 'If every box on a page is called div, how does anyone know which box is the menu?',
        'objectives' => "Name the job of header, nav, main, section, and footer.\nBuild a one-page profile that uses those tags.\nAdd a skip-friendly heading outline.\nSubmit a page another student can open and understand.",
        'guidance' => "This is the week project day. 25 minutes of teaching semantic tags with a diagram on the board. The rest is build time with a checklist on the board. Pair students who are stuck with someone whose page already has a header and a main. Quiz is shorter in spirit but still required before they leave. Collect the HTML file.",
        'content' => $hero(5, 'Semantic HTML and your profile page', 'Tags should say what something is, not only that it is a box.')
            .$plan([
                ['0:00 – 0:20', 'Compare a page of divs with a page of named regions'],
                ['0:20 – 0:40', 'header, nav, main, section, footer'],
                ['0:40 – 1:35', 'Build the Week 1 profile page'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Submit the profile'],
            ])
            .$h2('Name the regions')
            .$table(['Tag', 'What it is'], [
                ['header', 'The top of the page: name and a short line about you'],
                ['nav', 'The links'],
                ['main', 'The unique content of this page'],
                ['section', 'A block inside main with its own heading'],
                ['footer', 'The bottom: camp name, year'],
            ])
            .$pre("<body>\n  <header>\n    <h1>Amina Nakato</h1>\n    <p>Web Development 1 · Kampala</p>\n  </header>\n  <nav>\n    <a href=\"#about\">About</a>\n    <a href=\"#projects\">Projects</a>\n  </nav>\n  <main>\n    <section id=\"about\">\n      <h2>About</h2>\n      <p>I am learning HTML at Code Academy.</p>\n    </section>\n    <section id=\"projects\">\n      <h2>Projects</h2>\n      <p>Day 1 page, About Me, and a photo gallery.</p>\n    </section>\n  </main>\n  <footer>\n    <p>Code Academy Uganda · Web Development 1</p>\n  </footer>\n</body>")
            .$box('key', 'Key idea', '<p>A <code>div</code> is fine when nothing more specific exists. If the box is a header, a menu, or a footer, use the tag that says so. Screen readers and search tools use those names.</p>')
            .$box('try', 'Week 1 page', $steps([
                'Start from your Day 2 or Day 3 page, or a new file.',
                'Wrap the top in <strong>header</strong> and the links in <strong>nav</strong>.',
                'Put the real content in <strong>main</strong>, split into two <strong>section</strong>s.',
                'Add a <strong>footer</strong> with the camp name.',
                'Include one image with alt text and one list.',
                'Open it in the browser and read it out loud. Does the order make sense?',
            ]))
            .$check(['header, nav, main, and footer are all present', 'Each section has a heading', 'One image with alt', 'One list', 'The page still works with no CSS'])
            .$sources(['MDN: Semantic HTML' => 'https://developer.mozilla.org/en-US/docs/Glossary/Semantics#semantics_in_html']),
        'assignment' => [
            'title' => 'Week 1 project: Personal profile page',
            'description' => '<p>One HTML page that uses semantic tags. This is the page you will style next week.</p>',
            'instructions' => "Upload the HTML file (and images in a zip if you used any). Required: header, nav, main, at least two sections, footer, one image with alt text, and one list. No CSS is required today.",
            'prompt_question' => 'Which semantic tag did you use, and what job does it do on your page?',
        ],
    ],
];
