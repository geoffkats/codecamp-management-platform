<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Question text is plain text typed or imported by teachers. It often carries code pasted without
 * any markup, ``` fenced code, or Scratch scripts in scratchblocks syntax. This splits it into
 * segments the question-text component can render: prose, code panels and Scratch blocks.
 * Raw "<tags>" are always shown literally; questions about HTML mention them a lot.
 */
class QuestionText
{
    private const SCRATCH_LINE = '/^(when (green )?flag clicked|when this sprite clicked|when (\[.*\]|\w+) key pressed|when i receive|when backdrop switches|when i start as a clone|forever$|repeat( until)?\b|if\s*<.*>\s*then$|if .* then$|else$|end$|move \(?-?\d|turn (cw|ccw|right|left|↻|↺)?\s*\(?-?\d|go to\b|glide\b|point (in|towards)\b|change (x|y|size|color|\[|\()|set (x|y|size|rotation|\[|\()|say\b|think\b|switch (costume|backdrop)\b|next (costume|backdrop)$|show$|hide$|play sound\b|start sound\b|stop (all|this script|other scripts)|wait \(?\d|wait until\b|broadcast\b|ask \[|pen (up|down)$|erase all$|stamp$|create clone\b|delete this clone$|go to (front|back)|bounce\b|add .* to \[|delete .* of \[)/i';

    private const CODE_LINE = '/^(\s{2,}|\t)\S'
        .'|^\s*(class|def|import|from|print|return|for|while|if|elif|else|try|except|finally|with|lambda|let|const|var|function|public|private|static|void|int|float|double|string|bool|#include|using|echo|SELECT|INSERT|UPDATE|DELETE|CREATE|console\.|document\.|System\.out)\b'
        .'|[{};]\s*$'
        .'|^\s*<\/?[a-z][a-z0-9]*(\s[^>]*)?\/?>'
        .'|^\s*[A-Za-z_][\w.\[\]"\']*\s*[+\-*\/%]?=(?!=)\s*\S'
        .'|^\s*[A-Za-z_][\w.]*\(.*\)\s*;?\s*$'
        .'|^\s*#\s*\S'
        .'|^\s*\/\/'
        .'/';

    private const LANGUAGE_LABELS = [
        'python' => 'Python',
        'javascript' => 'JavaScript',
        'html' => 'HTML',
        'css' => 'CSS',
        'sql' => 'SQL',
        'java' => 'Java',
        'cpp' => 'C / C++',
    ];

    /**
     * @return array<int, array{type: 'text'|'code'|'scratch', content: string, language?: ?string, label?: string}>
     */
    public static function segments(?string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
        if (trim($text) === '') {
            return [];
        }

        $segments = [];
        $offset = 0;
        preg_match_all('/```[ \t]*([\w+#-]*)[ \t]*\n?(.*?)```/s', $text, $fences, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($fences as $fence) {
            [$whole, $start] = $fence[0];
            array_push($segments, ...self::detect(substr($text, $offset, $start - $offset)));

            $hint = strtolower($fence[1][0]);
            $body = rtrim($fence[2][0], "\n");
            $segments[] = in_array($hint, ['scratch', 'blocks', 'scratchblocks'], true)
                ? ['type' => 'scratch', 'content' => self::scratch($body)]
                : self::code($body, $hint);
            $offset = $start + strlen($whole);
        }

        array_push($segments, ...self::detect(substr($text, $offset)));

        return array_values(array_filter($segments, fn ($s) => trim($s['content']) !== ''));
    }

    public static function hasRichParts(?string $text): bool
    {
        return collect(self::segments($text))->contains(fn ($s) => $s['type'] !== 'text');
    }

    /**
     * Escapes prose and turns `inline code` into <code>.
     */
    public static function inline(string $text): string
    {
        $escaped = e(trim($text, "\n"));

        return preg_replace(
            '/`([^`\n]+)`/',
            '<code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-[0.9em] text-rose-600 dark:bg-gray-800 dark:text-rose-300">$1</code>',
            $escaped
        ) ?? $escaped;
    }

    /**
     * One-line summary for lists: the prose with code collapsed, plus what kinds of content follow.
     *
     * @return array{text: string, kinds: array<int, string>}
     */
    public static function preview(?string $text, int $limit = 160): array
    {
        $segments = self::segments($text);
        $prose = collect($segments)->where('type', 'text')->pluck('content')->implode(' ');
        if (trim($prose) === '') {
            $prose = (string) (collect($segments)->first()['content'] ?? '');
        }

        $kinds = collect($segments)->reject(fn ($s) => $s['type'] === 'text')
            ->map(fn ($s) => $s['type'] === 'scratch' ? 'Scratch blocks' : ($s['label'] ?? 'Code'))
            ->unique()->values()->all();

        return [
            'text' => Str::limit(preg_replace('/\s+/', ' ', trim($prose)), $limit),
            'kinds' => $kinds,
        ];
    }

    /**
     * Finds unmarked code or Scratch runs (two or more lines) inside plain text.
     */
    private static function detect(string $chunk): array
    {
        $lines = explode("\n", $chunk);
        $kinds = array_map(fn ($line) => self::classify($line), $lines);
        $segments = [];
        $prose = [];
        $i = 0;
        $count = count($lines);

        while ($i < $count) {
            if (! in_array($kinds[$i], ['code', 'scratch'], true)) {
                $prose[] = $lines[$i];
                $i++;

                continue;
            }

            $end = $i;
            $j = $i;
            while ($j < $count && in_array($kinds[$j], ['code', 'scratch', 'blank'], true)) {
                if ($kinds[$j] !== 'blank') {
                    $end = $j;
                }
                $j++;
            }

            $block = array_slice($lines, $i, $end - $i + 1);
            $blockKinds = array_slice($kinds, $i, $end - $i + 1);
            $nonBlank = count(array_filter($blockKinds, fn ($k) => $k !== 'blank'));

            if ($nonBlank < 2) {
                $prose[] = $lines[$i];
                $i++;

                continue;
            }

            $hint = '';
            while ($prose !== [] && trim(end($prose)) === '') {
                array_pop($prose);
            }
            if ($prose !== [] && self::normaliseLanguage(strtolower(trim(end($prose)))) !== null) {
                $hint = strtolower(trim(array_pop($prose)));
            }

            if (preg_match('/^(\s*[A-Z][^{]*?:)\s*(\S.*)$/', $block[0], $split) && str_contains(trim($split[1]), ' ')) {
                $prose[] = $split[1];
                $block[0] = $split[2];
            }

            if ($prose !== []) {
                $segments[] = ['type' => 'text', 'content' => implode("\n", $prose)];
                $prose = [];
            }

            $scratchLines = array_filter($block, fn ($line, $k) => $blockKinds[$k] === 'scratch'
                && ! preg_match('/^\s*(end|else)\s*$/i', $line), ARRAY_FILTER_USE_BOTH);
            $isScratch = count($scratchLines) > 0
                && count(array_filter($blockKinds, fn ($k) => $k === 'scratch')) * 2 >= $nonBlank;

            $body = rtrim(implode("\n", $block));
            $segments[] = $isScratch ? ['type' => 'scratch', 'content' => self::scratch($body)] : self::code($body, $hint);
            $i = $end + 1;
        }

        if ($prose !== []) {
            $segments[] = ['type' => 'text', 'content' => implode("\n", $prose)];
        }

        return $segments;
    }

    private static function classify(string $line): string
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return 'blank';
        }

        if (preg_match(self::SCRATCH_LINE, $trimmed) && ! str_ends_with($trimmed, ':') && ! preg_match('/\w\(/', $trimmed)) {
            return 'scratch';
        }

        if (preg_match('/[?]\s*$/', $trimmed) && ! preg_match('/^(\s{2,}|\t)/', $line)) {
            return 'text';
        }

        return preg_match(self::CODE_LINE, $line) ? 'code' : 'text';
    }

    private static function code(string $body, string $hint = ''): array
    {
        $body = self::dedent($body);
        $language = self::normaliseLanguage($hint) ?? self::guessLanguage($body);

        return [
            'type' => 'code',
            'content' => $body,
            'language' => $language,
            'label' => self::LANGUAGE_LABELS[$language] ?? 'Code',
        ];
    }

    private static function normaliseLanguage(string $hint): ?string
    {
        return match ($hint) {
            'py', 'python', 'python3' => 'python',
            'js', 'javascript', 'node' => 'javascript',
            'html', 'xml', 'htm' => 'html',
            'css' => 'css',
            'sql', 'mysql' => 'sql',
            'java' => 'java',
            'c', 'cpp', 'c++' => 'cpp',
            default => null,
        };
    }

    private static function guessLanguage(string $code): ?string
    {
        return match (true) {
            (bool) preg_match('/^\s*<\/?[a-z][a-z0-9]*[\s>\/]/mi', $code) => 'html',
            (bool) preg_match('/^\s*(SELECT|INSERT|UPDATE|DELETE|CREATE)\s/mi', $code) => 'sql',
            (bool) preg_match('/\b(def|elif|self|print\(|import |range\()|:\s*$/m', $code) => 'python',
            (bool) preg_match('/console\.|document\.|function\s*\w*\(|=>|\b(let|const|var)\s/', $code) => 'javascript',
            (bool) preg_match('/#include|std::|printf\(|int main/', $code) => 'cpp',
            (bool) preg_match('/System\.out|public static void/', $code) => 'java',
            (bool) preg_match('/^[\s.#\w-]+\{[^}]*:[^}]*\}/m', $code) => 'css',
            default => null,
        };
    }

    /**
     * scratchblocks only recognises number inputs written as (n) and treats a blank line as the start
     * of a new script, so "repeat 5" or "turn 15 degrees" typed by hand would render as unknown blocks.
     */
    private static function scratch(string $body): string
    {
        $lines = array_filter(explode("\n", self::dedent($body)), fn ($line) => trim($line) !== '');

        return implode("\n", array_map(function ($line) {
            $line = preg_replace('/(?<=^|\s)(-?\d+(?:\.\d+)?)(?=\s|$)/', '($1)', $line);

            return preg_replace('/^(\s*)turn\s+(?=\()/i', '$1turn cw ', $line);
        }, $lines));
    }

    private static function dedent(string $code): string
    {
        $lines = explode("\n", $code);
        $indents = array_map(
            fn ($line) => strlen($line) - strlen(ltrim($line, " \t")),
            array_filter($lines, fn ($line) => trim($line) !== '')
        );
        $min = $indents === [] ? 0 : min($indents);

        return implode("\n", array_map(fn ($line) => substr($line, $min), $lines));
    }
}
