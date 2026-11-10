<?php

return [
    'course' => [
        'short_description' => 'A 3-week camp: HTML, CSS, and Bootstrap. One 2-hour lesson a day, with a quiz and a project to hand in.',
        'description' => '<p><strong>Web Development 1</strong> is a three-week Code Camp. Each day is a <strong>2-hour lesson</strong>. Week 1 is HTML, week 2 is CSS, and week 3 is Bootstrap. Learners finish with a three-page camp site.</p><p>Every lesson has notes for the class, a short quiz from the Web 1 question bank, and an assignment where students submit the page they built.</p>',
        'difficulty_level' => 'Beginner',
        'estimated_duration' => 30,
        'category' => 'Web Development',
        'what_you_learn' => [
            'Build a complete HTML page with headings, lists, links, images, and forms',
            'Use semantic tags so a page has a header, navigation, main content, and footer',
            'Style pages with CSS: selectors, colour, the box model, and flexbox',
            'Make a page readable on a phone with the viewport tag and a media query',
            'Lay out pages with the Bootstrap grid, cards, forms, and a collapsing navbar',
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
            'title' => 'Week 1: HTML',
            'description' => 'Five 2-hour days. How the web works, text, links, images, forms, and a semantic profile page.',
            'overview' => 'Learners leave week 1 with a one-page profile written in real HTML, ready to style.',
            'hours' => 10,
            'lessons' => require __DIR__.'/week1.php',
        ],
        [
            'title' => 'Week 2: CSS',
            'description' => 'Five 2-hour days. Selectors, the box model, flexbox, responsive pages, and a styled profile.',
            'overview' => 'Learners move the look into a stylesheet and make the week 1 page work on a phone.',
            'hours' => 10,
            'lessons' => require __DIR__.'/week2.php',
        ],
        [
            'title' => 'Week 3: Bootstrap',
            'description' => 'Five 2-hour days. The grid, cards, forms, a navbar, utilities, and a three-page final site.',
            'overview' => 'Learners rebuild their camp pages with Bootstrap and submit a three-page site.',
            'hours' => 10,
            'lessons' => require __DIR__.'/week3.php',
        ],
    ],
];
