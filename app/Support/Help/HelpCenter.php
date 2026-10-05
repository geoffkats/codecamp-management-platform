<?php

namespace App\Support\Help;

use App\Models\User;
use App\View\Components\Navigation\Sidebar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * The staff manual: Markdown guides in resources/docs/manual/guides and release notes in
 * resources/docs/manual/whats-new, each with front matter naming the roles it is written for.
 */
class HelpCenter
{
    /** Sidebar role keys mapped to the audience names used in front matter. */
    private const AUDIENCES = [
        'admin' => 'admin',
        'supervisor' => 'supervisor',
        'codecamp_teacher' => 'trainer',
        'codeclub_facilitator' => 'facilitator',
        'ict_teacher' => 'ict_teacher',
        'operations_manager' => 'operations',
    ];

    public const AUDIENCE_LABELS = [
        'admin' => 'Admins',
        'supervisor' => 'Supervisors',
        'trainer' => 'Trainers',
        'facilitator' => 'Club facilitators',
        'ict_teacher' => 'ICT teachers',
        'operations' => 'Operations',
    ];

    /** Stands in for the image URL in cached HTML, so the cache never pins one host. */
    private const IMAGE_BASE = '/__manual_images__';

    public const CATEGORIES = [
        'Getting started',
        'Teaching',
        'Assessments',
        'Students & progress',
        'Administration',
    ];

    /**
     * @return array<int, string>
     */
    public function audiencesFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $keys = Sidebar::roleKeysFor($user);
        if (in_array('admin', $keys, true)) {
            return array_values(self::AUDIENCES);
        }

        return array_values(array_unique(array_filter(array_map(fn ($key) => self::AUDIENCES[$key] ?? null, $keys))));
    }

    public function canSee(?User $user): bool
    {
        return $this->audiencesFor($user) !== [];
    }

    public function guides(?User $user): Collection
    {
        return $this->visible($this->load()['guides'], $user)
            ->sortBy([['categoryOrder', 'asc'], ['order', 'asc'], ['title', 'asc']])
            ->values();
    }

    public function updates(?User $user): Collection
    {
        return $this->visible($this->load()['updates'], $user)
            ->sortBy([['date', 'desc'], ['order', 'asc'], ['slug', 'asc']])
            ->values();
    }

    public function guide(?User $user, string $slug): ?array
    {
        return $this->guides($user)->firstWhere('slug', $slug);
    }

    /**
     * Identifies the newest update this user can see, for the "new" dot on the help tab.
     */
    public function latestUpdateKey(?User $user): ?string
    {
        $latest = $this->updates($user)->first();

        return $latest ? $latest['date'].'/'.$latest['slug'] : null;
    }

    private function visible(Collection $items, ?User $user): Collection
    {
        $audiences = $this->audiencesFor($user);

        return $items->filter(fn ($item) => array_intersect($item['audience'], $audiences) !== []);
    }

    /**
     * @return array{guides: Collection, updates: Collection}
     */
    private function load(): array
    {
        $files = [
            'guides' => glob(resource_path('docs/manual/guides/*.md')) ?: [],
            'updates' => glob(resource_path('docs/manual/whats-new/*.md')) ?: [],
        ];
        $signature = md5(collect($files)->flatten()->map(fn ($f) => $f.filemtime($f))->implode('|'));

        $parsed = Cache::rememberForever("help-center:v2:{$signature}", fn () => [
            'guides' => array_map(fn ($f) => $this->parse($f, 'guide'), $files['guides']),
            'updates' => array_map(fn ($f) => $this->parse($f, 'update'), $files['updates']),
        ]);

        $base = asset('docs/manual');
        $resolve = fn (array $item) => array_merge($item, [
            'html' => str_replace(self::IMAGE_BASE, $base, $item['html']),
            'image' => isset($item['image']) ? str_replace(self::IMAGE_BASE, $base, $item['image']) : null,
        ]);

        return [
            'guides' => collect($parsed['guides'])->map($resolve),
            'updates' => collect($parsed['updates'])->map($resolve),
        ];
    }

    private function parse(string $file, string $kind): array
    {
        $markdown = str_replace('](manual:', ']('.self::IMAGE_BASE.'/', (string) file_get_contents($file));
        $result = $this->converter()->convert($markdown);
        $meta = $result instanceof RenderedContentWithFrontMatter ? (array) $result->getFrontMatter() : [];
        $html = (string) $result->getContent();
        $slug = (string) ($meta['slug'] ?? Str::of(basename($file, '.md'))->replaceMatches('/^\d{4}-\d{2}-\d{2}-/', ''));
        $audience = array_values(array_map('strval', (array) ($meta['audience'] ?? array_values(self::AUDIENCES))));

        $item = [
            'slug' => $slug,
            'title' => (string) ($meta['title'] ?? Str::headline($slug)),
            'summary' => (string) ($meta['summary'] ?? ''),
            'audience' => $audience,
            'audienceLabels' => array_values(array_intersect_key(self::AUDIENCE_LABELS, array_flip($audience))),
            'html' => $html,
            'search' => Str::lower(strip_tags(($meta['title'] ?? '').' '.($meta['summary'] ?? '').' '.$html)),
        ];

        if ($kind === 'guide') {
            $category = (string) ($meta['category'] ?? 'Getting started');

            return $item + [
                'category' => $category,
                'categoryOrder' => array_search($category, self::CATEGORIES, true) === false ? 99 : array_search($category, self::CATEGORIES, true),
                'icon' => (string) ($meta['icon'] ?? 'book-open'),
                'order' => (int) ($meta['order'] ?? 50),
                'minutes' => max(1, (int) ceil(str_word_count(strip_tags($html)) / 200)),
            ];
        }

        $date = $meta['date'] ?? substr(basename($file), 0, 10);

        return $item + [
            'date' => is_int($date) ? date('Y-m-d', $date) : (string) $date,
            'order' => (int) ($meta['order'] ?? 50),
            'image' => isset($meta['image']) ? self::IMAGE_BASE.'/'.ltrim((string) $meta['image'], '/') : null,
            'tags' => array_values(array_map('strval', (array) ($meta['tags'] ?? []))),
            'guide' => isset($meta['guide']) ? (string) $meta['guide'] : null,
        ];
    }

    private function converter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new FrontMatterExtension);

        return new MarkdownConverter($environment);
    }
}
