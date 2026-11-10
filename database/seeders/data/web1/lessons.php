<?php

return [
    'course' => [
        'short_description' => 'The full Web Development 1 syllabus: HTML, CSS, and Bootstrap. Every lesson is a 2-hour class with notes, a quiz, and a project to hand in.',
        'description' => '<p><strong>Web Development 1</strong> covers the full beginner syllabus. HTML foundations come first, then CSS layout, then Bootstrap. Each lesson is written as a <strong>2-hour class</strong>.</p><p>Every lesson has class notes, a short quiz from the Web 1 question bank, and an assignment where students submit the page they built. The course ends with a three-page camp site.</p>',
        'difficulty_level' => 'Beginner',
        'estimated_duration' => 42,
        'category' => 'Web Development',
        'what_you_learn' => [
            'Build a complete HTML page with headings, lists, links, images, and forms',
            'Use semantic tags so a page has a header, navigation, main content, and footer',
            'Style pages with CSS: selectors, colour, the box model, and flexbox',
            'Make a page readable on a phone with the viewport tag and a media query',
            'Group a page with divs, spans, ids, and classes, then mark the real regions with semantic tags',
            'Lay out pages with flexbox, CSS grid, and a media query for phones',
            'Build with Bootstrap: grid, cards, forms, a collapsing navbar, utilities, and a small theme override',
            'Publish a three-page site and submit the files',
        ],
    ],
    'assignment_defaults' => [
        'file_types' => ['html', 'css', 'png', 'jpg', 'jpeg', 'webp', 'zip', 'pdf'],
        'upload_question' => 'Upload your project files. Zip the HTML, CSS, and images together when there is more than one file. You can also add screenshots (PNG or JPG). Up to 3 files, 20 MB each.',
        'xp' => 50,
    ],
    'modules' => [
        [
            'title' => 'HTML Foundations',
            'description' => 'Seven 2-hour lessons. How the web works, text, links, images, forms, semantic tags, divs and classes, then a portfolio page.',
            'overview' => 'Learners leave this module with a one-page portfolio written in real HTML, ready to style.',
            'hours' => 14,
            'lessons' => require __DIR__.'/html.php',
        ],
        [
            'title' => 'CSS Styling and Layout',
            'description' => 'Seven 2-hour lessons. Stylesheets, colour and type, the box model, flexbox, grid, phone layouts, then a styled portfolio.',
            'overview' => 'Learners move the look into a stylesheet and make the portfolio work on a phone.',
            'hours' => 14,
            'lessons' => require __DIR__.'/css.php',
        ],
        [
            'title' => 'Bootstrap Framework',
            'description' => 'Seven 2-hour lessons. The CDN and grid, components, a navbar, utilities, alerts and modals, a small theme, then a three-page site.',
            'overview' => 'Learners rebuild their camp pages with Bootstrap and submit a three-page site.',
            'hours' => 14,
            'lessons' => require __DIR__.'/bootstrap.php',
        ],
    ],
];
