<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Gives existing lesson HTML the LessonBlocks look without rewriting any of the teaching text:
 * banner on top, callout boxes for blockquotes / "Tip:" paragraphs / known sections, orange h2s, styled tables.
 */
class LessonRestyler
{
    /**
     * Section headings that become boxes, matched against the start of the heading text.
     */
    private const SECTION_KINDS = [
        'checklist' => '/^(build |upload |submission |final )?check ?list/',
        'teacher' => '/^(teacher|teachers|teacher\'s|facilitator|for teachers|instructor)( notes?| tips?| guide)?\b/',
        'warn' => '/^(safety|warning|warnings|important|caution|common mistakes?|common errors?|troubleshooting|watch out|be careful)\b/',
        'tip' => '/^(tips?|pro tips?|hints?|did you know|fun facts?|top tips?)\b/',
        'think' => '/^(think about it|think|discussion|discuss|reflection|reflect|questions to think about|food for thought)\b/',
        'try' => '/^(try this|try it|activity|activities|class activity|challenges?|exercises?|practice|practise|your turn|tasks?|mini project|hands[- ]on|let\'?s practi[cs]e|homework|upload task)\b/',
        'key' => '/^(summary|in summary|key points?|key takeaways?|takeaways?|remember|recap|key ideas?|key terms|vocabulary|what you will learn|what you\'ll learn|learning objectives|lesson objectives|objectives|today\'?s goal|lesson goals?|goals)\b/',
    ];

    /**
     * Words at the start of a paragraph ("Tip: …") that turn it into a box.
     */
    private const PARAGRAPH_KINDS = [
        'tip' => ['tip', 'pro tip', 'hint', 'did you know', 'fun fact'],
        'key' => ['note', 'remember', 'key point', 'key idea', 'definition'],
        'warn' => ['important', 'warning', 'caution', 'safety', 'watch out'],
        'try' => ['activity', 'challenge', 'exercise', 'try this', 'try it', 'task', 'your turn', 'upload task'],
        'teacher' => ['teacher note', 'teacher\'s note', 'teacher tip'],
        'think' => ['think about it', 'discuss', 'question'],
    ];

    private const BLOCK_TAGS = ['p', 'ul', 'ol', 'table', 'pre', 'blockquote', 'div', 'figure', 'img', 'iframe', 'video', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    /** @var array<string, int> */
    private array $stats = [];

    private DOMDocument $document;

    public static function isStyled(?string $html): bool
    {
        return $html !== null && (str_contains($html, 'data-block=') || preg_match('/border-left:\s*6px solid/', $html) === 1);
    }

    /**
     * @param  array{badge: string, title: string, tagline?: string, meta?: string}|null  $hero
     */
    public function restyle(string $html, ?array $hero = null): string
    {
        $this->stats = ['boxes' => 0, 'headings' => 0, 'tables' => 0, 'empty_removed' => 0, 'banner' => 0];

        $wrapper = $this->load($html);
        if ($wrapper === null) {
            return $html;
        }

        $this->removeEmptyParagraphs($wrapper);
        $this->promoteBoldParagraphs($wrapper);
        if ($hero !== null) {
            $this->removeDuplicateTitle($wrapper, $hero['title']);
        }
        $this->boxSections($wrapper);
        $this->boxBlockquotes($wrapper);
        $this->boxLabelledParagraphs($wrapper);
        $this->styleHeadings($wrapper);
        $this->styleTables($wrapper);

        $output = '';
        foreach ($wrapper->childNodes as $child) {
            $output .= $this->document->saveHTML($child);
        }

        if ($hero !== null) {
            $this->stats['banner'] = 1;
            $output = LessonBlocks::hero(
                e($hero['badge']),
                e($hero['title']),
                e($hero['tagline'] ?? ''),
                e($hero['meta'] ?? ''),
            ).$output;
        }

        return $output;
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return $this->stats;
    }

    private function load(string $html): ?DOMElement
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $this->document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="lesson-restyle-root">'.$html.'</div></body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_PARSEHUGE
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $this->document->getElementById('lesson-restyle-root');
    }

