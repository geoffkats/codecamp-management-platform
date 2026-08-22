<?php

return [
    'use_dompdf' => env('CERTIFICATE_USE_DOMPDF', true),

    'auto_generate_on_course_completion' => env('CERTIFICATE_AUTO_GENERATE_ON_COMPLETION', false),

    /** Blade view used for all certificate PDF/HTML output */
    'html_template' => 'certificates.profile',

    /** Royal blue used in the official ict_bg.png artwork */
    'brand_color' => env('CERTIFICATE_BRAND_COLOR', '#1546c0'),

    /** Lighter blue used for field labels / date on the artwork */
    'label_color' => env('CERTIFICATE_LABEL_COLOR', '#2d7fd4'),

    /** Thick outer frame (mm) — matches ict_bg.png reference */
    'border_width_mm' => (float) env('CERTIFICATE_BORDER_WIDTH_MM', 5),

    'default_module_version' => env('CERTIFICATE_MODULE_VERSION', '1.0'),

    /** Minimum course progress % before a student appears as certificate-ready */
    'min_progress_percent' => (int) env('CERTIFICATE_MIN_PROGRESS_PERCENT', 80),

    'executive_director' => env(
        'CERTIFICATE_EXECUTIVE_DIRECTOR',
        'Edward Ssempala, Executive Director Code Academy Uganda'
    ),

    /** Default overlay positions (mm) — tuned to ict_bg.png */
    'layout' => [
        'signature' => [
            /** Distance from page bottom to bottom edge of signature — aligns with footer line on ict_bg.png */
            'bottom_mm' => 40.5,
            'left_mm' => 28,
            'width_mm' => 55,
            'max_height_mm' => 9,
        ],
        'signatory' => [
            'top_mm' => 258,
            'left_mm' => 28,
            'width_mm' => 95,
            'font_size_pt' => 8,
        ],
        'date' => [
            'top_mm' => 250.5,
            'left_mm' => 148.5,
            'width_mm' => 31,
            'font_size_pt' => 10,
        ],
    ],

    /** Signatory profiles — map program keys to settings field suffixes */
    'signatory_profiles' => [
        'default' => ['label' => 'Default (Code Academy)'],
        'ict' => ['label' => 'ICT Program'],
        'codecamp' => ['label' => 'Code Camp Program'],
    ],

    /**
     * When true, the certificate is rendered by overlaying dynamic data on top
     * of the official artwork (logo, frame, watermark, labels are all baked in),
     * guaranteeing an exact visual match. When false, the HTML fallback is used.
     */
    'use_background_image' => env('CERTIFICATE_USE_BACKGROUND', true),

    /** Official certificate artwork (full A4). Swap this file to rebrand. */
    'background_image' => env('CERTIFICATE_BACKGROUND', public_path('certs/ict_bg.png')),

    /** Optional standalone logo (used by HTML fallback if no background). */
    'logo_image' => env('CERTIFICATE_LOGO', public_path('certs/logo.png')),

    /** Legacy path kept for reference / optional fallback */
    'template_path' => storage_path('app/certificates/template.pdf'),

    'unit' => 'mm',
    'page' => [
        'width' => 210,
        'height' => 297,
    ],
];
