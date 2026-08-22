<?php

return [
    'use_dompdf' => env('CODECLUB_REPORT_USE_DOMPDF', true),

    'html_template' => 'reports.codeclub-student-report',
    'school_summary_template' => 'reports.codeclub-school-summary',
    'legacy_html_template' => 'reports.codeclub-term-report',

    'brand' => [
        'orange' => '#f97316',
        'orange_dark' => '#ea580c',
        'blue' => '#1e3a5f',
        'blue_bright' => '#1e40af',
        'navy' => '#0f172a',
        'light_blue' => '#dbeafe',
        'light_orange' => '#fff7ed',
        'light_green' => '#ecfdf5',
        'gray' => '#64748b',
        'gray_light' => '#f1f5f9',
        'green' => '#16a34a',
        'red' => '#ef4444',
        'gold' => '#ca8a04',
    ],

    'tracks' => [
        'scratch' => [
            'key' => 'scratch',
            'label' => 'Scratch Programming',
            'short_label' => 'Scratch',
            'color' => '#f97316',
            'light' => '#fff7ed',
            'keywords' => ['scratch'],
        ],
        'robotics' => [
            'key' => 'robotics',
            'label' => 'Robotics',
            'short_label' => 'Robotics',
            'color' => '#1e40af',
            'light' => '#dbeafe',
            'keywords' => ['robotics', 'microbit', 'arduino', 'robot'],
        ],
        'ai_ml' => [
            'key' => 'ai_ml',
            'label' => 'AI & Machine Learning',
            'short_label' => 'AI & ML',
            'color' => '#16a34a',
            'light' => '#ecfdf5',
            'keywords' => ['ai', 'machine learning', 'ml for kids', 'artificial intelligence', 'teachable machine'],
        ],
    ],

    'course_dot_colors' => ['#8b5cf6', '#f97316', '#1e40af', '#eab308', '#06b6d4', '#ec4899'],

    'tagline' => 'CODE TODAY. CHANGE TOMORROW.',
    'subtitle' => 'Empowering young minds through technology and innovation.',
    'partner_label' => 'In Partnership with',
    'institution_label' => 'Code Academy Uganda',
    'operated_by_label' => 'Code Genius Academy',
    'report_title' => 'STUDENT PROGRESS REPORT',
    'detail_title' => 'DETAILED LEARNING REPORT',
    'school_summary_title' => 'CODE CLUB SUMMARY REPORT',
    'single_page' => true,

    'contact' => [
        'address' => env('CODECLUB_REPORT_ADDRESS', 'Plot 12, Kampala Road, Kampala, Uganda'),
        'phone' => env('CODECLUB_REPORT_PHONE', '+256 700 000 000'),
        'email' => env('CODECLUB_REPORT_EMAIL', 'info@codeacademy.ug'),
        'website' => env('CODECLUB_REPORT_WEBSITE', 'www.codeacademy.ug'),
        'social' => env('CODECLUB_REPORT_SOCIAL', '@CodeAcademyUG'),
    ],

    'default_term_label' => env('CODECLUB_REPORT_TERM_LABEL', null),

    'default_instructor_comment' => 'This student has shown consistent effort and engagement throughout the term. They participate actively in sessions and demonstrate growing confidence with coding concepts.',

    'default_summary' => 'An enthusiastic learner who participates well in club sessions and shows steady progress across coding activities.',

    'inspirational_quote' => [
        'text' => 'The beautiful thing about learning is that nobody can take it away from you.',
        'author' => 'B.B. King',
    ],

    'behavior_keys' => [
        'participation' => 'Class Participation',
        'collaboration' => 'Collaboration',
        'initiative' => 'Initiative',
        'responsibility' => 'Responsibility',
    ],

    'skill_keys' => [
        'logical_thinking' => 'Logical Thinking',
        'problem_solving' => 'Problem Solving',
        'creativity' => 'Creativity',
        'teamwork' => 'Teamwork',
        'technical_skills' => 'Technical Skills',
    ],

    'grading_scale' => [
        ['grade' => 'A+', 'range' => '97–100%', 'label' => 'Excellent'],
        ['grade' => 'A', 'range' => '93–96%', 'label' => 'Excellent'],
        ['grade' => 'A-', 'range' => '90–92%', 'label' => 'Very Good'],
        ['grade' => 'B+', 'range' => '87–89%', 'label' => 'Good'],
        ['grade' => 'B', 'range' => '83–86%', 'label' => 'Good'],
        ['grade' => 'B-', 'range' => '80–82%', 'label' => 'Satisfactory'],
        ['grade' => 'C+', 'range' => '77–79%', 'label' => 'Satisfactory'],
        ['grade' => 'C', 'range' => '73–76%', 'label' => 'Average'],
        ['grade' => 'C-', 'range' => '70–72%', 'label' => 'Average'],
        ['grade' => 'D', 'range' => '60–69%', 'label' => 'Below Average'],
        ['grade' => 'F', 'range' => 'Below 60%', 'label' => 'Needs Improvement'],
    ],

    'performance_labels' => [
        'A+' => 'Excellent', 'A' => 'Excellent', 'A-' => 'Very Good',
        'B+' => 'Good', 'B' => 'Good', 'B-' => 'Satisfactory',
        'C+' => 'Satisfactory', 'C' => 'Average', 'C-' => 'Average',
        'D+' => 'Below Average', 'D' => 'Below Average', 'D-' => 'Below Average',
        'F' => 'Needs Improvement',
    ],

    'score_labels' => [
        ['min' => 95, 'label' => 'Outstanding'],
        ['min' => 90, 'label' => 'Excellent'],
        ['min' => 85, 'label' => 'Very Good'],
        ['min' => 80, 'label' => 'Good'],
        ['min' => 70, 'label' => 'Satisfactory'],
        ['min' => 0, 'label' => 'Needs Improvement'],
    ],

    'performance_metrics' => [
        ['key' => 'teamwork_collaboration', 'label' => 'Teamwork & Collaboration', 'source' => 'overall', 'offset' => 3.5],
        ['key' => 'documentation_communication', 'label' => 'Documentation & Communication', 'source' => 'assignments', 'offset' => 2.0],
        ['key' => 'technical_skills_application', 'label' => 'Technical Skills & Application', 'source' => 'overall', 'offset' => 2.5],
        ['key' => 'concept_application', 'label' => 'Concept Application', 'source' => 'quiz', 'offset' => 1.0],
        ['key' => 'creativity_innovation', 'label' => 'Creativity & Innovation', 'source' => 'overall', 'offset' => 0.5],
        ['key' => 'project_completion', 'label' => 'Project Completion', 'source' => 'assignments', 'offset' => 0.0],
        ['key' => 'coding_proficiency', 'label' => 'Coding Proficiency', 'source' => 'quiz', 'offset' => 0.0],
        ['key' => 'attendance_participation', 'label' => 'Attendance & Participation', 'source' => 'attendance', 'offset' => 0.0],
        ['key' => 'engagement_effort', 'label' => 'Engagement & Effort', 'source' => 'overall', 'offset' => -1.5],
        ['key' => 'problem_solving', 'label' => 'Problem Solving', 'source' => 'quiz', 'offset' => -2.0],
    ],

    'report_metrics_limit' => 10,
    'report_comment_max_chars' => 200,

    'metric_grade_scale' => [
        ['min' => 90, 'grade' => 'A+'],
        ['min' => 85, 'grade' => 'A'],
        ['min' => 80, 'grade' => 'A-'],
        ['min' => 75, 'grade' => 'B+'],
        ['min' => 70, 'grade' => 'B'],
        ['min' => 65, 'grade' => 'B-'],
        ['min' => 60, 'grade' => 'C+'],
        ['min' => 55, 'grade' => 'C'],
        ['min' => 50, 'grade' => 'C-'],
        ['min' => 0,  'grade' => 'D'],
    ],

    'dompdf' => [
        'dpi' => 96,
        'default_font' => 'DejaVu Sans',
        'font_height_ratio' => 1.0,
        'chroot' => [public_path(), storage_path('app/public')],
    ],
];
