<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class RichContent
{
    /**
     * Formatting tags produced by the rich-text editor. Anything outside this
     * list found raw in stored content (e.g. <nav>, <form> typed as teaching
     * material) is escaped so it renders as visible literal text instead of
     * being stripped or parsed as real markup.
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code', 'a', 'img', 'table', 'thead',
        'tbody', 'tr', 'th', 'td', 'span', 'div', 'hr', 'sub', 'sup',
    ];

    /**
     * Render lesson/question text with HTML or plain-text line breaks.
     */
    public static function render(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        $content = trim($content);
        $hasHtml = strip_tags($content) !== $content;

        if ($hasHtml) {
            $safe = self::escapeUnknownTags($content);
            $safe = strip_tags($safe, self::ALLOWED_TAGS);

            $safe = self::balanceHtml($safe);

            return self::normalizeEmbeddedMediaUrls($safe);
        }

        return nl2br(e($content));
    }

    /**
     * Escape tags that are not part of the editor's formatting vocabulary so
     * code snippets like <nav> or <form> survive as literal text.
     */
    private static function escapeUnknownTags(string $html): string
    {
        $escaped = preg_replace_callback(
            '/<\/?([a-zA-Z][a-zA-Z0-9-]*)((?:"[^"]*"|\'[^\']*\'|[^>"\'])*)>/',
            function (array $matches) {
                return in_array(strtolower($matches[1]), self::ALLOWED_TAGS, true)
                    ? $matches[0]
                    : str_replace(['<', '>'], ['&lt;', '&gt;'], $matches[0]);
            },
            $html
        );

        return $escaped ?? $html;
    }

    public static function storageUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, 'data:')
            || str_starts_with($path, 'blob:')
            || str_starts_with($path, 'mailto:')
            || str_starts_with($path, 'tel:')
            || str_starts_with($path, '#')
            || str_starts_with($path, '/')) {
            return $path;
        }

        $normalized = ltrim(preg_replace('#^/?storage/#', '', $path), '/');

        if (Storage::disk('public')->exists($normalized)) {
            return asset('storage/'.$normalized);
        }

        return asset('storage/'.$normalized);
    }

    private static function balanceHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div>'.$html.'</div></body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if (! $wrapper) {
            return $html;
        }

        $balanced = '';
        foreach ($wrapper->childNodes as $child) {
            $balanced .= $document->saveHTML($child);
        }

        return $balanced;
    }

    private static function normalizeEmbeddedMediaUrls(string $html): string
    {
        return preg_replace_callback(
            '/(<img[^>]+src=["\'])([^"\']+)(["\'])/i',
            function (array $matches) {
                $url = self::storageUrl($matches[2]) ?? $matches[2];

                return $matches[1].$url.$matches[3];
            },
            $html
        ) ?? $html;
    }
}
