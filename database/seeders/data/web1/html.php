<?php

$b = require __DIR__.'/blocks.php';
extract($b);

$mdn = ['MDN: HTML basics' => 'https://developer.mozilla.org/en-US/docs/Learn/Getting_started_with_the_web/HTML_basics'];

return [
    [
        'title' => 'Introduction to HTML and Web Development',
        'summary' => 'Open a browser, meet HTML, and publish a first page that says who you are.',
        'question_of_day' => 'When you type a website address, what is the computer actually showing you?',
        'objectives' => "Explain what a browser, a server, and HTML each do.\nWrite a complete HTML page with doctype, html, head, and body.\nSave a .html file and open it in the browser.\nChange the page and refresh to see the update.",
        'guidance' => "0–15 min: show codeacademyug.org and ask what they see. 15–40: draw browser → internet → server → HTML file. 40–70: everyone types the skeleton together, save as index.html, open it. 70–100: personalise the page. 100–115: quiz. 115–120: upload. Common mistake: saving as index.html.txt.",
        'content' => $hero('Lesson 1 · 2 hours', 'How the web works and your first page', 'HTML is the language that tells the browser what is on the page.')
            .$plan([
                ['0:00 – 0:15', 'Look at a real website and name the parts you can see'],
                ['0:15 – 0:45', 'Browser, server, and the HTML skeleton'],
                ['0:45 – 1:40', 'Build and refresh your first page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit today’s page'],
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
                'Create a folder called <strong>web1</strong> on the desktop.',
                'Create <strong>index.html</strong> inside it.',
                'Paste the skeleton. Put your own name in the heading.',
                'Save. Open the file in the browser.',
                'Change the paragraph, save, and press <strong>F5</strong>.',
            ]))
            .$box('warn', 'Watch out', '<p>If the page looks like code instead of a heading, the file was saved as <code>index.html.txt</code>. In the save box, set the type to “All files” and the name to <code>index.html</code>.</p>')
            .$check(['Save a file that ends in .html', 'See your name as a large heading', 'Change a sentence and refresh', 'Point to the title on the browser tab'])
            .$sources($mdn),
        'assignment' => [
            'title' => 'Project: My first page',
            'description' => '<p>Submit the HTML page you built in class. It must open in a browser and show your name.</p>',
            'instructions' => 'Upload index.html. The page must have a doctype, a title, one h1 with your name, and one paragraph about you or the camp.',
            'prompt_question' => 'What is one tag you used today, and what does it do?',
        ],
    ],
    [
        'title' => 'HTML Elements: Headings, Paragraphs, and Text Formatting',
        'summary' => 'Structure a page so a reader can scan it: headings, paragraphs, emphasis, and lists.',
        'question_of_day' => 'Why do books use chapters and bullet points instead of one long block of text?',
        'objectives' => "Choose the right heading level from h1 to h3.\nWrite paragraphs on purpose, and use br only for a real line break.\nMark important words with strong and em.\nBuild a bulleted list and a numbered list.",
        'guidance' => "Start with one giant paragraph. Ask students to find the title, the steps, and the tools. Then rebuild it. Do not let them use h1 for every line, or strong as a fake heading.",
        'content' => $hero('Lesson 2 · 2 hours', 'Headings, paragraphs, and lists', 'A page should be easy to scan, not a wall of text.')
            .$plan([
                ['0:00 – 0:15', 'Read a messy paragraph and say what is missing'],
                ['0:15 – 0:50', 'Headings, paragraphs, bold, italic, and lists'],
                ['0:50 – 1:40', 'Build an About Me page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$h2('Headings make an outline')
            .$p('<code>h1</code> is the title of the page. <code>h2</code> is a section. <code>h3</code> is a smaller part inside that section. Do not skip levels, and do not use a heading just to make text big.')
            .$pre("<h1>Amina Nakato</h1>\n<h2>About me</h2>\n<p>I am in Kampala. I like drawing and football.</p>\n<h2>What I want to build</h2>\n<p>A page for my school coding club.</p>")
            .$h2('Emphasis is not a heading')
            .$table(['Tag', 'Use it for', 'Do not use it for'], [
                ['strong', 'Words that are important', 'A section title'],
                ['em', 'Words you would stress if you read aloud', 'Making a whole paragraph italic'],
                ['br', 'A line break inside one thought, like an address', 'Starting a new paragraph'],
            ])
            .$h2('Two kinds of lists')
            .$table(['Tag', 'Use it when', 'Example'], [
                ['ul + li', 'The order does not matter', 'Favourite foods'],
                ['ol + li', 'The order matters', 'Steps to save a file'],
            ])
            .$pre("<h2>Tools I used</h2>\n<ul>\n  <li>A browser</li>\n  <li>A text editor</li>\n</ul>\n<h2>How I opened the page</h2>\n<ol>\n  <li>Save the file.</li>\n  <li>Double-click index.html.</li>\n  <li>Press F5 after each change.</li>\n</ol>")
            .$box('try', 'Try it', $steps([
                'Open yesterday’s index.html.',
                'Add an h1 with your name and two h2 sections: About me, and What I am learning.',
                'Add a ul of three things you like, and an ol of three steps.',
                'Mark one important word with strong. Do not put strong around a whole heading.',
            ]))
            .$box('warn', 'Watch out', '<p>A line that starts with a dash is not a list. The browser only treats it as a list when you use <code>ul</code> or <code>ol</code> and <code>li</code>.</p>')
            .$check(['One h1 only', 'At least two h2 sections', 'One bulleted list and one numbered list', 'strong or em on a word, not on a heading'])
            .$sources($mdn),
        'assignment' => [
            'title' => 'Project: About me',
            'description' => '<p>Submit an About Me page that a stranger can scan in ten seconds.</p>',
            'instructions' => 'Upload index.html. It needs one h1, at least two h2 sections, a paragraph, a ul, and an ol. Use strong or em once.',
            'prompt_question' => 'Where did you use a list, and why was it ul or ol?',
        ],
    ],
    [
        'title' => 'Links, Images, and Media',
        'summary' => 'Connect pages with links and add images that still make sense if they do not load.',
        'question_of_day' => 'How does a word on one page open a different page?',
        'objectives' => "Write a link to another page in the same folder.\nWrite a link that opens a real website.\nShow an image with src and a useful alt.\nKnow when a video or audio element is the right tool.",
        'guidance' => "Everyone needs a second HTML file and one image in the same folder. Walk around and check relative paths. The usual bug is an image that works on their computer because they pasted a full C:\\ path.",
        'content' => $hero('Lesson 3 · 2 hours', 'Links and images', 'A website is pages that point at each other, plus the pictures on those pages.')
            .$plan([
                ['0:00 – 0:15', 'Click a link on a real site and watch the address change'],
                ['0:15 – 0:50', 'href, relative paths, src, and alt'],
                ['0:50 – 1:40', 'Build a small gallery: two pages and two images'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the folder as a zip'],
            ])
            .$h2('A link is an address')
            .$pre("<a href=\"projects.html\">My projects</a>\n<a href=\"https://codeacademyug.org\">Code Academy</a>\n<a href=\"mailto:amina@example.com\">Email me</a>")
            .$table(['href value', 'Where it goes'], [
                ['projects.html', 'A file in the same folder'],
                ['images/camp.jpg', 'A file inside the images folder'],
                ['https://...', 'Another website'],
                ['mailto:name@example.com', 'The visitor’s email app'],
            ])
            .$h2('An image needs a source and a description')
            .$pre("<img src=\"images/camp.jpg\" alt=\"Students at a laptop during Code Camp\">")
            .$box('key', 'Key idea', '<p><code>alt</code> is the words a screen reader speaks, and the words a visitor sees if the picture fails. Describe the picture. Do not write “image” or leave it empty unless the picture is purely decoration.</p>')
            .$h3('Sound and video')
            .$p('Use <code>audio</code> or <code>video</code> when the file is yours and you have the right to show it. A YouTube clip is usually a link, not a file you copy into the folder.')
            .$pre("<video src=\"demo.mp4\" controls></video>\n<audio src=\"intro.mp3\" controls></audio>")
            .$box('try', 'Try it', $steps([
                'Create <strong>images</strong> inside your web1 folder and put two photos there.',
                'On index.html, add an image with a real alt sentence.',
                'Create projects.html with an h1 and a link back to index.html.',
                'On index.html, link to projects.html and to https://codeacademyug.org.',
                'Open index.html and click every link. Both photos must show.',
            ]))
            .$box('warn', 'Watch out', '<p>A path like <code>C:\\Users\\Amina\\camp.jpg</code> works only on that one computer. Use <code>images/camp.jpg</code> so the page still works for your teacher.</p>')
            .$check(['Two HTML files that link to each other', 'Two images that load from the images folder', 'Every image has an alt sentence', 'One link to a website, written with https://'])
            .$sources(['MDN: Creating links' => 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/Creating_hyperlinks']),
        'assignment' => [
            'title' => 'Project: Two pages and a gallery',
            'description' => '<p>Submit a zip of two HTML pages and the images they use.</p>',
            'instructions' => 'Zip the folder. index.html must link to projects.html and show at least two images with alt text. projects.html must link back home. Do not include C:\\ paths.',
            'prompt_question' => 'What is the difference between href and src?',
        ],
    ],
    [
        'title' => 'HTML Forms and Input Elements',
        'summary' => 'Build a form that asks for a name, an email, a choice, and a message. It does not need a server yet.',
        'question_of_day' => 'What has to be on the page before a visitor can send you information?',
        'objectives' => "Wrap inputs in a form.\nConnect every control to a label.\nUse the right input type for email, choice, and a longer message.\nName each control so a later program can tell them apart.",
        'guidance' => "Build the camp registration form together. Stress label-for and id. Do not pretend the form emails anyone. Say clearly: today the browser only collects the answers. A later course sends them.",
        'content' => $hero('Lesson 4 · 2 hours', 'Forms that collect information', 'A form is a set of questions. Each question needs a label and a name.')
            .$plan([
                ['0:00 – 0:15', 'Look at a signup form and list the questions it asks'],
                ['0:15 – 0:55', 'form, label, input, textarea, select, and button'],
                ['0:55 – 1:40', 'Build a camp registration form'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$box('key', 'Key idea', '<p>The <code>label</code> is what the human reads. The <code>name</code> is what a program would store. The <code>id</code> connects the label to the control. Clicking the label should focus the box.</p>')
            .$pre("<form action=\"#\" method=\"post\">\n  <label for=\"fullname\">Full name</label>\n  <input id=\"fullname\" name=\"fullname\" type=\"text\" required>\n\n  <label for=\"email\">Email</label>\n  <input id=\"email\" name=\"email\" type=\"email\" required>\n\n  <label for=\"track\">Track</label>\n  <select id=\"track\" name=\"track\">\n    <option>Web 1</option>\n    <option>Scratch</option>\n  </select>\n\n  <label for=\"note\">Anything we should know?</label>\n  <textarea id=\"note\" name=\"note\" rows=\"4\"></textarea>\n\n  <button type=\"submit\">Register</button>\n</form>")
            .$table(['Type', 'Use it for'], [
                ['text', 'A short answer, such as a name'],
                ['email', 'An email address. The browser checks the shape'],
                ['number', 'An age or a quantity'],
                ['checkbox', 'Yes/no choices. Many can be ticked'],
                ['radio', 'One choice from a group. Share the same name'],
                ['textarea', 'A longer message'],
            ])
            .$box('try', 'Try it', $steps([
                'Add a registration section to a new file, register.html.',
                'Ask for name, email, track, and a short note.',
                'Click each label. The matching box must focus.',
                'Submit with an email missing the @ and notice the browser’s warning.',
            ]))
            .$box('warn', 'Watch out', '<p><code>action="#"</code> means “stay on this page”. The answers are not saved on a server yet. Do not tell a visitor their registration was received.</p>')
            .$check(['Every control has a label', 'Clicking the label focuses the control', 'Email uses type="email"', 'The button is type="submit" inside the form'])
            .$sources(['MDN: Your first form' => 'https://developer.mozilla.org/en-US/docs/Learn/Forms']),
        'assignment' => [
            'title' => 'Project: Camp registration form',
            'description' => '<p>Submit a registration form. It does not need a backend.</p>',
            'instructions' => 'Upload the HTML file. Include name, email, one choice (select or radio), a textarea, and a submit button. Every control needs a label and a name.',
            'prompt_question' => 'Which input type did you choose for email, and why not type="text"?',
        ],
    ],
    [
        'title' => 'HTML5 Semantic Elements',
        'summary' => 'Replace a pile of anonymous boxes with header, nav, main, section, and footer.',
        'question_of_day' => 'If you delete the colours, can someone still tell which part is the menu?',
        'objectives' => "Name the job of header, nav, main, section, article, and footer.\nPut the main content in main, once.\nKeep navigation links in nav.\nExplain why a div is not a substitute for those tags.",
        'guidance' => "Take yesterday’s page, which is probably one long body, and wrap the regions. Draw the page as a stack: header, nav, main, footer. One main only.",
        'content' => $hero('Lesson 5 · 2 hours', 'Semantic HTML', 'Semantic tags say what a region is for, not what it looks like.')
            .$plan([
                ['0:00 – 0:15', 'Sketch a page and label the regions in plain words'],
                ['0:15 – 0:45', 'header, nav, main, section, article, footer'],
                ['0:45 – 1:40', 'Rebuild the About page with those regions'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$table(['Tag', 'What it means'], [
                ['header', 'The top of the page or of a section: title and intro'],
                ['nav', 'A block of navigation links'],
                ['main', 'The content that is unique to this page. Use it once'],
                ['section', 'A grouped topic, usually with a heading'],
                ['article', 'A piece that would still make sense on its own, like a news story'],
                ['footer', 'The close: contact, copyright, or links'],
            ])
            .$pre("<body>\n  <header>\n    <h1>Amina Nakato</h1>\n    <p>Web Development 1 · Kampala</p>\n  </header>\n  <nav>\n    <a href=\"index.html\">Home</a>\n    <a href=\"projects.html\">Projects</a>\n  </nav>\n  <main>\n    <section>\n      <h2>About me</h2>\n      <p>I am learning HTML at Code Camp.</p>\n    </section>\n  </main>\n  <footer>\n    <p>Code Academy Uganda</p>\n  </footer>\n</body>")
            .$box('key', 'Key idea', '<p>These tags do not make the page pretty. They make the outline clear to browsers, search engines, and screen readers. Colour and columns come in CSS and Bootstrap.</p>')
            .$box('try', 'Try it', $steps([
                'Open your About page.',
                'Wrap the title in header, the links in nav, the real content in main, and the camp name in footer.',
                'Put each topic inside main in its own section with an h2.',
                'Check there is only one main and only one h1.',
            ]))
            .$box('warn', 'Watch out', '<p>Do not put <code>main</code> inside <code>header</code> or <code>footer</code>. The main content is the middle, not the chrome around it.</p>')
            .$check(['One header, one nav, one main, one footer', 'Sections inside main each have a heading', 'Links live in nav', 'No second h1'])
            .$sources(['MDN: Semantic HTML' => 'https://developer.mozilla.org/en-US/docs/Glossary/Semantics#semantics_in_html']),
        'assignment' => [
            'title' => 'Project: A page with regions',
            'description' => '<p>Submit the About page after you have marked its regions.</p>',
            'instructions' => 'Upload the HTML. The body must use header, nav, main, at least two sections, and footer. One h1. One main.',
            'prompt_question' => 'Why is main better than a div for the middle of the page?',
        ],
    ],
    [
        'title' => "Divs, Spans, Id's, and Classes",
        'summary' => 'Group content with div and span, then name those groups with classes and ids so CSS can find them.',
        'question_of_day' => 'When a tag already says what something is, why would you add a class?',
        'objectives' => "Use div to group block content and span to mark a few words.\nGive a class to things that share a style, and an id to one unique thing.\nWrite class and id names in English, with hyphens, not spaces.\nKeep semantic tags for meaning, and classes for hooks.",
        'guidance' => "This is the bridge into CSS. Students will want to class everything. Show a bad example: class=\"big-red-text\". Then a useful one: class=\"camp-card\". Ids must be unique on the page. Check with a search.",
        'content' => $hero('Lesson 6 · 2 hours', 'Divs, spans, ids, and classes', 'A class is a name you invent so you can style or find a group of elements.')
            .$plan([
                ['0:00 – 0:15', 'Find repeated cards on a website and guess their shared name'],
                ['0:15 – 0:50', 'div, span, class, and id'],
                ['0:50 – 1:40', 'Mark up three project cards'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$table(['Tool', 'What it is', 'Example'], [
                ['div', 'A block box that groups other blocks', 'A card: heading, picture, paragraph'],
                ['span', 'A few words inside a line', 'A price, or a required star'],
                ['class', 'A reusable name. Many elements can share it', 'class="project-card"'],
                ['id', 'A unique name. Use it once on the page', 'id="contact"'],
            ])
            .$pre("<main>\n  <div class=\"project-card\" id=\"gallery\">\n    <h2>Gallery</h2>\n    <p>Photos from <span class=\"highlight\">week 1</span>.</p>\n  </div>\n  <div class=\"project-card\">\n    <h2>Registration</h2>\n    <p>A form for new campers.</p>\n  </div>\n</main>")
            .$box('key', 'Key idea', '<p>Prefer a semantic tag when one exists: <code>nav</code> for links, not <code>div class="nav"</code>. Use <code>div</code> when you only need a box. Use a <code>class</code> when several boxes should be treated the same way later.</p>')
            .$box('tip', 'Tip', '<p>Class names are lowercase with hyphens: <code>project-card</code>, not <code>Project Card</code> and not <code>red</code>. Name the thing, not the colour. The colour will change.</p>')
            .$box('try', 'Try it', $steps([
                'On your portfolio page, wrap each project in a div with class="project-card".',
                'Give the contact section id="contact" and point a nav link at href="#contact".',
                'Wrap one important phrase in span class="highlight".',
                'Search the file and confirm no id is repeated.',
            ]))
            .$box('warn', 'Watch out', '<p>Two elements with the same id make links and labels unreliable. If two cards are the same kind of thing, they share a class. They do not share an id.</p>')
            .$check(['At least two elements share one class', 'One id is used once and a link jumps to it', 'Class names have no spaces', 'header, nav, main, and footer are still there'])
            .$sources(['MDN: Classes and IDs' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/Selectors/Type_Class_and_ID_Selectors']),
        'assignment' => [
            'title' => 'Project: Three cards with classes',
            'description' => '<p>Submit a page that groups three projects with a shared class.</p>',
            'instructions' => 'Upload the HTML. Three divs must share class="project-card". One section must have a unique id, and the nav must link to it with href="#that-id".',
            'prompt_question' => 'Why did those three boxes share a class instead of an id?',
        ],
    ],
    [
        'title' => 'Week 1 Project: Build a Personal Portfolio Page',
        'summary' => 'Put the HTML lessons into one portfolio page: regions, text, links, images, a form, and classes.',
        'question_of_day' => 'Could another student open your folder and understand the page without you explaining it?',
        'objectives' => "Combine header, nav, main, and footer on one page.\nInclude a short bio, a list, three project cards, an image, and a contact form.\nUse relative paths that work from the folder, not from your desktop.\nZip the folder so images travel with the HTML.",
        'guidance' => "This is a build lesson. Ten minutes of checklist, then they work. Teachers circulate with the checklist. Do not introduce new tags except to fix mistakes. Last 20 minutes: quiz and zip.",
        'content' => $hero('Lesson 7 · 2 hours', 'Week 1 portfolio', 'One page that proves you can structure a real document.')
            .$plan([
                ['0:00 – 0:15', 'Read the checklist and open last week’s files'],
                ['0:15 – 1:35', 'Build the portfolio page'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Zip the folder and submit'],
            ])
            .$h2('What the page must contain')
            .$check([
                'A header with your name in one h1',
                'A nav with links to #projects and #contact',
                'A main with an about section, a projects section, and a contact section',
                'A ul or ol in the about section',
                'Three project cards that share a class',
                'At least one image with a useful alt, stored in an images folder',
                'A contact form with name, email, a message, and a submit button',
                'A footer with “Code Academy Uganda”',
            ])
            .$box('key', 'Key idea', '<p>The zip must contain the HTML and the images folder. If you upload only the HTML, the picture will be broken on your teacher’s computer.</p>')
            .$box('try', 'Try it', $steps([
                'Start from your best page so far. Rename it index.html if needed.',
                'Walk the checklist from top to bottom. Do not add new sections until the checklist is done.',
                'Open the page from the folder, click both nav links, and click every label.',
                'Zip the whole folder, not a single file.',
            ]))
            .$box('warn', 'Watch out', '<p>Test the zip by moving it to another folder, extracting it, and opening index.html. If the image breaks there, the path is wrong.</p>')
            .$sources($mdn),
        'assignment' => [
            'title' => 'Project: Personal portfolio page',
            'description' => '<p>Submit the week 1 portfolio as a zip.</p>',
            'instructions' => 'Zip index.html and the images folder. The page must match the checklist in the lesson: one h1, nav, three classed cards, one image with alt, and a labelled contact form.',
            'prompt_question' => 'What did you have to fix when you tested the page from the extracted zip?',
        ],
    ],
];
