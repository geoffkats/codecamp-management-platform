<?php

namespace App\Support;

use DOMElement;
use DOMNode;
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
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'strike', 'mark',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code', 'a', 'img', 'table', 'thead',
        'tbody', 'tr', 'th', 'td', 'colgroup', 'col',
        'span', 'div', 'hr', 'sub', 'sup',
        'iframe', 'figure', 'figcaption', 'video', 'source',
        'input', 'label',
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

        self::sanitizeNode($wrapper);

        $balanced = '';
        foreach ($wrapper->childNodes as $child) {
            $balanced .= $document->saveHTML($child);
        }

        return $balanced;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                if (! self::sanitizeElement($child)) {
                    $child->parentNode?->removeChild($child);
                    continue;
                }
            }

            self::sanitizeNode($child);
        }
    }

    /**
     * @return bool false when the element should be removed
     */
    private static function sanitizeElement(DOMElement $element): bool
    {
        $tag = strtolower($element->tagName);
        $remove = [];

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on') || $name === 'srcdoc' || $name === 'formaction' || $name === 'xlink:href') {
                $remove[] = $attribute->name;
            }
        }

        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }

        if ($tag === 'a') {
            $href = $element->getAttribute('href');
            if ($href !== '' && ! self::isSafeUrl($href, ['http', 'https', 'mailto', 'tel'])) {
                $element->removeAttribute('href');
            }
        }

        if (in_array($tag, ['img', 'video', 'source'], true)) {
            $src = $element->getAttribute('src');
            if ($src !== '' && ! self::isSafeMediaSrc($src)) {
                $element->removeAttribute('src');
            }
        }

        if ($tag === 'iframe') {
            $src = $element->getAttribute('src');
            if (! self::isAllowedEmbedSrc($src)) {
                return false;
            }

            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->name);
                if (! in_array($name, ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'title', 'class', 'style'], true)) {
                    $element->removeAttribute($attribute->name);
                }
            }

            $element->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
            $element->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        }

        if ($tag === 'input') {
            if (strtolower($element->getAttribute('type')) !== 'checkbox') {
                return false;
            }

            $element->setAttribute('disabled', 'disabled');
            $element->removeAttribute('name');
            $element->removeAttribute('form');
            $element->removeAttribute('value');
        }

        return true;
    }

    private static function isSafeUrl(string $url, array $schemes): bool
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, $schemes, true);
    }

    private static function isSafeMediaSrc(string $src): bool
    {
        $src = trim($src);

        if ($src === '' || str_starts_with($src, '/') || str_starts_with($src, 'data:image/')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $src);
    }

    private static function isAllowedEmbedSrc(string $src): bool
    {
        $src = trim($src);

        if ($src === '' || ! preg_match('#^https://#i', $src)) {
            return false;
        }

        $parts = parse_url($src);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = $parts['path'] ?? '';

        $youtube = in_array($host, [
            'www.youtube.com',
            'youtube.com',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
        ], true) && str_starts_with($path, '/embed/');

        $vimeo = $host === 'player.vimeo.com' && str_starts_with($path, '/video/');
        $scratch = $host === 'scratch.mit.edu' && (bool) preg_match('#^/projects/\d+/embed/?$#', $path);

        return $youtube || $vimeo || $scratch;
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
