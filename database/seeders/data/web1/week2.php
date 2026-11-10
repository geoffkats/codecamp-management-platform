<?php

$b = require __DIR__.'/blocks.php';
extract($b);

return [
    [
        'title' => 'Day 6: CSS, Selectors, and Colour',
        'summary' => 'Move the look of the page into a stylesheet and target elements, classes, and ids.',
        'question_of_day' => 'The HTML from last week looks plain. Where should the colours live?',
        'objectives' => "Link a CSS file from an HTML page.\nStyle elements with element, class, and id selectors.\nSet colour, background-colour, and text-align.\nExplain why class is usually better than id.",
        'guidance' => "Students must open last week's HTML. If someone has no file, give them a starter profile. Show a page with style attributes first and say we will not do that. Then one shared styles.css. Spend time on the link tag in the head: wrong path is the number one bug. Class names: lowercase, hyphens, describe the job (profile-name) not the colour (red-text).",
        'content' => $hero(6, 'CSS, selectors, and colour', 'HTML is the content. CSS is the look. They live in different files.')
            .$plan([
                ['0:00 – 0:15', 'Open last week\'s page and list what you want to change'],
                ['0:15 – 0:50', 'The link tag, selectors, and colour'],
                ['0:50 – 1:40', 'Colour your profile page'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit HTML and CSS'],
            ])
            .$h2('Connect the stylesheet')
            .$pre("<head>\n  <link rel=\"stylesheet\" href=\"styles.css\">\n</head>")
            .$pre("body {\n  font-family: Arial, sans-serif;\n  background: #f8fafc;\n  color: #0f172a;\n}\n\nh1 {\n  color: #1e3a8a;\n}\n\n.tagline {\n  color: #c2410c;\n}\n\n#site-footer {\n  background: #1e3a8a;\n  color: #ffffff;\n}")
            .$table(['Selector', 'Matches', 'How often to use it'], [
                ['h1', 'Every h1', 'For things that should all look the same'],
                ['.tagline', 'Any element with class="tagline"', 'The usual choice'],
                ['#site-footer', 'The one element with that id', 'Rare. An id must be unique on the page'],
            ])
            .$box('key', 'Key idea', '<p>Add <code>class="tagline"</code> in the HTML, then style <code>.tagline</code> in the CSS. Do not put <code>style="color: red"</code> on the tag. A class can be reused. A colour buried in the HTML cannot.</p>')
            .$box('try', 'Try it', $steps([
                'Put <code>styles.css</code> in the same folder as your profile page.',
                'Link it from the head.',
                'Give the body a light background and a dark text colour.',
                'Colour the h1 with the element selector.',
                'Add a class to the line under your name and colour that with a class selector.',
                'If nothing changes, check the file name and press F5.',
            ]))
            .$box('warn', 'Watch out', '<p>Hex colours need the <code>#</code>. <code>1e3a8a</code> does nothing. <code>#1e3a8a</code> is navy.</p>')
            .$check(['styles.css is linked from the head', 'The background colour changed', 'A class selector is used at least once', 'No style attributes left in the HTML'])
            .$sources(['MDN: CSS first steps' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/First_steps']),
        'assignment' => [
            'title' => 'Day 6 project: Add colour with a stylesheet',
            'description' => '<p>Your Week 1 page, now linked to a CSS file that sets colours.</p>',
            'instructions' => "Upload a zip with the HTML file and styles.css. The HTML must link the CSS. Required: a body background, a styled h1, and at least one class selector. Do not use style attributes.",
            'prompt_question' => 'What is the difference between a class selector and an element selector?',
        ],
    ],
    [
        'title' => 'Day 7: Type and the Box Model',
        'summary' => 'Control font size and the space inside and outside every box.',
        'question_of_day' => 'Why does a heading sometimes touch the edge of the page, and how do you give it room?',
        'objectives' => "Set font-family, font-size, and line-height.\nExplain content, padding, border, and margin.\nUse border-box so width includes padding.\nBuild a student-ID card with even spacing.",
        'guidance' => "Draw the box model on the board before anyone codes: content, padding, border, margin. Use the browser inspector on one card and point at the coloured regions. The project is a single card centred on the page, not a whole new website. box-sizing: border-box goes on the card today; next lessons will put it on everything.",
        'content' => $hero(7, 'Type and the box model', 'Every element is a box. You decide the space inside it and the space around it.')
            .$plan([
                ['0:00 – 0:15', 'Inspect a heading and find the empty space around it'],
                ['0:15 – 0:50', 'Fonts, then margin, padding, and border'],
                ['0:50 – 1:40', 'Build a student ID card'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the card'],
            ])
            .$h2('Type')
            .$pre("body {\n  font-family: \"Segoe UI\", Arial, sans-serif;\n  font-size: 18px;\n  line-height: 1.5;\n}\n\nh1 {\n  font-size: 36px;\n  line-height: 1.2;\n}")
            .$box('tip', 'Tip', '<p><code>line-height: 1.5</code> means the line is one and a half times the font size. Body text becomes easier to read. Headings can stay tighter.</p>')
            .$h2('The four layers')
            .$table(['Layer', 'What it is'], [
                ['Content', 'The words or the picture'],
                ['Padding', 'Space inside the border, around the content'],
                ['Border', 'The line around the padding'],
                ['Margin', 'Space outside the border, pushing other boxes away'],
            ])
            .$pre(".card {\n  box-sizing: border-box;\n  width: 320px;\n  margin: 40px auto;\n  padding: 24px;\n  border: 4px solid #1e3a8a;\n  border-radius: 16px;\n  background: #ffffff;\n}\n\n.card h1 {\n  margin-top: 0;\n}")
            .$box('key', 'Key idea', '<p><code>margin: 40px auto</code> gives 40px above and below, and <code>auto</code> on the left and right centres a box that has a width. Padding never uses auto.</p>')
            .$box('try', 'Try it', '<p>Make a card that looks like a student ID: your name, “Web Development 1”, the camp city, and a short line. Give it a width, padding, a border, and centre it. The heading inside the card should not crash into the border.</p>')
            .$check(['The card is not stuck to the left edge', 'Padding keeps the words off the border', 'You can point to margin versus padding', 'border-box is set on the card'])
            .$sources(['MDN: The box model' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/The_box_model']),
        'assignment' => [
            'title' => 'Day 7 project: Student ID card',
            'description' => '<p>A centred card styled with the box model.</p>',
            'instructions' => "Upload a zip of the HTML and CSS. The card needs a set width, padding, a visible border, and margin that centres it. Include your name and the course name.",
            'prompt_question' => 'Where is the padding on your card, and where is the margin?',
        ],
    ],
    [
        'title' => 'Day 8: Flexbox Layouts',
        'summary' => 'Line up cards in a row and control the gaps without guessing margins.',
        'question_of_day' => 'How do you put three cards in a row that stays even when the words are different lengths?',
        'objectives' => "Turn a container into a flex row.\nUse justify-content, align-items, and gap.\nLet items wrap on a narrow screen.\nBuild a row of three equal project cards.",
        'guidance' => "Do not teach float. Flexbox only. Live-code one row, then change justify-content and let the class shout what happened. The project is three cards (Day 1, Day 3, Day 5 projects) in one row with a gap. If time is short, two cards is not enough: push for three.",
        'content' => $hero(8, 'Flexbox layouts', 'Flexbox lays out a row or a column and shares the space for you.')
            .$plan([
                ['0:00 – 0:15', 'Look at a navbar and a row of cards. Which way do they run?'],
                ['0:15 – 0:50', 'display: flex, justify-content, gap, wrap'],
                ['0:50 – 1:40', 'Build a row of three project cards'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the row'],
            ])
            .$h2('A flex container')
            .$pre(".card-row {\n  display: flex;\n  justify-content: space-between;\n  align-items: stretch;\n  gap: 16px;\n  flex-wrap: wrap;\n}\n\n.card {\n  flex: 1 1 200px;\n  padding: 16px;\n  border: 1px solid #cbd5e1;\n  border-radius: 12px;\n}")
            .$table(['Property', 'What you say to the browser'], [
                ['display: flex', 'Lay these children out in a row'],
                ['justify-content', 'How to share extra space along the row'],
                ['align-items', 'How to line the cards up across the row'],
                ['gap', 'The space between cards. Prefer this to margins'],
                ['flex-wrap: wrap', 'Drop to the next line when the row is full'],
                ['flex: 1 1 200px', 'Grow, shrink, and try to stay at least 200px wide'],
            ])
            .$box('key', 'Key idea', '<p>Flexbox is set on the <strong>parent</strong>. The children are the items. If the cards do not sit in a row, <code>display: flex</code> is missing on the parent, not on each card.</p>')
            .$box('try', 'Try it', '<p>Make three cards: “HTML page”, “Gallery”, and “Profile”. Put them in a div with class <code>card-row</code>. They should sit side by side with a gap, and wrap if the window is narrow. Drag the window narrower and watch them wrap.</p>')
            .$check(['Three cards in one row on a wide window', 'A visible gap that is not a pile of margins', 'The row wraps when the window is narrow', 'display: flex is on the parent'])
            .$sources(['MDN: Flexbox' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Flexbox']),
        'assignment' => [
            'title' => 'Day 8 project: Three project cards',
            'description' => '<p>A flex row of three cards describing work from this camp.</p>',
            'instructions' => "Upload HTML and CSS. A parent must use display: flex and gap. There must be three cards. They must wrap when the browser window is narrow.",
            'prompt_question' => 'Which element did you put display: flex on, and why that one?',
        ],
    ],
    [
        'title' => 'Day 9: Pages that Fit a Phone',
        'summary' => 'Add the viewport tag and a media query so the layout changes on a small screen.',
        'question_of_day' => 'Why does a desktop page become a tiny unreadable strip on a phone?',
        'objectives' => "Add the viewport meta tag and explain what it does.\nWrite a mobile-first media query.\nStack a flex row on a narrow screen.\nTest by resizing the browser, not by guessing.",
        'guidance' => "Phones in the room are the test. If a student has no phone, the browser device toolbar is enough. Teach mobile-first: style the narrow page first, then min-width for wider screens. One breakpoint at 700px is enough. Do not introduce a framework today.",
        'content' => $hero(9, 'Pages that fit a phone', 'The same HTML can look different when the screen changes. That is responsive design.')
            .$plan([
                ['0:00 – 0:15', 'Open yesterday\'s row on a phone or a narrow window'],
                ['0:15 – 0:45', 'Viewport and one media query'],
                ['0:45 – 1:35', 'Make the profile readable on a phone'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Submit and show it narrow'],
            ])
            .$h2('Tell the phone the width is real')
            .$pre("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">")
            .$box('key', 'Key idea', '<p>Without that tag, many phones pretend the page is about 980 pixels wide and zoom out. Your text becomes tiny. The viewport tag says: use the real width of the device.</p>')
            .$h2('One breakpoint')
            .$pre(".card-row {\n  display: flex;\n  flex-direction: column;\n  gap: 16px;\n}\n\n@media (min-width: 700px) {\n  .card-row {\n    flex-direction: row;\n  }\n}")
            .$p('This is <strong>mobile first</strong>. The normal CSS is the phone. The media query adds the row only when there is at least 700 pixels.')
            .$box('try', 'Try it', $steps([
                'Put the viewport tag in the head of your profile page.',
                'On a narrow screen, stack the header text and the cards.',
                'At 700px and wider, put the cards in a row.',
                'Make the body padding smaller on a phone so words are not squeezed.',
                'Resize slowly. The change should happen at the breakpoint, not at a random width.',
            ]))
            .$box('warn', 'Watch out', '<p>A fixed width such as <code>width: 1000px</code> on the whole page will force sideways scrolling on a phone. Prefer <code>max-width</code> and <code>width: 100%</code>.</p>')
            .$check(['The viewport meta tag is in the head', 'A media query uses min-width', 'Nothing important is cut off at 375 pixels wide', 'You tested by resizing, not only on your laptop size'])
            .$sources(['MDN: Responsive design' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design']),
        'assignment' => [
            'title' => 'Day 9 project: A page that fits a phone',
            'description' => '<p>Your profile or card row, with a viewport tag and one breakpoint.</p>',
            'instructions' => "Upload HTML and CSS. The head must include the viewport meta tag. The CSS must include one min-width media query that changes the layout. Also upload a screenshot of the narrow page and a screenshot of the wide page.",
            'prompt_question' => 'What broke on a narrow screen, and what did you change?',
        ],
    ],
    [
        'title' => 'Day 10: Week 2 Project — Style the Profile',
        'summary' => 'Spend the session finishing a styled, responsive profile page and hand it in.',
        'question_of_day' => 'If someone opens your page on a phone in a taxi, can they still read your name?',
        'objectives' => "Combine selectors, the box model, flexbox, and a media query on one page.\nKeep HTML free of style attributes.\nCheck contrast: dark text on a light background, or the reverse.\nSubmit a zip another teacher can open.",
        'guidance' => "This is a build day. Put the checklist on the board and circulate. Do a 10-minute gallery at 1:30 where two students show a phone-width page. Then quiz (it reviews the week) and collect zips. Reject pages that are one unstyled HTML file. Help with paths, not with inventing a new design for them.",
        'content' => $hero(10, 'Style the profile', 'Today you finish the page. The notes are the checklist, not a new topic.')
            .$plan([
                ['0:00 – 0:15', 'Open your Week 1 HTML and say what is still unstyled'],
                ['0:15 – 0:30', 'Review the checklist together'],
                ['0:30 – 1:30', 'Build, test narrow and wide, fix paths'],
                ['1:30 – 1:45', 'Two students show their phone layout'],
                ['1:45 – 2:00', 'Quiz and submit the zip'],
            ])
            .$h2('What finished looks like')
            .$table(['Piece', 'Done when'], [
                ['HTML', 'header, nav, main, sections, footer. No style attributes'],
                ['CSS file', 'Linked from the head. Body font and background set'],
                ['Type', 'The h1 is clearly bigger than the paragraphs'],
                ['Cards or sections', 'Padding and a gap. Nothing touches the edge'],
                ['Layout', 'A flex row on a wide screen'],
                ['Phone', 'Viewport tag, and the row stacks under 700px'],
                ['Images', 'Still have alt text and still load'],
            ])
            .$box('key', 'Key idea', '<p>A finished page is one someone else can open. Zip the HTML, the CSS, and the images folder. Keep the same folder structure you used on your computer.</p>')
            .$pre("web1-profile/\n  index.html\n  styles.css\n  images/\n    me.jpg")
            .$box('try', 'Build', '<p>Use your real name, a short bio, a list, and at least two sections. Colours should look like a page you would show a parent, not a test of every colour in CSS. Navy, white, and one accent colour is enough.</p>')
            .$box('warn', 'Watch out', '<p>If the teacher opens your HTML and the page is white with black default text, the CSS path is wrong. The link must match the file name, including capital letters.</p>')
            .$check(['Someone else can open the HTML and see your design', 'It is readable at phone width', 'Headings, a list, and one image are still there', 'The zip contains the CSS and the images, not only the HTML'])
            .$sources(['MDN: Styling the page' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS']),
        'assignment' => [
            'title' => 'Week 2 project: Styled profile page',
            'description' => '<p>The personal profile, now styled and readable on a phone.</p>',
            'instructions' => "Upload one zip with index.html, styles.css, and any images. The page must use semantic HTML, a linked stylesheet, flexbox, the viewport tag, and one media query. Include screenshots of the wide and narrow layouts.",
            'prompt_question' => 'What is one thing you would still improve if you had another hour?',
        ],
    ],
];
