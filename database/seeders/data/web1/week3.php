<?php

$b = require __DIR__.'/blocks.php';
extract($b);

$cdn = "<!-- in the head -->\n<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n\n<!-- before </body> -->\n<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>";

return [
    [
        'title' => 'Day 11: Bootstrap Grid and Containers',
        'summary' => 'Stop hand-writing every row. Use Bootstrap’s container and 12-column grid.',
        'question_of_day' => 'What if a library already knew how to make a row of columns that stacks on a phone?',
        'objectives' => "Add Bootstrap 5 with the CDN links.\nPut page content in a container.\nBuild a row with columns that stack on small screens.\nRead a class name such as col-md-4.",
        'guidance' => "Internet is required for the CDN. If the lab is offline, download bootstrap.min.css and the bundle once and link locally. Teach the 12 columns with a sketch: 12, 6+6, 4+4+4. Do not restyle everything today. One container, one row, three columns.",
        'content' => $hero(11, 'Bootstrap grid and containers', 'Bootstrap is a CSS library. You add classes instead of writing the layout from zero.')
            .$plan([
                ['0:00 – 0:15', 'Compare your handmade flex row with a Bootstrap row'],
                ['0:15 – 0:45', 'CDN, container, row, and the 12 columns'],
                ['0:45 – 1:40', 'Rebuild three cards with the grid'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the page'],
            ])
            .$h2('Add Bootstrap 5')
            .$pre($cdn)
            .$box('key', 'Key idea', '<p>A Bootstrap <strong>row</strong> is 12 columns wide. <code>col-md-4</code> means “from the medium breakpoint up, take 4 of the 12”. Three of those sit on one line because 4 + 4 + 4 = 12. Below that breakpoint they stack.</p>')
            .$pre("<div class=\"container py-4\">\n  <h1>Code Camp projects</h1>\n  <div class=\"row g-3\">\n    <div class=\"col-md-4\">\n      <p>HTML profile</p>\n    </div>\n    <div class=\"col-md-4\">\n      <p>Gallery</p>\n    </div>\n    <div class=\"col-md-4\">\n      <p>Registration form</p>\n    </div>\n  </div>\n</div>")
            .$table(['Class', 'Meaning'], [
                ['container', 'A centred column with side padding'],
                ['row', 'A horizontal group of columns'],
                ['col-12', 'Full width at every size'],
                ['col-md-6', 'Half width from 768px up'],
                ['g-3', 'A gap between the columns'],
                ['py-4', 'Padding on the top and bottom'],
            ])
            .$box('try', 'Try it', '<p>Put your three project descriptions in <code>col-md-4</code> columns inside a container. On a narrow window they must stack. On a wide window they must share one row.</p>')
            .$box('warn', 'Watch out', '<p>Columns must be direct children of a <code>row</code>. A column outside a row will not line up. Also keep your own <code>styles.css</code> linked after Bootstrap if you still want your colours.</p>')
            .$check(['Bootstrap CSS is linked in the head', 'Content sits in a container', 'A row holds columns that add up to 12', 'The columns stack on a phone width'])
            .$sources(['Bootstrap: Grid' => 'https://getbootstrap.com/docs/5.3/layout/grid/']),
        'assignment' => [
            'title' => 'Day 11 project: Three columns with the grid',
            'description' => '<p>A Bootstrap page with a container and a row of three columns.</p>',
            'instructions' => "Upload the HTML file. It must load Bootstrap 5, use a container, and show three col-md-4 columns that stack on a small screen. You may keep a small styles.css for colours.",
            'prompt_question' => 'What does the 4 in col-md-4 mean?',
        ],
    ],
    [
        'title' => 'Day 12: Buttons, Cards, and Forms',
        'summary' => 'Turn plain boxes into Bootstrap cards and rebuild the registration form with form classes.',
        'question_of_day' => 'Which parts of a sign-up page repeat on almost every website?',
        'objectives' => "Style a button with btn and a colour class.\nBuild a card with a title, text, and a button.\nLay out a form with form-label and form-control.\nKeep the label connected to the input.",
        'guidance' => "Cards are the visual win of the day. Live-code one card, then students convert yesterday's three columns into cards. The second half is the Day 4 form restyled. Insist that for/id still exists. Bootstrap does not replace the HTML rules from week 1.",
        'content' => $hero(12, 'Buttons, cards, and forms', 'Bootstrap components are HTML patterns with class names you can reuse.')
            .$plan([
                ['0:00 – 0:10', 'Point at buttons and cards on a site you know'],
                ['0:10 – 0:45', 'btn, card, form-control'],
                ['0:45 – 1:35', 'Three cards and a registration form'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Submit'],
            ])
            .$h2('A card')
            .$pre("<div class=\"card h-100\">\n  <div class=\"card-body\">\n    <h2 class=\"card-title h5\">HTML profile</h2>\n    <p class=\"card-text\">A one-page profile built with semantic tags.</p>\n    <a class=\"btn btn-primary\" href=\"profile.html\">Open</a>\n  </div>\n</div>")
            .$h2('A form that lines up')
            .$pre("<form class=\"mt-4\">\n  <div class=\"mb-3\">\n    <label class=\"form-label\" for=\"student-name\">Full name</label>\n    <input class=\"form-control\" id=\"student-name\" name=\"student_name\" type=\"text\" required>\n  </div>\n  <div class=\"mb-3\">\n    <label class=\"form-label\" for=\"email\">Email</label>\n    <input class=\"form-control\" id=\"email\" name=\"email\" type=\"email\" required>\n  </div>\n  <button class=\"btn btn-primary\" type=\"submit\">Register</button>\n</form>")
            .$box('key', 'Key idea', '<p><code>btn btn-primary</code> is a filled button in the theme colour. <code>btn btn-outline-primary</code> is the outline version. <code>mb-3</code> is margin under a field so the form can breathe. The <code>for</code> and <code>id</code> from week 1 are still required.</p>')
            .$box('try', 'Try it', '<p>Turn your three columns into cards with a title, one sentence, and a button. Under the row, add a short “Ask a question” form with two fields and a submit button. Every field needs a label.</p>')
            .$check(['Three cards with the card class', 'A primary button', 'A form using form-control', 'Labels still use for and id'])
            .$sources(['Bootstrap: Cards' => 'https://getbootstrap.com/docs/5.3/components/card/', 'Bootstrap: Forms' => 'https://getbootstrap.com/docs/5.3/forms/overview/']),
        'assignment' => [
            'title' => 'Day 12 project: Cards and a form',
            'description' => '<p>A Bootstrap page with three cards and a labelled form.</p>',
            'instructions' => "Upload the HTML. Required: three cards, one btn-primary, and a form with at least two labelled inputs using form-label and form-control.",
            'prompt_question' => 'Which Bootstrap classes did you add to an input, and what did each one change?',
        ],
    ],
    [
        'title' => 'Day 13: A Navbar that Works on a Phone',
        'summary' => 'Replace the hand-made menu with a Bootstrap navbar that collapses.',
        'question_of_day' => 'Where does a menu go when the screen is too narrow for every link?',
        'objectives' => "Build a navbar with a brand and three links.\nMake it collapse behind a button on a small screen.\nUse the Bootstrap JavaScript bundle so the button works.\nMark the current page with aria-current.",
        'guidance' => "The collapse button fails when the script tag is missing or the data-bs-target does not match the id. Write those two ids on the board and make students check them before asking for help. Test on a narrow window: the links hide, the button shows them.",
        'content' => $hero(13, 'A navbar that works on a phone', 'The menu stays usable when the links no longer fit.')
            .$plan([
                ['0:00 – 0:15', 'Shrink the window until a normal menu breaks'],
                ['0:15 – 0:50', 'navbar, toggler, and collapse'],
                ['0:50 – 1:35', 'Add the navbar to your camp page'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Submit'],
            ])
            .$pre("<nav class=\"navbar navbar-expand-md navbar-dark bg-primary\">\n  <div class=\"container\">\n    <a class=\"navbar-brand\" href=\"index.html\">Code Camp</a>\n    <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#main-nav\" aria-controls=\"main-nav\" aria-expanded=\"false\" aria-label=\"Open the menu\">\n      <span class=\"navbar-toggler-icon\"></span>\n    </button>\n    <div class=\"collapse navbar-collapse\" id=\"main-nav\">\n      <ul class=\"navbar-nav ms-auto\">\n        <li class=\"nav-item\"><a class=\"nav-link active\" aria-current=\"page\" href=\"index.html\">Home</a></li>\n        <li class=\"nav-item\"><a class=\"nav-link\" href=\"projects.html\">Projects</a></li>\n        <li class=\"nav-item\"><a class=\"nav-link\" href=\"contact.html\">Contact</a></li>\n      </ul>\n    </div>\n  </div>\n</nav>")
            .$box('key', 'Key idea', '<p><code>data-bs-target="#main-nav"</code> must match <code>id="main-nav"</code>. The Bootstrap JavaScript file must be at the bottom of the body. Without it, the button does nothing.</p>')
            .$box('warn', 'Watch out', '<p><code>navbar-expand-md</code> means the menu is open from the medium breakpoint up, and collapsed below that. If you forget <code>navbar-expand-md</code>, the menu stays collapsed even on a laptop.</p>')
            .$box('try', 'Try it', '<p>Put this navbar on your page. Change the brand to your name. Make sure Home, Projects, and Contact are real links, even if Projects and Contact are still simple pages with one heading. Test the button at phone width.</p>')
            .$check(['The brand and three links are visible on a wide screen', 'On a narrow screen the links hide behind the button', 'The button opens and closes the menu', 'The script tag is before </body>'])
            .$sources(['Bootstrap: Navbar' => 'https://getbootstrap.com/docs/5.3/components/navbar/']),
        'assignment' => [
            'title' => 'Day 13 project: Collapsing navbar',
            'description' => '<p>A page whose menu collapses on a small screen.</p>',
            'instructions' => "Upload the HTML. Required: a navbar with a brand and at least three links, a toggler button, a matching collapse id, and the Bootstrap JavaScript bundle. The button must open the menu.",
            'prompt_question' => 'What happened when you clicked the menu button, and what would break that click?',
        ],
    ],
    [
        'title' => 'Day 14: Utilities, Alerts, and a Modal',
        'summary' => 'Use spacing and colour utilities, then add an alert and a modal dialog.',
        'question_of_day' => 'How do you show a short warning, or a popup, without writing a new CSS file?',
        'objectives' => "Use margin, padding, and text utilities instead of one-off CSS.\nAdd a success or info alert.\nOpen a modal from a button.\nSay when a utility is better than a new CSS rule.",
        'guidance' => "Utilities are a vocabulary lesson: m, p, t, b, x, y, and the 0–5 scale. Do not make them memorise every class. Modal needs the same JS bundle as the navbar. If the modal does not open, check data-bs-toggle and data-bs-target first.",
        'content' => $hero(14, 'Utilities, alerts, and a modal', 'Small classes handle spacing. Components handle alerts and popups.')
            .$plan([
                ['0:00 – 0:10', 'Find spacing on your page that you wrote by hand in CSS'],
                ['0:10 – 0:40', 'Spacing utilities, alerts, badges'],
                ['0:40 – 1:30', 'Add an alert and a project modal'],
                ['1:30 – 1:45', 'Lesson quiz'],
                ['1:45 – 2:00', 'Submit and start thinking about tomorrow\'s site'],
            ])
            .$h2('Spacing without a new rule')
            .$table(['Class', 'Effect'], [
                ['mt-4', 'Margin on top, step 4'],
                ['mb-3', 'Margin on the bottom'],
                ['px-3', 'Padding on the left and right'],
                ['py-5', 'Padding on the top and bottom'],
                ['text-center', 'Centre the text'],
                ['text-bg-primary', 'Primary background with readable text'],
            ])
            .$pre("<div class=\"alert alert-info\" role=\"alert\">\n  Camp runs for three weeks. Bring your page from yesterday.\n</div>\n\n<span class=\"badge text-bg-primary\">Web 1</span>")
            .$h2('A modal')
            .$pre("<button class=\"btn btn-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#project-modal\">\n  See the project brief\n</button>\n\n<div class=\"modal fade\" id=\"project-modal\" tabindex=\"-1\">\n  <div class=\"modal-dialog\">\n    <div class=\"modal-content\">\n      <div class=\"modal-header\">\n        <h2 class=\"modal-title h5\">Final project</h2>\n        <button class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n      </div>\n      <div class=\"modal-body\">\n        <p>Tomorrow you will publish a three-page camp site.</p>\n      </div>\n    </div>\n  </div>\n</div>")
            .$box('key', 'Key idea', '<p>Use a utility when the job is spacing, alignment, or a colour you will not reuse as a design. Write a CSS class when the same look belongs to your site, such as a special card. Do not fight Bootstrap by restyling <code>.btn</code> from scratch.</p>')
            .$box('try', 'Try it', '<p>Add an info alert under your navbar. Add a badge on one card. Add a button that opens a modal describing one project you built this camp. Close the modal with the X.</p>')
            .$check(['An alert is visible without custom CSS', 'A button opens a modal', 'The modal closes', 'You used at least two spacing utilities'])
            .$sources(['Bootstrap: Utilities' => 'https://getbootstrap.com/docs/5.3/utilities/spacing/', 'Bootstrap: Modal' => 'https://getbootstrap.com/docs/5.3/components/modal/']),
        'assignment' => [
            'title' => 'Day 14 project: Alert and modal',
            'description' => '<p>Your camp page plus an alert and a working modal.</p>',
            'instructions' => "Upload the HTML. Required: one alert, one button that opens a modal, and the Bootstrap JavaScript bundle so the modal works. Use at least two spacing utilities such as mt-4 or mb-3.",
            'prompt_question' => 'When would you use a Bootstrap utility instead of writing a CSS rule?',
        ],
    ],
    [
        'title' => 'Day 15: Final Project — A Three-Page Camp Site',
        'summary' => 'Publish Home, Projects, and Contact with one shared navbar, then sit a review quiz.',
        'question_of_day' => 'What would you show a parent to prove you can build a website?',
        'objectives' => "Ship three HTML pages that share a navbar.\nUse the grid, cards, and a form on the right pages.\nCheck phone width on every page.\nSubmit a zip and explain one choice you made.",
        'guidance' => "Build day. First 15 minutes: agree the three pages and the colours so they do not redesign for an hour. Home = hero text plus three cards. Projects = the work from the camp. Contact = the form. Navbar links must work both ways. At 1:20 stop building and start the review quiz (15 random questions from the whole bank). Collect zips before they leave. Help with links between files, not with new components.",
        'content' => $hero(15, 'A three-page camp site', 'This is the page you show someone. It uses everything from the three weeks.')
            .$plan([
                ['0:00 – 0:15', 'Sketch the three pages and pick one accent colour'],
                ['0:15 – 1:20', 'Build Home, Projects, and Contact'],
                ['1:20 – 1:45', 'Review quiz from the whole course'],
                ['1:45 – 2:00', 'Zip the site and submit'],
            ])
            .$h2('The three pages')
            .$table(['File', 'What is on it'], [
                ['index.html', 'Your name, a one-sentence welcome, and three cards'],
                ['projects.html', 'At least three projects from this camp, each in a card'],
                ['contact.html', 'A form with name, email, and a message'],
            ])
            .$box('key', 'Key idea', '<p>Copy the same navbar onto each page. Change <code>aria-current="page"</code> and the <code>active</code> class so the link for <em>this</em> page is the one that looks selected. Relative links (<code>projects.html</code>) must work from every page.</p>')
            .$pre("final-site/\n  index.html\n  projects.html\n  contact.html\n  images/\n    project-1.jpg")
            .$h2('Done enough to submit')
            .$check([
                'All three pages open from the navbar',
                'Bootstrap grid is used on the home page',
                'Projects are cards, not a single paragraph',
                'The contact form has labels',
                'Each page is readable when the window is narrow',
                'The zip includes every HTML file and the images folder',
            ])
            .$box('teacher', 'Before you zip', '<p>Open <code>index.html</code> from the zip on a different computer, or ask a partner to open it. If the navbar or pictures break, the paths are wrong. Fix that before you submit.</p>')
            .$sources(['Bootstrap docs' => 'https://getbootstrap.com/docs/5.3/getting-started/introduction/', 'MDN: Structuring a page' => 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/Document_and_website_structure']),
        'assignment' => [
            'title' => 'Final project: Three-page camp site',
            'description' => '<p>Home, Projects, and Contact, built with Bootstrap and linked by one navbar.</p>',
            'instructions' => "Upload one zip containing index.html, projects.html, contact.html, and any images. The navbar must link all three pages. Home uses the grid and cards. Contact is a labelled form. Pages must be readable on a narrow screen. A screenshot of each page is useful; the HTML is required.",
            'prompt_question' => 'Which page are you most proud of, and what would you fix next?',
        ],
    ],
];
