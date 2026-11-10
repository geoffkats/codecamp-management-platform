<?php

use App\Support\LessonBlocks;

return [
    'hero' => fn (string $kicker, string $title, string $tagline): string => LessonBlocks::hero(
        $kicker,
        $title,
        $tagline,
        'Web Development 1 · Code Academy Uganda'
    ),
    'h2' => fn (string $text): string => LessonBlocks::h2($text),
    'h3' => fn (string $text): string => '<h3 style="'.LessonBlocks::H3_STYLE.'">'.$text.'</h3>',
    'box' => fn (string $kind, string $label, string $html): string => LessonBlocks::box($kind, $label, $html),
    'steps' => fn (array $items): string => LessonBlocks::steps($items),
    'table' => fn (array $headers, array $rows): string => LessonBlocks::table($headers, $rows),
    'check' => fn (array $items): string => LessonBlocks::checklist($items),
    'sources' => fn (array $links): string => LessonBlocks::sources($links),
    'pre' => function (string $code): string {
        $safe = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<pre style="background:#0f172a;color:#e2e8f0;border-radius:12px;padding:16px 18px;overflow:auto;font-size:14px;line-height:1.55;"><code>'.$safe.'</code></pre>';
    },
    'plan' => function (array $rows): string {
        return LessonBlocks::h2('Today\'s 2-hour plan').LessonBlocks::table(['Time', 'What we do'], $rows);
    },
    'p' => fn (string $html): string => '<p>'.$html.'</p>',
];
