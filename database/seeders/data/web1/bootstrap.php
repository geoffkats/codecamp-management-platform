<?php

$b = require __DIR__.'/blocks.php';
extract($b);

$cdn = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
$js = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';

return [
    [
        'title' => 'Getting Started with Bootstrap',
        'summary' => 'Add Bootstrap to a page and use a container plus a simple grid.',
        'question_of_day' => 'Why link a whole library when you already know CSS?',
        'objectives' => "Link the Bootstrap 5 stylesheet.\nWrap the page in a container.\nPlace three columns with the grid on a medium screen.\nKeep your own stylesheet after Bootstrap so you can still override it.",
        'guidance' => "Use the CDN, not a download, so the class has the same version. Their own styles.css must come after the Bootstrap link. Show container versus container-fluid on the projector.",
        'content' => $hero('Lesson 15 · 2 hours', 'Bootstrap on the page', 'Bootstrap is a stylesheet of ready-made classes. You still write the HTML.')
            .$plan([
                ['0:00 – 0:15', 'Name three things you would rather not write by hand'],
                ['0:15 – 0:45', 'The CDN link, container, row, and columns'],
                ['0:45 – 1:40', 'Put the three projects in a Bootstrap row'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre("<link href=\"{$cdn}\" rel=\"stylesheet\">\n<link rel=\"stylesheet\" href=\"styles.css\">")
            .$pre("<div class=\"container\">\n  <div class=\"row g-3\">\n    <div class=\"col-md-4\">First project</div>\n    <div class=\"col-md-4\">Second project</div>\n    <div class=\"col-md-4\">Third project</div>\n  </div>\n</div>")
            .$table(['Class', 'Meaning'], [
                ['container', 'A centred column with a maximum width'],
                ['container-fluid', 'Full width of the window'],
                ['row', 'A grid row. Columns go inside it'],
                ['col-md-4', 'Four of twelve columns from the medium breakpoint up'],
                ['g-3', 'A gap between the columns'],
            ])
            .$box('key', 'Key idea', '<p>Twelve columns is the grid. <code>col-md-4</code> three times is 4+4+4. Below the medium width, each column becomes a full row unless you say otherwise.</p>')
            .$box('try', 'Try it', $steps([
                'Add the Bootstrap link in the head, then your styles.css after it.',
                'Wrap the page content in container.',
                'Put the three project cards in a row of col-md-4.',
                'Refresh. On a wide window they sit in three columns.',
            ]))
            .$box('warn', 'Watch out', '<p>A column must be inside a row, and a row inside a container. A col sitting directly in the body will not line up.</p>')
            .$check(['Bootstrap CSS is linked', 'Your CSS file comes after it', 'Three col-md-4 columns sit in one row', 'The page still has a header and a main'])
            .$sources(['Bootstrap: Grid' => 'https://getbootstrap.com/docs/5.3/layout/grid/']),
        'assignment' => [
            'title' => 'Project: A Bootstrap row',
            'description' => '<p>Submit the page with Bootstrap linked and three columns.</p>',
            'instructions' => 'Upload the HTML. It must link Bootstrap 5 from the CDN and show three col-md-4 columns inside a container and a row.',
            'prompt_question' => 'Why does your stylesheet link come after the Bootstrap link?',
        ],
    ],
    [
        'title' => 'Bootstrap Components: Buttons, Cards, and Forms',
        'summary' => 'Replace homemade cards and form spacing with Bootstrap components.',
        'question_of_day' => 'Which pieces of the page are so common that a library should already know them?',
        'objectives' => "Build a card with a title, text, and a button.\nUse button classes instead of restyling a link from scratch.\nLay out a form with Bootstrap’s form classes and labels.\nKeep labels attached to inputs.",
        'guidance' => "Convert the three project divs into card components and the contact form into a Bootstrap form. Remind them the label and id rules from HTML still apply. btn-primary is a class, not a new tag.",
        'content' => $hero('Lesson 16 · 2 hours', 'Buttons, cards, and forms', 'A component is HTML with the classes Bootstrap already styled.')
            .$plan([
                ['0:00 – 0:10', 'Point at a card and a button on a site you know'],
                ['0:10 – 0:45', 'card, btn, and form-control'],
                ['0:45 – 1:40', 'Rebuild the projects and the contact form'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre("<div class=\"card h-100\">\n  <div class=\"card-body\">\n    <h2 class=\"card-title h5\">Gallery</h2>\n    <p class=\"card-text\">Photos from week 1.</p>\n    <a class=\"btn btn-primary\" href=\"projects.html\">Open</a>\n  </div>\n</div>")
            .$pre("<div class=\"mb-3\">\n  <label class=\"form-label\" for=\"email\">Email</label>\n  <input class=\"form-control\" id=\"email\" name=\"email\" type=\"email\" required>\n</div>\n<button class=\"btn btn-primary\" type=\"submit\">Register</button>")
            .$table(['Class', 'Put it on'], [
                ['card / card-body / card-title / card-text', 'The project box and its parts'],
                ['btn btn-primary', 'The main action'],
                ['btn btn-outline-secondary', 'A quieter action'],
                ['form-label / form-control', 'The label and the input'],
                ['mb-3', 'Space under a form group'],
                ['h-100', 'A card in a row, so the cards match in height'],
            ])
            .$box('try', 'Try it', $steps([
                'Turn each project into a card with a title, a sentence, and a button.',
                'Add h-100 so the three cards in the row are equal height.',
                'Rebuild the contact form with form-label and form-control.',
                'Click a label. The input must still focus.',
            ]))
            .$box('warn', 'Watch out', '<p>Do not remove the <code>for</code> and <code>id</code> pair just because you added form-label. The class changes the look. The for attribute connects the label.</p>')
            .$check(['Three cards with buttons', 'The form uses form-control', 'Labels still focus their inputs', 'Primary buttons look the same because they share btn-primary'])
            .$sources(['Bootstrap: Cards' => 'https://getbootstrap.com/docs/5.3/components/card/']),
        'assignment' => [
            'title' => 'Project: Cards and a form',
            'description' => '<p>Submit the page after the projects are cards and the contact area is a Bootstrap form.</p>',
            'instructions' => 'Upload the HTML. Include at least two cards with btn classes and a form that uses form-label and form-control.',
            'prompt_question' => 'What does h-100 change when the cards sit in one row?',
        ],
    ],
    [
        'title' => 'Bootstrap Navigation and Navbar',
        'summary' => 'Replace the plain nav with a navbar that collapses on a phone.',
        'question_of_day' => 'Where do the links go when the screen is too narrow for a full menu?',
        'objectives' => "Build a navbar with a brand and links.\nAdd the toggler button and the collapse region.\nInclude the Bootstrap JavaScript bundle.\nCheck the menu opens and closes on a narrow window.",
        'guidance' => "The collapse does nothing without the JS bundle at the end of the body. data-bs-target must match the collapse id. Have them narrow the window and click the button before they submit.",
        'content' => $hero('Lesson 17 · 2 hours', 'A navbar that works on a phone', 'The links stay. On a phone they hide behind a button.')
            .$plan([
                ['0:00 – 0:10', 'Narrow a site and find the menu button'],
                ['0:10 – 0:50', 'navbar, toggler, collapse, and the script'],
                ['0:50 – 1:40', 'Replace your nav and test the button'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre("<nav class=\"navbar navbar-expand-md navbar-dark bg-primary\">\n  <div class=\"container\">\n    <a class=\"navbar-brand\" href=\"index.html\">Amina</a>\n    <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#mainNav\">\n      <span class=\"navbar-toggler-icon\"></span>\n    </button>\n    <div class=\"collapse navbar-collapse\" id=\"mainNav\">\n      <ul class=\"navbar-nav ms-auto\">\n        <li class=\"nav-item\"><a class=\"nav-link\" href=\"#projects\">Projects</a></li>\n        <li class=\"nav-item\"><a class=\"nav-link\" href=\"#contact\">Contact</a></li>\n      </ul>\n    </div>\n  </div>\n</nav>\n<script src=\"{$js}\"></script>")
            .$box('key', 'Key idea', '<p><code>navbar-expand-md</code> means: from the medium width up, show the links. Below that, show the button. The button’s <code>data-bs-target</code> must be the id of the collapse div.</p>')
            .$box('try', 'Try it', $steps([
                'Replace the old nav with the navbar. Keep the same hrefs.',
                'Put the script just before the closing body tag.',
                'Narrow the window until the links disappear.',
                'Click the button. The links must open. Click again. They must close.',
            ]))
            .$box('warn', 'Watch out', '<p>If the button does nothing, the script is missing or the target id does not match. CSS alone cannot open this menu.</p>')
            .$check(['A brand and at least two links', 'The toggler shows on a narrow window', 'The menu opens and closes', 'The script tag is on the page'])
            .$sources(['Bootstrap: Navbar' => 'https://getbootstrap.com/docs/5.3/components/navbar/']),
        'assignment' => [
            'title' => 'Project: Collapsing navbar',
            'description' => '<p>Submit the page with a working Bootstrap navbar.</p>',
            'instructions' => 'Upload the HTML. The navbar must collapse below the md breakpoint and the Bootstrap bundle script must be included. Say which id the button targets.',
            'prompt_question' => 'What did you have to add besides CSS for the button to open the menu?',
        ],
    ],
    [
        'title' => 'Bootstrap Utilities and Helpers',
        'summary' => 'Space, align, and colour with utility classes instead of a new CSS rule for every gap.',
        'question_of_day' => 'When should you use a utility class, and when should you write your own CSS?',
        'objectives' => "Add margin and padding with m- and p- utilities.\nAlign text and flex items with text-center and justify-content.\nColour text and backgrounds with Bootstrap colour utilities.\nLeave a custom class for a look Bootstrap does not have.",
        'guidance' => "Spend the hour cleaning spacing: mb-4 under the header, py-5 on a section, text-center only on the hero. Show the scale: 0, 1, 2, 3, 4, 5. Warn them not to stack five utilities when one rule in styles.css would be clearer.",
        'content' => $hero('Lesson 18 · 2 hours', 'Utility classes', 'Utilities are short classes for one job: space, alignment, or colour.')
            .$plan([
                ['0:00 – 0:15', 'Find uneven gaps on your page'],
                ['0:15 – 0:45', 'Spacing, text, and colour utilities'],
                ['0:45 – 1:40', 'Clean the portfolio spacing without new custom rules'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$table(['Class', 'Effect'], [
                ['mt-4', 'Margin on top, step 4'],
                ['mb-3', 'Margin on the bottom, step 3'],
                ['py-5', 'Padding on the top and bottom, step 5'],
                ['px-3', 'Padding on the left and right'],
                ['text-center', 'Centre the text in this box'],
                ['text-muted', 'Quieter grey text'],
                ['bg-light', 'A light grey background'],
                ['d-flex justify-content-between', 'A row with the items at the two ends'],
            ])
            .$pre("<header class=\"bg-primary text-white text-center py-5\">\n  <h1 class=\"mb-2\">Amina Nakato</h1>\n  <p class=\"mb-0\">Web Development 1 · Kampala</p>\n</header>\n<main class=\"container py-4\">\n  ...\n</main>")
            .$box('key', 'Key idea', '<p>The number is a step, not a pixel you invent. <code>mb-3</code> is the same step everywhere, so the page looks consistent. Use your own CSS when the look is special to your camp, such as a card shadow you designed.</p>')
            .$box('try', 'Try it', $steps([
                'Give the header py-5 and text-center.',
                'Give main a container and py-4.',
                'Put mb-4 on each section so they do not collide.',
                'Use text-muted for a secondary sentence. Do not grey out a whole paragraph of important text.',
            ]))
            .$box('warn', 'Watch out', '<p><code>m-3</code> adds margin on all four sides and can fight a grid gap. Prefer <code>mb-3</code> or <code>mt-3</code> when you only need one side.</p>')
            .$check(['The header has vertical padding', 'Sections are separated with margin utilities', 'Centring is only where you chose it', 'Body text is still dark enough to read'])
            .$sources(['Bootstrap: Spacing' => 'https://getbootstrap.com/docs/5.3/utilities/spacing/']),
        'assignment' => [
            'title' => 'Project: Spacing with utilities',
            'description' => '<p>Submit the page after the spacing uses Bootstrap utilities.</p>',
            'instructions' => 'Upload the HTML. Show py- or px- on a region, mb- or mt- between sections, and one text or background utility. Do not remove the navbar or the cards.',
            'prompt_question' => 'Which utility did you use for the space under a section, and why not a custom margin in CSS?',
        ],
    ],
    [
        'title' => 'Bootstrap Modals, Alerts, and More Components',
        'summary' => 'Show a message with an alert, and open a dialog with a modal.',
        'question_of_day' => 'When should a message sit on the page, and when should it wait behind a button?',
        'objectives' => "Add an alert for a permanent note.\nOpen a modal from a button using the Bootstrap attributes.\nPut a short confirm or a small form inside the modal.\nClose the modal with its close button.",
        'guidance' => "One alert at the top of main: camp dates. One modal for “project details” or a confirm. The JS bundle from the navbar lesson must still be on the page. If they removed it, the modal will not open.",
        'content' => $hero('Lesson 19 · 2 hours', 'Alerts and a modal', 'An alert is always visible. A modal waits until the visitor asks.')
            .$plan([
                ['0:00 – 0:10', 'Decide which message should always show'],
                ['0:10 – 0:45', 'alert and modal markup'],
                ['0:45 – 1:40', 'Add one alert and one modal to the portfolio'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre("<div class=\"alert alert-info\" role=\"alert\">\n  Camp runs for three weeks in Kampala. Bring your notes.\n</div>")
            .$pre("<button class=\"btn btn-outline-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#details\">\n  Project details\n</button>\n<div class=\"modal fade\" id=\"details\" tabindex=\"-1\">\n  <div class=\"modal-dialog\">\n    <div class=\"modal-content\">\n      <div class=\"modal-header\">\n        <h2 class=\"modal-title h5\">Gallery</h2>\n        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button>\n      </div>\n      <div class=\"modal-body\">\n        <p>Photos from the first HTML lessons.</p>\n      </div>\n    </div>\n  </div>\n</div>")
            .$box('key', 'Key idea', '<p>The button’s <code>data-bs-target</code> is the modal’s id, the same pattern as the navbar. <code>data-bs-dismiss=\"modal\"</code> on the close button tells Bootstrap to hide it.</p>')
            .$box('try', 'Try it', $steps([
                'Add an info alert under the navbar.',
                'Add a button on one card that targets a modal id.',
                'Write two sentences in the modal body.',
                'Confirm the script bundle is still before the closing body tag, then open and close the modal.',
            ]))
            .$box('warn', 'Watch out', '<p>Do not put the important instructions only inside a modal. A visitor who never clicks the button will not see them. The alert is for what everyone must read.</p>')
            .$check(['One alert is visible without a click', 'A button opens a modal', 'The modal closes', 'The Bootstrap script is still on the page'])
            .$sources(['Bootstrap: Modal' => 'https://getbootstrap.com/docs/5.3/components/modal/']),
        'assignment' => [
            'title' => 'Project: Alert and modal',
            'description' => '<p>Submit the page with one alert and one working modal.</p>',
            'instructions' => 'Upload the HTML. Include an alert and a modal that opens from a button and closes again. The Bootstrap JavaScript bundle must be on the page.',
            'prompt_question' => 'Which message did you leave in the alert, and which did you put in the modal? Why?',
        ],
    ],
    [
        'title' => 'Customizing Bootstrap: Themes and Overrides',
        'summary' => 'Change Bootstrap’s colours without editing the library file.',
        'question_of_day' => 'What happens to your edits if you paste them inside the Bootstrap file and the library updates?',
        'objectives' => "Override a Bootstrap look from your own stylesheet loaded afterwards.\nChange a colour with a CSS variable where Bootstrap allows it.\nRestyle one component, such as the primary button, on purpose.\nLeave the CDN file untouched.",
        'guidance' => "Show the cascade: same specificity, later stylesheet wins. They already link styles.css after Bootstrap. Set --bs-primary or restyle .btn-primary. Do not download and edit bootstrap.min.css.",
        'content' => $hero('Lesson 20 · 2 hours', 'Make Bootstrap yours', 'Your stylesheet comes last, so your rules can win.')
            .$plan([
                ['0:00 – 0:15', 'Find one Bootstrap colour you want to change'],
                ['0:15 – 0:45', 'Cascade, CSS variables, and a safe override'],
                ['0:45 – 1:40', 'Recolour the navbar and the primary button'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre(":root {\n  --bs-primary: #c2410c;\n}\n\n.btn-primary {\n  background-color: #c2410c;\n  border-color: #c2410c;\n}\n\n.navbar.bg-primary {\n  background-color: #1e3a8a !important;\n}")
            .$box('key', 'Key idea', '<p>Never edit the file from the CDN. The next time the link updates, your edits would vanish. Put camp colours in <code>styles.css</code>, which you control.</p>')
            .$p('Bootstrap 5 stores many colours as CSS variables that start with <code>--bs-</code>. Setting <code>--bs-primary</code> on <code>:root</code> changes places that use that variable. A class like <code>bg-primary</code> is sometimes a plain background, so a direct override is the reliable fix. Use <code>!important</code> only when the utility would otherwise win, and only on that one rule.')
            .$box('try', 'Try it', $steps([
                'In styles.css, set a camp colour on .btn-primary.',
                'Recolour the navbar so it is not the default blue, unless you prefer to keep it.',
                'Refresh and check the button, the navbar, and the alert still look intentional.',
                'Confirm you did not change any file except styles.css and your HTML.',
            ]))
            .$box('warn', 'Watch out', '<p>A very light primary colour makes white button text disappear. Check the words on the button after you change the background.</p>')
            .$check(['styles.css still loads after Bootstrap', 'The primary button is no longer the default blue, or you chose to keep it and can say why', 'The CDN link is unchanged', 'Text on the coloured button is readable'])
            .$sources(['Bootstrap: CSS variables' => 'https://getbootstrap.com/docs/5.3/customize/css-variables/']),
        'assignment' => [
            'title' => 'Project: A small theme',
            'description' => '<p>Submit the page with one deliberate override in your stylesheet.</p>',
            'instructions' => 'Upload HTML and styles.css. Change at least the primary button or the navbar from your own CSS. Do not edit Bootstrap’s file. Say which rule you overrode.',
            'prompt_question' => 'Why is the override in your file instead of inside bootstrap.min.css?',
        ],
    ],
    [
        'title' => 'Week 3 Final Project: Complete Responsive Website with Bootstrap',
        'summary' => 'Build a three-page camp site: home, projects, and contact. Bootstrap runs the layout. Your CSS adds the camp colour.',
        'question_of_day' => 'Can someone who was not in the room use the site on a phone?',
        'objectives' => "Publish index.html, projects.html, and contact.html with the same navbar.\nUse the grid, cards, a form, and utilities you already practised.\nKeep one stylesheet and working relative links.\nTest wide and narrow, then zip everything.",
        'guidance' => "This is the build. Checklist on the board. They may reuse the portfolio. The new requirement is three pages that share the navbar. Final quiz draws from the whole bank. Leave time to zip and test one link between pages.",
        'final' => true,
        'content' => $hero('Lesson 21 · 2 hours', 'Three-page camp site', 'Home, projects, and contact. Same navbar on every page.')
            .$plan([
                ['0:00 – 0:10', 'Read the checklist and split the old page into three files'],
                ['0:10 – 1:25', 'Build the three pages'],
                ['1:25 – 1:45', 'Review quiz from the whole course'],
                ['1:45 – 2:00', 'Test links and zip'],
            ])
            .$check([
                'index.html introduces you and links to the other two pages',
                'projects.html shows at least three cards in a Bootstrap row',
                'contact.html has a labelled form: name, email, message, submit',
                'Every page has the same collapsing navbar',
                'Bootstrap CSS and the script bundle are on every page',
                'styles.css comes after Bootstrap and holds your colour override',
                'A narrow window does not hide the menu forever: the button opens it',
                'Images use relative paths and travel inside the zip',
            ])
            .$box('key', 'Key idea', '<p>Copy the navbar carefully onto each page, then fix the links. A brand link should go to <code>index.html</code> from every page, not to <code>#</code>.</p>')
            .$box('warn', 'Watch out', '<p>From contact.html, <code>href="images/camp.jpg"</code> still works if images is beside contact.html. <code>href="index.html"</code> must not say <code>../index.html</code> unless the file really is in a subfolder.</p>')
            .$sources(['Bootstrap docs' => 'https://getbootstrap.com/docs/5.3/getting-started/introduction/']),
        'assignment' => [
            'title' => 'Final project: Three-page camp site',
            'description' => '<p>Submit the three-page site as a zip.</p>',
            'instructions' => 'Zip index.html, projects.html, contact.html, styles.css, and images. Every page needs the navbar. Projects need cards. Contact needs a form. Include a narrow screenshot of the open menu if you can.',
            'prompt_question' => 'Which page was hardest to keep consistent, and what did you copy onto all three?',
        ],
    ],
];
