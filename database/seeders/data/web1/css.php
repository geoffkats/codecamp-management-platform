<?php

$b = require __DIR__.'/blocks.php';
extract($b);

$mdn = ['MDN: CSS first steps' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/First_steps'];

return [
    [
        'title' => 'Introduction to CSS',
        'summary' => 'Move the look out of the HTML and into a stylesheet the page can share.',
        'question_of_day' => 'Why is the colour of a heading not written inside the h1 tag?',
        'objectives' => "Link a CSS file from the head of the page.\nWrite an element selector, a class selector, and an id selector.\nChange colour, background, and font size from the stylesheet.\nExplain why a class is usually better than an id for styling.",
        'guidance' => "Create styles.css beside index.html and link it. If nothing changes, the href is wrong or the file was not saved. Show the three selector shapes on the board before they style the portfolio.",
        'content' => $hero('Lesson 8 · 2 hours', 'A stylesheet and selectors', 'HTML says what it is. CSS says how it looks.')
            .$plan([
                ['0:00 – 0:15', 'Compare an unstyled page with a styled one'],
                ['0:15 – 0:50', 'The link tag and three kinds of selector'],
                ['0:50 – 1:40', 'Style the portfolio from styles.css'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit HTML and CSS'],
            ])
            .$pre("<link rel=\"stylesheet\" href=\"styles.css\">")
            .$p('That line goes in the <code>head</code>, not the body.')
            .$pre("h1 {\n  color: #1e3a8a;\n}\n\n.project-card {\n  background: #f8fafc;\n}\n\n#contact {\n  background: #eff6ff;\n}")
            .$table(['Selector', 'Matches', 'When to use it'], [
                ['h1', 'Every h1', 'A rule for that kind of element'],
                ['.project-card', 'Every element with that class', 'A shared look'],
                ['#contact', 'The one element with that id', 'One special region'],
            ])
            .$box('key', 'Key idea', '<p>If you delete styles.css, the words must still make sense. CSS is not allowed to be the only thing that says “this is a heading”.</p>')
            .$box('try', 'Try it', $steps([
                'Create styles.css next to index.html.',
                'Link it from the head.',
                'Colour the h1, give .project-card a background, and give #contact a different background.',
                'Save both files and refresh. If nothing changes, check the href.',
            ]))
            .$box('warn', 'Watch out', '<p>A missing dot turns a class selector into an element selector. <code>project-card</code> looks for a tag named project-card. <code>.project-card</code> looks for the class.</p>')
            .$check(['styles.css is linked from the head', 'The h1 colour comes from CSS', 'The cards share one class rule', 'The page is still readable if you unlink the CSS'])
            .$sources($mdn),
        'assignment' => [
            'title' => 'Project: Linked stylesheet',
            'description' => '<p>Submit the portfolio with a real stylesheet, not inline styles.</p>',
            'instructions' => 'Upload index.html and styles.css, or a zip of both. The CSS file must style an element, a class, and an id.',
            'prompt_question' => 'Where did you put the link tag, and what happens if that href is wrong?',
        ],
    ],
    [
        'title' => 'Colors, Typography, and Text Styling',
        'summary' => 'Choose readable colours and type: size, weight, line height, and alignment.',
        'question_of_day' => 'What makes a paragraph hard to read even when the words are simple?',
        'objectives' => "Write colours as hex or as a named colour, and know which is more precise.\nSet a font stack with a fallback.\nControl size, weight, and line-height.\nKeep body text near black on a light background.",
        'guidance' => "Put a pale grey paragraph on a white card and ask who can read it from the back row. Then fix contrast. Ban comic-style display fonts for body text.",
        'content' => $hero('Lesson 9 · 2 hours', 'Colour and type', 'If the text is hard to read, the design has failed.')
            .$plan([
                ['0:00 – 0:15', 'Find text on a slide that you cannot read from your seat'],
                ['0:15 – 0:50', 'Colour, font stacks, size, weight, line-height'],
                ['0:50 – 1:40', 'Retype the portfolio so the body text is comfortable'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit the updated CSS'],
            ])
            .$pre("body {\n  color: #1f2937;\n  background: #ffffff;\n  font-family: \"Segoe UI\", Arial, sans-serif;\n  font-size: 18px;\n  line-height: 1.5;\n}\n\nh1 {\n  font-size: 40px;\n  font-weight: 800;\n  color: #1e3a8a;\n}\n\n.highlight {\n  color: #c2410c;\n  font-weight: 700;\n}")
            .$table(['Property', 'What to remember'], [
                ['color', 'The text. #1f2937 is a soft black'],
                ['background', 'The surface behind the text'],
                ['font-family', 'List a first choice, then a fallback that every computer has'],
                ['font-size', 'Body around 18px in class. Headings larger'],
                ['line-height', '1.5 gives the lines air. 1 is cramped'],
                ['text-align', 'Left for paragraphs. Centre only for a short title'],
            ])
            .$box('key', 'Key idea', '<p>A hex colour is six digits after <code>#</code>: red, green, blue. <code>#ffffff</code> is white. <code>#000000</code> is black. You do not need to memorise every code. You do need enough contrast to read.</p>')
            .$box('try', 'Try it', $steps([
                'Set the body font, size, and line-height.',
                'Make the h1 a deep blue and heavier than the paragraphs.',
                'Centre only the header, not the paragraphs.',
                'Read a paragraph aloud. If you lose your place, increase line-height.',
            ]))
            .$box('warn', 'Watch out', '<p>Light grey text on a white card looks “designed” and fails in a bright classroom. Body text stays dark.</p>')
            .$check(['A font stack with a fallback', 'Body text is dark on a light background', 'Headings are clearly larger than paragraphs', 'Paragraphs are not centred'])
            .$sources(['MDN: Fundamental text and font styling' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/Styling_text/Fundamentals']),
        'assignment' => [
            'title' => 'Project: Readable type',
            'description' => '<p>Submit the portfolio after the type and colours are set in CSS.</p>',
            'instructions' => 'Upload the HTML and CSS. Body text must set font-family, font-size, line-height, and a dark colour. The h1 must be visibly larger.',
            'prompt_question' => 'Which colour did you use for body text, and why is it not light grey?',
        ],
    ],
    [
        'title' => 'CSS Box Model: Margin, Padding, and Borders',
        'summary' => 'See every element as a box: content, padding, border, then margin.',
        'question_of_day' => 'Why is there empty space inside a card and a different empty space between cards?',
        'objectives' => "Point to content, padding, border, and margin on a box.\nAdd padding inside a card and margin between cards.\nUse border-box so width includes the padding.\nTell margin from padding without guessing.",
        'guidance' => "Draw one box on the board and label the four layers. Then they style .project-card. The classic bug is width: 100% plus padding, which overflows. box-sizing: border-box fixes it. Set it on the whole page.",
        'content' => $hero('Lesson 10 · 2 hours', 'The box model', 'Padding is inside the border. Margin is outside it.')
            .$plan([
                ['0:00 – 0:20', 'Label the four layers on a drawn card'],
                ['0:20 – 0:50', 'padding, border, margin, and border-box'],
                ['0:50 – 1:40', 'Turn the project cards into real cards'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre("* {\n  box-sizing: border-box;\n}\n\n.project-card {\n  padding: 16px;\n  margin-bottom: 16px;\n  border: 1px solid #e5e7eb;\n  border-radius: 12px;\n  background: #ffffff;\n}")
            .$table(['Layer', 'Where it sits', 'A card uses it to'], [
                ['Content', 'The words and the picture', 'Show the project'],
                ['Padding', 'Inside the border, around the content', 'Stop the text touching the edge'],
                ['Border', 'The line around the padding', 'Show where the card ends'],
                ['Margin', 'Outside the border', 'Separate this card from the next'],
            ])
            .$box('key', 'Key idea', '<p>With <code>box-sizing: border-box</code>, the width you set includes padding and border. Without it, padding is added on top and the card becomes wider than you asked.</p>')
            .$box('try', 'Try it', $steps([
                'Add the border-box rule at the top of styles.css.',
                'Give .project-card padding, a light border, and a bottom margin.',
                'Temporarily set a background on the card. See the padding as coloured space inside.',
                'Remove a huge margin if the cards fly far apart.',
            ]))
            .$box('warn', 'Watch out', '<p>Margin between two stacked cards can collapse into one gap. If you need a reliable gap, you will use flex or grid in the next lessons. For today, margin-bottom on the card is enough.</p>')
            .$check(['Cards have padding so text does not touch the border', 'There is space between cards', 'border-box is set', 'You can say which space is padding and which is margin'])
            .$sources(['MDN: The box model' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/The_box_model']),
        'assignment' => [
            'title' => 'Project: Cards with padding and margin',
            'description' => '<p>Submit the portfolio after the cards use the box model.</p>',
            'instructions' => 'Upload HTML and CSS. .project-card must set padding, border, and margin. The stylesheet must set box-sizing: border-box.',
            'prompt_question' => 'In your card, which property creates the space inside the border?',
        ],
    ],
    [
        'title' => 'CSS Flexbox Layout',
        'summary' => 'Line the project cards up in a row that can wrap, with even gaps.',
        'question_of_day' => 'How do you put three cards side by side without guessing pixel positions?',
        'objectives' => "Turn a container into a flex row.\nUse gap instead of margins between the children.\nAlign items on the main axis and the cross axis.\nLet the cards wrap on a narrow window.",
        'guidance' => "One container around the three cards, class projects. Do not set flex on each card. Resize the window and show wrap. gap is the property they will forget and replace with margins.",
        'content' => $hero('Lesson 11 · 2 hours', 'Flexbox', 'The parent decides the row. The children sit inside it.')
            .$plan([
                ['0:00 – 0:15', 'Resize a page and watch a row of cards wrap'],
                ['0:15 – 0:50', 'display, gap, justify-content, align-items, flex-wrap'],
                ['0:50 – 1:40', 'Put the three project cards in a wrapping row'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre(".projects {\n  display: flex;\n  flex-wrap: wrap;\n  gap: 16px;\n}\n\n.project-card {\n  flex: 1 1 240px;\n}")
            .$table(['Property', 'On', 'Job'], [
                ['display: flex', 'The parent', 'Lay the children out in a row'],
                ['gap', 'The parent', 'Space between children, both directions'],
                ['justify-content', 'The parent', 'Where extra space goes along the row'],
                ['align-items', 'The parent', 'How children line up across the row'],
                ['flex-wrap', 'The parent', 'Allow a new line when the row is full'],
                ['flex: 1 1 240px', 'Each card', 'Grow, shrink, and try to be at least 240px'],
            ])
            .$box('key', 'Key idea', '<p>You set <code>display: flex</code> on the box that holds the cards, not on each card. The cards are the flex items.</p>')
            .$box('try', 'Try it', $steps([
                'Wrap the three cards in a div or section with class="projects".',
                'Make .projects a wrapping flex container with a 16px gap.',
                'Give each card flex: 1 1 240px.',
                'Drag the browser narrower until the cards wrap.',
            ]))
            .$box('warn', 'Watch out', '<p>If you also leave a large margin-bottom on the card, the wrapped rows look uneven. Prefer gap on the parent.</p>')
            .$check(['The three cards sit in a row on a wide window', 'They wrap when the window is narrow', 'The gap is even', 'display: flex is on the parent'])
            .$sources(['MDN: Flexbox' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Flexbox']),
        'assignment' => [
            'title' => 'Project: A row of cards',
            'description' => '<p>Submit the portfolio with the project cards in a flex row.</p>',
            'instructions' => 'Upload HTML and CSS. The parent of the cards must use display: flex, flex-wrap, and gap. Cards must wrap when the window is narrow.',
            'prompt_question' => 'Which element did you set display: flex on, and why not on each card?',
        ],
    ],
    [
        'title' => 'CSS Grid Layout',
        'summary' => 'Build a two-dimensional layout: columns and rows, not just one line of cards.',
        'question_of_day' => 'When is a grid a better tool than a single flex row?',
        'objectives' => "Create a grid with a set number of columns.\nUse gap for both rows and columns.\nLet one item span two columns.\nChoose grid for a page layout and flex for a simple row.",
        'guidance' => "Keep flex on the card row. Use grid for the page: a main column and a side column, or a 3-column project grid. Show grid-template-columns: 2fr 1fr and a span. Do not teach every grid property.",
        'content' => $hero('Lesson 12 · 2 hours', 'CSS grid', 'Grid is for rows and columns at the same time.')
            .$plan([
                ['0:00 – 0:15', 'Sketch a page with a wide column and a narrow column'],
                ['0:15 – 0:50', 'grid-template-columns, gap, and span'],
                ['0:50 – 1:40', 'Lay the portfolio out with grid'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit'],
            ])
            .$pre(".layout {\n  display: grid;\n  grid-template-columns: 2fr 1fr;\n  gap: 24px;\n}\n\n.projects {\n  display: grid;\n  grid-template-columns: repeat(3, 1fr);\n  gap: 16px;\n}\n\n.feature {\n  grid-column: span 2;\n}")
            .$table(['Idea', 'Meaning'], [
                ['1fr', 'One share of the free space'],
                ['2fr 1fr', 'The first column is twice the second'],
                ['repeat(3, 1fr)', 'Three equal columns'],
                ['span 2', 'This item crosses two columns'],
            ])
            .$box('key', 'Key idea', '<p>Flexbox is excellent for one row that wraps. Grid is for a layout where columns and rows both matter, like a page with a sidebar, or a gallery that must stay in columns.</p>')
            .$box('try', 'Try it', $steps([
                'Make the projects section a 3-column grid with a gap.',
                'On a wide screen the three cards sit in three columns.',
                'Add a class feature to the first card and let it span 2 columns. Then decide if you like it. You may remove it.',
                'Do not set a fixed pixel width on the page.',
            ]))
            .$box('warn', 'Watch out', '<p>Three columns of 1fr on a phone make tiny columns. The next lesson fixes that with a media query. For today, check the layout on a wide window first.</p>')
            .$check(['The project area uses display: grid', 'There are three columns on a wide window', 'gap is set', 'You can say why this is grid and the navbar might stay flex'])
            .$sources(['MDN: CSS grid layout' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Grids']),
        'assignment' => [
            'title' => 'Project: A three-column grid',
            'description' => '<p>Submit the portfolio with the projects in a CSS grid.</p>',
            'instructions' => 'Upload HTML and CSS. The projects container must use display: grid, three equal columns, and gap. Say in your answer whether any item spans columns.',
            'prompt_question' => 'Where did you use grid, and where did you leave flex in place?',
        ],
    ],
    [
        'title' => 'Responsive Web Design with Media Queries',
        'summary' => 'Make the same page readable on a phone: a viewport tag and one media query.',
        'question_of_day' => 'What breaks first when you open a desktop page on a phone?',
        'objectives' => "Add the viewport meta tag.\nWrite a min-width or max-width media query on purpose.\nStack the grid into one column on a narrow screen.\nTest by resizing the browser, not by guessing.",
        'guidance' => "Phone-width first in the window, then widen it. Use max-width: 700px to stack columns. Check the viewport tag is in the head. Without it, phones zoom the desktop site out and the media query never feels right.",
        'content' => $hero('Lesson 13 · 2 hours', 'Pages that fit a phone', 'One page, two layouts. The window width decides.')
            .$plan([
                ['0:00 – 0:15', 'Open the portfolio in a narrow window and list what overflows'],
                ['0:15 – 0:45', 'The viewport tag and a media query'],
                ['0:45 – 1:40', 'Stack the grid and calm the heading size'],
                ['1:40 – 1:55', 'Lesson quiz'],
                ['1:55 – 2:00', 'Submit, with a phone-width screenshot if you can'],
            ])
            .$pre("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">")
            .$pre(".projects {\n  display: grid;\n  grid-template-columns: 1fr;\n  gap: 16px;\n}\n\n@media (min-width: 700px) {\n  .projects {\n    grid-template-columns: repeat(3, 1fr);\n  }\n\n  h1 {\n    font-size: 40px;\n  }\n}")
            .$box('key', 'Key idea', '<p>Write the narrow layout as the normal CSS. Put the wide layout inside <code>@media (min-width: 700px)</code>. A phone stays with one column. A laptop gets three.</p>')
            .$box('try', 'Try it', $steps([
                'Add the viewport meta tag in the head.',
                'Make the projects grid one column by default.',
                'Inside a min-width: 700px query, switch it to three columns.',
                'Drag the window across 700 pixels and watch the layout change. Then check the heading does not overflow.',
            ]))
            .$box('warn', 'Watch out', '<p>A media query does not load a different HTML file. It only changes CSS. If you forget the viewport tag, a phone may shrink the desktop layout instead of using your query.</p>')
            .$check(['The viewport tag is in the head', 'Narrow window: one column', 'Wide window: three columns', 'No sideways scrolling of the whole page'])
            .$sources(['MDN: Media queries' => 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Media_queries']),
        'assignment' => [
            'title' => 'Project: A page that fits a phone',
            'description' => '<p>Submit the portfolio after it stacks on a narrow window.</p>',
            'instructions' => 'Upload HTML, CSS, and a screenshot of the page narrower than 700 pixels. The head must include the viewport tag. The CSS must include a media query.',
            'prompt_question' => 'What changes at 700 pixels wide, and what stays the same?',
        ],
    ],
    [
        'title' => 'Week 2 Project: Style Your Portfolio with CSS',
        'summary' => 'Finish the portfolio look: type, cards, flex or grid, and a phone layout, all in one stylesheet.',
        'question_of_day' => 'If you handed someone only your folder, would the styled page open with its images?',
        'objectives' => "Keep all presentation in the stylesheet.\nUse the box model, a card row or grid, and a media query together.\nCheck contrast and heading size.\nZip HTML, CSS, and images.",
        'guidance' => "Build session. Checklist on the board. Refuse inline style attributes unless they are fixing a one-off and then move it into the CSS. Last 15 minutes is the zip test in a different folder.",
        'content' => $hero('Lesson 14 · 2 hours', 'Style the portfolio', 'The week 2 page is the week 1 HTML, wearing a stylesheet.')
            .$plan([
                ['0:00 – 0:10', 'Open the checklist'],
                ['0:10 – 1:35', 'Finish the stylesheet and test both widths'],
                ['1:35 – 1:50', 'Lesson quiz'],
                ['1:50 – 2:00', 'Zip and submit'],
            ])
            .$check([
                'styles.css is linked. No style attributes left in the HTML',
                'Body type is dark, at least 16px, with line-height around 1.5',
                'Cards have padding, a border or background, and space between them',
                'Projects use flex or grid, not floated guesses',
                'A viewport tag and a media query stack the layout on a phone',
                'Images still load from relative paths',
                'The zip contains html, css, and the images folder',
            ])
            .$box('key', 'Key idea', '<p>Test twice: a wide window and a window under 700 pixels. Then extract the zip somewhere else and open index.html. That is the version your teacher will see.</p>')
            .$box('warn', 'Watch out', '<p>A link written as <code>href="styles.css"</code> breaks if the CSS file is actually inside another folder. The path is relative to the HTML file.</p>')
            .$sources($mdn),
        'assignment' => [
            'title' => 'Project: Styled portfolio',
            'description' => '<p>Submit the finished week 2 portfolio.</p>',
            'instructions' => 'Zip index.html, styles.css, and images. Include one wide screenshot and one narrow screenshot. The page must match the checklist in the lesson.',
            'prompt_question' => 'What broke when you opened the extracted zip, if anything?',
        ],
    ],
];
