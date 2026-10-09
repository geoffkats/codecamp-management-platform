<?php

namespace App\Support;

/**
 * Styled lesson components (banner, callout boxes, block pills, tables).
 *
 * Lesson HTML is rendered by RichContent and edited in the TipTap lesson editor, so every component is plain
 * HTML with inline styles. Containers carry data-block="…" so the editor keeps them as editable boxes; the
 * markup here must stay in sync with resources/js/components/lesson-blocks.js.
 */
class LessonBlocks
{
    public const BOXES = [
        'key' => ['label' => 'Key idea', 'background' => '#eff6ff', 'accent' => '#2563eb', 'text' => '#1e3a8a'],
        'tip' => ['label' => 'Tip', 'background' => '#ecfdf5', 'accent' => '#059669', 'text' => '#064e3b'],
        'warn' => ['label' => 'Watch out', 'background' => '#fffbeb', 'accent' => '#d97706', 'text' => '#78350f'],
        'try' => ['label' => 'Try it', 'background' => '#f5f3ff', 'accent' => '#7c3aed', 'text' => '#4c1d95'],
        'teacher' => ['label' => 'Teacher note', 'background' => '#f8fafc', 'accent' => '#475569', 'text' => '#1e293b'],
        'think' => ['label' => 'Think about it', 'background' => '#fff1f2', 'accent' => '#e11d48', 'text' => '#881337'],
    ];

    public const PILLS = [
        'ml' => ['#0d9488', '#ffffff'],
        'event' => ['#ffbf00', '#3b2f00'],
        'control' => ['#ffab19', '#3b2600'],
        'looks' => ['#9966ff', '#ffffff'],
        'sound' => ['#cf63cf', '#ffffff'],
        'operator' => ['#59c059', '#ffffff'],
        'data' => ['#ff8c1a', '#ffffff'],
        'robot' => ['#2563eb', '#ffffff'],
    ];

    public const HERO_STYLE = 'background:#1e3a8a;color:#ffffff;border-radius:16px;padding:28px 32px;margin:0 0 28px 0;';

    public const H2_STYLE = 'margin-top:36px;padding-bottom:6px;border-bottom:3px solid #f97316;';

    public const H3_STYLE = 'color:#1e3a8a;';

    public const TH_STYLE = 'background:#1e3a8a;color:#ffffff;text-align:left;padding:10px 12px;';

    public const TD_STYLE = 'padding:10px 12px;vertical-align:top;';

    public const TABLE_STYLE = 'width:100%;border-collapse:collapse;margin:16px 0;';

    public static function hero(string $badge, string $title, string $tagline = '', string $meta = ''): string
    {
        return '<div data-block="hero" style="'.self::HERO_STYLE.'">'
            .'<p style="margin:0;"><span style="display:inline-block;background:#f97316;color:#ffffff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:999px;">'.$badge.'</span></p>'
            .'<h1 style="color:#ffffff;margin:14px 0 8px 0;font-size:30px;line-height:1.2;">'.$title.'</h1>'
            .($tagline !== '' ? '<p style="color:#dbeafe;margin:0 0 14px 0;font-size:17px;">'.$tagline.'</p>' : '')
            .($meta !== '' ? '<p style="color:#bfdbfe;margin:0;font-size:13px;">'.$meta.'</p>' : '')
            .'</div>';
    }

    public static function boxStyle(string $kind): string
    {
        $box = self::BOXES[$kind] ?? self::BOXES['key'];

        return 'background:'.$box['background'].';border-left:6px solid '.$box['accent'].';border-radius:12px;padding:16px 20px;margin:20px 0;color:'.$box['text'].';';
    }

    public static function labelStyle(string $kind): string
    {
        $box = self::BOXES[$kind] ?? self::BOXES['key'];

        return 'margin:0 0 6px 0;font-weight:700;color:'.$box['accent'].';text-transform:uppercase;font-size:13px;letter-spacing:.06em;';
    }

    /**
     * Callout box. $html is the box body and should be block HTML (<p>, <ul>, <ol>, <table>…).
     */
    public static function box(string $kind, ?string $label, string $html): string
    {
        $kind = isset(self::BOXES[$kind]) ? $kind : 'key';
        $label ??= self::BOXES[$kind]['label'];

        return self::openBox($kind, $label).$html.'</div>';
    }

    public static function openBox(string $kind, string $label): string
    {
        return '<div data-block="'.$kind.'" style="'.self::boxStyle($kind).'">'
            .'<p style="'.self::labelStyle($kind).'">'.$label.'</p>';
    }

    public static function pillStyle(string $kind = 'ml'): string
    {
        [$background, $color] = self::PILLS[$kind] ?? self::PILLS['ml'];

        return 'display:inline-block;background:'.$background.';color:'.$color.';font-family:ui-monospace,Consolas,monospace;font-size:14px;font-weight:600;padding:2px 10px;border-radius:999px;margin:2px 0;';
    }

    public static function pill(string $text, string $kind = 'ml'): string
    {
        return '<span style="'.self::pillStyle($kind).'">'.$text.'</span>';
    }

    public static function h2(string $text): string
    {
        return '<h2 style="'.self::H2_STYLE.'">'.$text.'</h2>';
    }

    /**
     * @param  array<int, string>  $items
     */
    public static function steps(array $items): string
    {
        $html = '<ol>';
        foreach ($items as $item) {
            $html .= '<li style="margin-bottom:8px;">'.$item.'</li>';
        }

        return $html.'</ol>';
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     */
    public static function table(array $headers, array $rows): string
    {
        $html = '<table style="'.self::TABLE_STYLE.'"><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th style="'.self::TH_STYLE.'">'.$header.'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="'.self::TD_STYLE.'">'.$cell.'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</tbody></table>';
    }

    /**
     * @param  array<int, string>  $items
     */
    public static function checklist(array $items, string $title = 'Before you submit, check that you can:'): string
    {
        $html = '<div data-block="checklist" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:16px 20px;margin:20px 0;color:#1e293b;">'
            .'<p style="margin:0 0 8px 0;font-weight:700;color:#1e3a8a;">'.$title.'</p><ul>';
        foreach ($items as $item) {
            $html .= '<li style="margin-bottom:6px;">'.$item.'</li>';
        }

        return $html.'</ul></div>';
    }

    /**
     * @param  array<string, string>  $links  label => url
     */
    public static function sources(array $links): string
    {
        $parts = [];
        foreach ($links as $label => $url) {
            $parts[] = '<a href="'.$url.'">'.$label.'</a>';
        }

        return '<p style="font-size:13px;color:#64748b;margin-top:32px;"><strong>Further reading:</strong> '.implode(' · ', $parts).'</p>';
    }
}
