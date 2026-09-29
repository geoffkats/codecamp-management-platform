<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    |
    | Real, approved quotes only. The testimonials section stays hidden until
    | at least one entry exists. Each entry: quote, name, role (optional).
    |
    */

    'testimonials' => [
        // ['quote' => '...', 'name' => '...', 'role' => 'Code Camp learner'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Project showcase
    |--------------------------------------------------------------------------
    |
    | Real learner projects. When empty, the showcase shows the kinds of
    | projects built in each published learning path instead. Each entry:
    | title, category, description, image (public URL, optional), url (optional).
    |
    */

    'projects' => [
        // ['title' => '...', 'category' => 'Games', 'description' => '...', 'image' => null, 'url' => null],
    ],

    /*
    | Public gallery where children's projects are hosted.
    */

    /*
    | Shown instead of the live learner count in the homepage stats.
    | Set to an empty value to show the real count.
    */

    'learners_display' => env('HOMEPAGE_LEARNERS_DISPLAY', '500+'),

    'projects_url' => env('HOMEPAGE_PROJECTS_URL', 'https://codeacademyug.org/children-projects'),

    /*
    | Where the Code Camps "Register interest" button sends visitors.
    */

    'codecamp_register_url' => env('HOMEPAGE_CODECAMP_REGISTER_URL', 'https://codeacademyug.org/register'),

];
