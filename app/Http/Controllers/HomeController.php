<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Learning paths, matched against course titles and categories in order.
     * The first path whose keywords match a course claims it.
     */
    protected const PATHS = [
        'python' => [
            'title' => 'Python',
            'icon' => 'python',
            'description' => 'Learn programming by solving real problems and building useful projects.',
            'keywords' => ['python'],
        ],
        'robotics' => [
            'title' => 'Robotics',
            'icon' => 'robot',
            'description' => 'Turn code into physical things that move, sense and respond.',
            'keywords' => ['robot', 'arduino', 'mbot', 'acebott', 'stem'],
        ],
        'mobile' => [
            'title' => 'Mobile Apps',
            'icon' => 'phone',
            'description' => 'Design screens, add logic and build apps people can use on their phones.',
            'keywords' => ['mobile', 'android', 'ios', 'app development'],
        ],
        'web' => [
            'title' => 'Web Development',
            'icon' => 'code',
            'description' => 'Build websites and interactive experiences from the ground up.',
            'keywords' => ['web', 'html', 'css', 'javascript'],
        ],
        'ai' => [
            'title' => 'AI & Data',
            'icon' => 'spark',
            'description' => 'Explore how machines learn from data and build your first smart projects.',
            'keywords' => ['artificial intelligence', 'machine learning', 'data science'],
        ],
        'digital' => [
            'title' => 'Digital Skills',
            'icon' => 'monitor',
            'description' => 'Get confident with computers, documents, spreadsheets and staying safe online.',
            'keywords' => ['icdl', 'computer', 'office', 'document', 'online', 'presentation', 'spreadsheet'],
        ],
        'coding' => [
            'title' => 'Coding',
            'icon' => 'blocks',
            'description' => 'Start with visual, block-based coding and learn to think like a programmer.',
            'keywords' => ['scratch', 'coding', 'block', 'programming'],
        ],
    ];

    /** What learners make on each path. Shown until real projects are configured. */
    protected const PATH_PROJECTS = [
        'coding' => ['category' => 'Games', 'title' => 'Interactive games & animations', 'description' => 'Design characters, add rules and scoring, then share a playable game.'],
        'web' => ['category' => 'Websites', 'title' => 'Responsive websites', 'description' => 'Plan, code and style websites that work on phones and laptops.'],
        'python' => ['category' => 'Programs', 'title' => 'Python programs', 'description' => 'Write programs that take input, make decisions and solve everyday problems.'],
        'robotics' => ['category' => 'Robotics', 'title' => 'Robots that sense & move', 'description' => 'Build a robot, wire its sensors and program how it reacts to the world.'],
        'mobile' => ['category' => 'Apps', 'title' => 'Mobile apps', 'description' => 'Design app screens and connect them with logic to make a working app.'],
        'ai' => ['category' => 'AI', 'title' => 'Smart projects', 'description' => 'Train simple models and use them inside your own creative projects.'],
        'digital' => ['category' => 'Digital skills', 'title' => 'Documents & presentations', 'description' => 'Produce polished documents, spreadsheets and presentations with confidence.'],
    ];

    public function __invoke(): View
    {
        $data = Cache::remember('home.page-data.v2', now()->addMinutes(10), fn () => $this->buildData());

        return view('welcome', $data + [
            'projectsUrl' => config('homepage.projects_url'),
            'brand' => $this->brand(),
            'programs' => $this->programs(),
            'testimonials' => collect(config('homepage.testimonials', []))->filter(fn ($t) => filled($t['quote'] ?? null) && filled($t['name'] ?? null))->values()->all(),
        ]);
    }

    protected function buildData(): array
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->withCount('enrollments')
            ->orderByDesc('is_featured')
            ->orderByDesc('enrollments_count')
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course) => $this->presentCourse($course));

        $paths = collect(self::PATHS)
            ->map(function (array $path, string $key) use ($courses) {
                $pathCourses = $courses->where('path', $key);

                return [
                    'key' => $key,
                    'title' => $path['title'],
                    'icon' => $path['icon'],
                    'description' => $path['description'],
                    'count' => $pathCourses->count(),
                    'example' => $pathCourses->first(),
                ];
            })
            ->filter(fn ($path) => $path['count'] > 0)
            ->sortBy(fn ($path) => array_search($path['key'], ['coding', 'web', 'python', 'robotics', 'mobile', 'ai', 'digital'], true))
            ->values();

        return [
            'stats' => $this->stats($courses->count()),
            'paths' => $paths->all(),
            'courses' => $courses->take(24)->values()->all(),
            'projects' => $this->projects($paths),
            'beginnerCourses' => $courses->where('level', 'Beginner')->count(),
        ];
    }

    protected function presentCourse(Course $course): array
    {
        $title = trim($course->title);
        if ($title === mb_strtoupper($title)) {
            $title = Str::title(mb_strtolower($title));
        }

        $summary = trim((string) ($course->short_description ?: strip_tags((string) $course->description)));

        return [
            'id' => $course->id,
            'title' => $title,
            'summary' => Str::limit(preg_replace('/\s+/', ' ', $summary), 120),
            'level' => $course->difficulty_level ? Str::ucfirst(Str::lower($course->difficulty_level)) : null,
            'hours' => $course->estimated_duration ? (int) $course->estimated_duration : null,
            'category' => $course->category ? trim($course->category) : null,
            'image' => $course->featured_image ? asset('storage/' . $course->featured_image) : null,
            'enrollment' => $course->enrollment_type,
            'path' => $this->pathFor($course),
            'url' => route('courses.show', $course),
        ];
    }

    protected function pathFor(Course $course): ?string
    {
        $haystack = ' ' . Str::lower($course->title . ' ' . $course->category) . ' ';

        foreach (self::PATHS as $key => $path) {
            foreach ($path['keywords'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return $key;
                }
            }
        }

        return null;
    }

    protected function stats(int $courseCount): array
    {
        $learners = DB::table('users')
            ->whereExists(fn ($q) => $q->selectRaw(1)
                ->from('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->whereColumn('user_roles.user_id', 'users.id')
                ->where('roles.name', 'student'))
            ->count();

        $lessons = DB::table('lessons')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('courses.is_published', true)
            ->whereNull('courses.deleted_at')
            ->where('lessons.is_published', true)
            ->whereNull('lessons.deleted_at')
            ->count();

        $projects = DB::table('assignment_submissions')->whereNotNull('submitted_at')->count();

        return array_values(array_filter([
            ['value' => $learners, 'display' => config('homepage.learners_display'), 'label' => 'Registered learners'],
            ['value' => $courseCount, 'label' => 'Published courses'],
            ['value' => $lessons, 'label' => 'Lessons to explore'],
            ['value' => $projects, 'label' => 'Projects submitted'],
        ], fn ($stat) => $stat['value'] > 0));
    }

    protected function projects($paths): array
    {
        $configured = collect(config('homepage.projects', []))
            ->filter(fn ($p) => filled($p['title'] ?? null))
            ->values();

        if ($configured->isNotEmpty()) {
            return $configured->all();
        }

        return $paths
            ->filter(fn ($path) => isset(self::PATH_PROJECTS[$path['key']]))
            ->map(fn ($path) => self::PATH_PROJECTS[$path['key']] + [
                'path' => $path['key'],
                'icon' => $path['icon'],
                'url' => config('homepage.projects_url'),
                'image' => null,
            ])
            ->values()
            ->all();
    }

    protected function brand(): array
    {
        return [
            'name' => SystemSetting::get('app_name') ?: config('app.name', 'Code Academy Uganda'),
            'logo' => SystemSetting::get('logo'),
            'logoDark' => SystemSetting::get('logo_dark'),
            'email' => SystemSetting::get('contact_email'),
            'phone' => SystemSetting::get('contact_phone'),
            'address' => SystemSetting::get('contact_address'),
        ];
    }

    protected function programs(): array
    {
        $programs = [
            [
                'title' => 'Code Camps',
                'audience' => 'Students & individuals',
                'description' => 'Hands-on holiday camps where learners build real projects with instructors.',
                'url' => config('homepage.codecamp_register_url'),
                'cta' => 'Register interest',
            ],
            [
                'title' => 'Code Clubs',
                'audience' => 'For schools',
                'description' => 'Weekly coding clubs hosted at your school, running alongside the term.',
                'route' => 'registration.codeclub',
                'cta' => 'Bring a club to your school',
                'enabled' => (bool) config('features.code_club', false),
            ],
            [
                'title' => 'School Partnerships',
                'audience' => 'For schools',
                'description' => 'Bring structured technology learning to your learners with Code Academy.',
                'route' => 'registration.school',
                'cta' => 'Register your school',
            ],
            [
                'title' => 'ICDL Certification',
                'audience' => 'Digital skills',
                'description' => 'Prepare for and register for ICDL digital skills testing.',
                'route' => 'registration.icdl',
                'cta' => 'Register for ICDL',
            ],
        ];

        return array_values(array_filter(
            array_map(fn ($p) => $p + ['url' => isset($p['route']) && Route::has($p['route']) ? route($p['route']) : null], $programs),
            fn ($p) => ($p['enabled'] ?? true) && filled($p['url'])
        ));
    }
}