    /**
     * @return array<int, DOMNode>
     */
    private function children(DOMNode $node): array
    {
        return iterator_to_array($node->childNodes);
    }

    private function text(DOMNode $node): string
    {
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $node->textContent) ?? '');
    }

    /**
     * Heading text without numbering, "Section 3:" prefixes or emoji, lower-cased.
     */
    private function normalizedHeading(DOMNode $node): string
    {
        $text = mb_strtolower($this->text($node));
        $text = preg_replace('/^[^\p{L}\p{N}]+/u', '', $text) ?? $text;
        $text = preg_replace('/^(section|part|step|lesson|day|module)?\s*\d+[a-z]?\s*[:.)\-–—]\s*/u', '', $text) ?? $text;

        return trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $text) ?? $text);
    }

    private function isHeading(DOMNode $node): bool
    {
        return $node instanceof DOMElement && preg_match('/^h[1-6]$/', strtolower($node->tagName)) === 1;
    }

    private function headingLevel(DOMElement $node): int
    {
        return (int) substr(strtolower($node->tagName), 1);
    }

    private function hasMedia(DOMNode $node): bool
    {
        return $node instanceof DOMElement
            && ($node->getElementsByTagName('img')->length > 0
                || $node->getElementsByTagName('iframe')->length > 0
                || $node->getElementsByTagName('video')->length > 0
                || $node->getElementsByTagName('input')->length > 0);
    }

    private function removeEmptyParagraphs(DOMElement $root): void
    {
        foreach ($this->children($root) as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'p' && $this->text($child) === '' && ! $this->hasMedia($child)) {
                $root->removeChild($child);
                $this->stats['empty_removed']++;
            }
        }
    }

    /**
     * "<p><strong>Processor</strong></p>" used as a heading becomes a real <h3>.
     */
    private function promoteBoldParagraphs(DOMElement $root): void
    {
        foreach ($this->children($root) as $child) {
            if (! $child instanceof DOMElement || strtolower($child->tagName) !== 'p' || $this->hasMedia($child)) {
                continue;
            }

            $text = $this->text($child);
            $length = mb_strlen($text);
            if ($length < 3 || $length > 80 || preg_match('/([.,;!?]|\b(and|or|of|the|to|a|an|in|for|with|on|by))$/iu', $text)) {
                continue;
            }

            $bold = null;
            foreach ($child->childNodes as $node) {
                if ($node instanceof DOMText && $this->text($node) === '') {
                    continue;
                }
                if ($node instanceof DOMElement && strtolower($node->tagName) === 'br') {
                    continue;
                }
                if ($bold === null && $node instanceof DOMElement && in_array(strtolower($node->tagName), ['strong', 'b'], true)) {
                    $bold = $node;

                    continue;
                }
                $bold = null;
                break;
            }

            if ($bold === null || $this->text($bold) !== $text) {
                continue;
            }

            $heading = $this->document->createElement('h3');
            $heading->appendChild($this->document->createTextNode(rtrim($text, ': ')));
            $root->replaceChild($heading, $child);
            $this->stats['headings']++;
        }
    }

    private function removeDuplicateTitle(DOMElement $root, string $title): void
    {
        foreach ($this->children($root) as $child) {
            if ($child instanceof DOMText && $this->text($child) === '') {
                continue;
            }
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'h1'
                && mb_strtolower($this->text($child)) === mb_strtolower(trim($title))) {
                $root->removeChild($child);
            }

            return;
        }
    }

    private function sectionKind(DOMNode $heading): ?string
    {
        $text = $this->normalizedHeading($heading);
        if ($text === '') {
            return null;
        }

        foreach (self::SECTION_KINDS as $kind => $pattern) {
            if (preg_match($pattern, $text)) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * A heading such as "Try this" or "Safety" and the content under it (up to the next heading of the same level) go into a box.
     */
    private function boxSections(DOMElement $root): void
    {
        $nodes = $this->children($root);
        $count = count($nodes);

        for ($i = 0; $i < $count; $i++) {
            $heading = $nodes[$i];
            if (! $this->isHeading($heading) || $heading->parentNode !== $root) {
                continue;
            }

            $kind = $this->sectionKind($heading);
            if ($kind === null) {
                continue;
            }

            $level = $this->headingLevel($heading);
            $section = [];
            for ($j = $i + 1; $j < $count; $j++) {
                $next = $nodes[$j];
                if ($this->isHeading($next) && ($this->headingLevel($next) <= $level || $this->sectionKind($next) !== null)) {
                    break;
                }
                if ($next instanceof DOMElement && strtolower($next->tagName) === 'hr') {
                    break;
                }
                $section[] = $next;
            }

            $content = array_filter($section, fn (DOMNode $node) => ! ($node instanceof DOMText && $this->text($node) === ''));
            if ($content === [] || count($content) > 12) {
                continue;
            }

            $box = $kind === 'checklist'
                ? $this->checklistElement($this->text($heading))
                : $this->boxElement($kind, $this->text($heading));
            $root->replaceChild($box, $heading);
            foreach ($section as $node) {
                $box->appendChild($node);
            }
            $this->stats['boxes']++;
            $i += count($section);
        }
    }

    private function boxBlockquotes(DOMElement $root): void
    {
        foreach ($this->children($root) as $child) {
            if (! $child instanceof DOMElement || strtolower($child->tagName) !== 'blockquote') {
                continue;
            }

            $match = $this->labelFor($this->text($child));
            [$kind, $label] = $match ?? ['key', LessonBlocks::BOXES['key']['label']];
            if ($match !== null) {
                $this->stripLeadingLabel($child);
            }

            $box = $this->boxElement($kind, $label);
            foreach ($this->children($child) as $node) {
                $box->appendChild($node);
            }
            $this->wrapLooseInline($box);
            $root->replaceChild($box, $child);
            $this->stats['boxes']++;
        }
    }

    private function boxLabelledParagraphs(DOMElement $root): void
    {
        foreach ($this->children($root) as $child) {
            if (! $child instanceof DOMElement || strtolower($child->tagName) !== 'p') {
                continue;
            }

            $match = $this->labelFor($this->text($child));
            if ($match === null) {
                continue;
            }

            [$kind, $label] = $match;
            $this->stripLeadingLabel($child);
            $box = $this->boxElement($kind, $label);
            $root->replaceChild($box, $child);
            $box->appendChild($child);
            $this->stats['boxes']++;
        }
    }

    /**
     * "Tip: …", "💡 Note - …" → ['tip', 'Tip'].
     *
     * @return array{0: string, 1: string}|null
     */
    private function labelFor(string $text): ?array
    {
        $text = preg_replace('/^[^\p{L}]+/u', '', $text) ?? $text;

        foreach (self::PARAGRAPH_KINDS as $kind => $words) {
            foreach ($words as $word) {
                if (preg_match('/^'.preg_quote($word, '/').'\s*[:!\-–—]/iu', $text)) {
                    return [$kind, mb_convert_case($word, MB_CASE_TITLE)];
                }
            }
        }

        return null;
    }

    /**
     * Removes the "Tip:" text the box label now shows, from the first text in the element.
     */
    private function stripLeadingLabel(DOMElement $element): void
    {
        $node = $element;
        while ($node->firstChild !== null) {
            $first = $node->firstChild;
            if ($first instanceof DOMText && $this->text($first) === '' && $first->nextSibling !== null) {
                $node->removeChild($first);

                continue;
            }
            if (! $first instanceof DOMElement) {
                break;
            }
            $node = $first;
        }

        $textNode = $node->firstChild ?? $node;
        if (! $textNode instanceof DOMText) {
            return;
        }

        $stripped = preg_replace('/^[^\p{L}]*[\p{L}\' ]+?(\s*[:!\-–—]\s*|\s*$)/u', '', $textNode->data, 1);
        if ($stripped === null) {
            return;
        }
        $textNode->data = $stripped;

        while ($node !== $element && $node instanceof DOMElement && $this->text($node) === '') {
            $parent = $node->parentNode;
            $parent?->removeChild($node);
            $node = $parent;
        }

        $next = $element->firstChild;
        if ($next instanceof DOMText) {
            $next->data = preg_replace('/^[\s:\x{00A0}\-–—]+/u', '', $next->data) ?? $next->data;
        }
    }

    private function boxElement(string $kind, string $label): DOMElement
    {
        $box = $this->document->createElement('div');
        $box->setAttribute('data-block', $kind);
        $box->setAttribute('style', LessonBlocks::boxStyle($kind));

        $title = $this->document->createElement('p');
        $title->setAttribute('style', LessonBlocks::labelStyle($kind));
        $title->appendChild($this->document->createTextNode(rtrim($label, ': ')));
        $box->appendChild($title);

        return $box;
    }

    private function checklistElement(string $label): DOMElement
    {
        $box = $this->document->createElement('div');
        $box->setAttribute('data-block', 'checklist');
        $box->setAttribute('style', 'background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:16px 20px;margin:20px 0;color:#1e293b;');

        $title = $this->document->createElement('p');
        $title->setAttribute('style', 'margin:0 0 8px 0;font-weight:700;color:#1e3a8a;');
        $title->appendChild($this->document->createTextNode(rtrim($label, ': ')));
        $box->appendChild($title);

        return $box;
    }

    /**
     * Inline text left directly inside a box (e.g. from a blockquote without <p>) goes into a paragraph.
     */
    private function wrapLooseInline(DOMElement $box): void
    {
        $paragraph = null;
        foreach ($this->children($box) as $node) {
            $isBlock = $node instanceof DOMElement && in_array(strtolower($node->tagName), self::BLOCK_TAGS, true);
            if ($isBlock || ($node instanceof DOMText && $this->text($node) === '' && $paragraph === null)) {
                $paragraph = null;

                continue;
            }
            if ($paragraph === null) {
                $paragraph = $this->document->createElement('p');
                $box->insertBefore($paragraph, $node);
            }
            $paragraph->appendChild($node);
        }
    }

    private function styleHeadings(DOMElement $root): void
    {
        foreach ($this->children($root) as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'h2' && ! str_contains($child->getAttribute('style'), 'border-bottom')) {
                $child->setAttribute('style', trim($child->getAttribute('style').' '.LessonBlocks::H2_STYLE));
                $this->stats['headings']++;
            }
        }
    }

    private function styleTables(DOMElement $root): void
    {
        foreach (iterator_to_array($root->getElementsByTagName('table')) as $table) {
            if (! $table->hasAttribute('style')) {
                $table->setAttribute('style', LessonBlocks::TABLE_STYLE);
            }

            $hasHeader = $table->getElementsByTagName('th')->length > 0;
            $firstRow = $table->getElementsByTagName('tr')->item(0);

            foreach (iterator_to_array($table->getElementsByTagName('tr')) as $row) {
                foreach (iterator_to_array($row->childNodes) as $cell) {
                    if (! $cell instanceof DOMElement || $cell->hasAttribute('style')) {
                        continue;
                    }
                    $tag = strtolower($cell->tagName);
                    $isHeader = $tag === 'th' || (! $hasHeader && $row === $firstRow);
                    if ($tag === 'th' || $tag === 'td') {
                        $cell->setAttribute('style', $isHeader ? LessonBlocks::TH_STYLE : LessonBlocks::TD_STYLE);
                    }
                }
            }
            $this->stats['tables']++;
        }
    }
}
