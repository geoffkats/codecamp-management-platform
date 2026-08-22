<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\StudentLessonProgress;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CertificateDataService
{
    /** @var list<string> */
    public const SIGNATORY_KEYS = ['default', 'ict', 'codecamp'];

    public function resolve(Certificate $certificate): array
    {
        $user = $certificate->user;
        $profile = $user?->studentProfile;
        $course = $certificate->course;

        $modules = data_get($certificate->completion_data, 'modules');
        if (empty($modules)) {
            $modules = $this->buildModulesForUser($user, $certificate->course_id);
        }

        $issuedAt = $certificate->issued_at ?? $certificate->created_at ?? now();

        return $this->formatPayload(
            candidateName: $profile?->full_name ?? $user?->name ?? 'Student',
            candidateNo: $profile?->student_id ?? $certificate->certificate_number ?? ('CERT-' . $certificate->id),
            signatureDate: $issuedAt,
            modules: $modules,
            context: [
                'course' => $course,
                'student' => $user,
                'signatory_key' => data_get($certificate->completion_data, 'signatory.profile'),
                'custom_signatory' => data_get($certificate->completion_data, 'signatory.name_line'),
            ],
        );
    }

    public function resolveForUser(User $user, ?Course $course = null, array $context = []): array
    {
        $profile = $user->studentProfile;
        $modules = $this->buildModulesForUser($user, $course?->id);

        return $this->formatPayload(
            candidateName: $profile?->full_name ?? $user->name,
            candidateNo: $profile?->student_id ?? ('STU-' . $user->id),
            signatureDate: now(),
            modules: $modules,
            context: array_merge($context, [
                'course' => $course,
                'student' => $user,
            ]),
        );
    }

    public function buildModulesForUser(?User $user, ?int $courseId = null): array
    {
        if (! $user) {
            return [];
        }

        if ($courseId) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->whereNotNull('completed_at')
                ->with('course.modules')
                ->first();

            if ($enrollment?->course) {
                $moduleRows = $this->completedCourseModuleRows($user, $enrollment->course, $enrollment->completed_at);
                if ($moduleRows !== []) {
                    return $moduleRows;
                }

                return [[
                    'name'    => $enrollment->course->title,
                    'version' => config('certificate.default_module_version', '1.0'),
                    'date'    => $enrollment->completed_at->format('Y-m-d'),
                ]];
            }
        }

        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('course.modules')
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->orderBy('completed_at')
            ->get();

        $modules = [];

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->course) {
                continue;
            }

            $moduleRows = $this->completedCourseModuleRows($user, $enrollment->course, $enrollment->completed_at);
            if ($moduleRows !== []) {
                $modules = array_merge($modules, $moduleRows);
                continue;
            }

            $modules[] = [
                'name'    => $enrollment->course->title,
                'version' => config('certificate.default_module_version', '1.0'),
                'date'    => $enrollment->completed_at->format('Y-m-d'),
            ];
        }

        return $modules;
    }

    private function completedCourseModuleRows(User $user, Course $course, Carbon $fallbackDate): array
    {
        $course->loadMissing(['modules.lessons']);

        $rows = [];

        foreach ($course->modules->sortBy('order_index') as $module) {
            if (! $this->isModuleCompleted($user, $module)) {
                continue;
            }

            $completedAt = $this->moduleCompletionDate($user, $module) ?? $fallbackDate;

            $rows[] = [
                'name'    => $module->title,
                'version' => config('certificate.default_module_version', '1.0'),
                'date'    => $completedAt->format('Y-m-d'),
            ];
        }

        return $rows;
    }

    private function isModuleCompleted(User $user, CourseModule $module): bool
    {
        $lessonIds = $module->lessons()->pluck('id');

        if ($lessonIds->isEmpty()) {
            return false;
        }

        $completedCount = StudentLessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->count();

        return $completedCount >= $lessonIds->count();
    }

    private function moduleCompletionDate(User $user, CourseModule $module): ?Carbon
    {
        $lessonIds = $module->lessons()->pluck('id');

        if ($lessonIds->isEmpty()) {
            return null;
        }

        $lastCompleted = StudentLessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->max('completed_at');

        return $lastCompleted ? Carbon::parse($lastCompleted) : null;
    }

    public function formatPayload(string $candidateName, string $candidateNo, Carbon $signatureDate, array $modules, array $context = []): array
    {
        $modules = collect($modules)->map(function ($row) {
            $date = $row['date'] ?? now()->format('Y-m-d');

            return [
                'name'    => $row['name'] ?? '',
                'version' => $row['version'] ?? config('certificate.default_module_version', '1.0'),
                'date'    => $date,
                'date_formatted' => Carbon::parse($date)->format('jS M Y'),
            ];
        })->filter(fn ($row) => $row['name'] !== '')->values()->all();

        if ($modules === []) {
            $modules[] = [
                'name'    => 'Course Completion',
                'version' => config('certificate.default_module_version', '1.0'),
                'date'    => $signatureDate->format('Y-m-d'),
                'date_formatted' => $signatureDate->format('jS M Y'),
            ];
        }

        $course = $context['course'] ?? null;
        $student = $context['student'] ?? null;
        $signatoryKey = $this->resolveSignatoryKey(
            $course,
            $student,
            $context['signatory_key'] ?? null,
        );
        $signatoryLine = ! empty($context['custom_signatory'])
            ? $context['custom_signatory']
            : $this->signatoryLine($signatoryKey);

        $showSignature = $this->showSignature();
        $signatureImage = $showSignature ? $this->signatureImageDataUri($signatoryKey) : null;
        $layout = $this->layoutPositions($context['layout_overrides'] ?? []);

        return [
            'candidateName'     => strtoupper($candidateName),
            'candidateNo'       => $candidateNo,
            'signatureDate'     => $signatureDate->format('Y-m-d'),
            'signatureDateFormatted' => $signatureDate->format('jS M Y'),
            'modules'           => $modules,
            'signatoryKey'      => $signatoryKey,
            'executiveDirector' => $signatoryLine,
            'showSignature'     => $showSignature && $signatureImage !== null,
            'showSignatoryText' => $this->shouldOverlaySignatoryText(),
            'signatureImage'    => $signatureImage,
            'layout'            => $layout,
            'brandColor'        => $this->setting('certificate_brand_color', 'certificate.brand_color', '#1546c0'),
            'labelColor'        => $this->setting('certificate_label_color', 'certificate.label_color', '#2d7fd4'),
            'borderWidth'       => $this->setting('certificate_border_width', 'certificate.border_width_mm', 5),
            'useBackground'     => $this->useBackground(),
            'backgroundImage'   => $this->backgroundImageDataUri(),
            'audit'             => $context['audit'] ?? null,
        ];
    }

    /**
     * @return array<string, array{label: string, has_signature: bool, signatory_line: string}>
     */
    public function signatoryProfileOptions(): array
    {
        $options = [];

        foreach (self::SIGNATORY_KEYS as $key) {
            $options[$key] = [
                'label' => config("certificate.signatory_profiles.{$key}.label", ucfirst($key)),
                'has_signature' => $this->signatureImagePath($key) !== null,
                'signatory_line' => $this->signatoryLine($key),
            ];
        }

        return $options;
    }

    public function resolveSignatoryKey(?Course $course, ?User $student, ?string $override = null): string
    {
        if ($override && $override !== 'auto' && in_array($override, self::SIGNATORY_KEYS, true)) {
            return $override;
        }

        $program = strtolower((string) (
            $student?->studentProfile?->program_type
            ?? $student?->studentProfile?->student_category
            ?? ''
        ));

        if ($program === 'ict') {
            return 'ict';
        }

        if ($program === 'codecamp') {
            return 'codecamp';
        }

        $category = strtolower((string) ($course?->category ?? ''));

        if (str_contains($category, 'ict')) {
            return 'ict';
        }

        if (str_contains($category, 'camp') || str_contains($category, 'code')) {
            return 'codecamp';
        }

        return 'default';
    }

    public function signatoryLine(string $key): string
    {
        $settingKey = $key === 'default'
            ? 'certificate_executive_director'
            : "certificate_executive_director_{$key}";

        $fallback = $key === 'default'
            ? config('certificate.executive_director')
            : $this->signatoryLine('default');

        return (string) $this->setting($settingKey, 'certificate.executive_director', $fallback);
    }

    public function showSignature(): bool
    {
        $value = SystemSetting::get('certificate_show_signature');

        if ($value !== null && $value !== '') {
            return in_array($value, ['1', 1, true, 'true'], true);
        }

        return true;
    }

    public function showSignatoryText(): bool
    {
        $value = SystemSetting::get('certificate_show_signatory_text');

        if ($value !== null && $value !== '') {
            return in_array($value, ['1', 1, true, 'true'], true);
        }

        return false;
    }

    /** Only overlay signatory text when using custom artwork (default ict_bg already has it printed). */
    public function shouldOverlaySignatoryText(): bool
    {
        if (! $this->showSignatoryText()) {
            return false;
        }

        if ($this->useBackground() && ! $this->hasCustomBackgroundArtwork()) {
            return false;
        }

        return true;
    }

    public function hasCustomBackgroundArtwork(): bool
    {
        return (bool) SystemSetting::get('certificate_background');
    }

    /**
     * @return array<string, array<string, float|int>>
     */
    public function defaultLayoutPositions(): array
    {
        return config('certificate.layout', []);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, array<string, float|int>>
     */
    public function layoutPositions(array $overrides = []): array
    {
        $layout = $this->defaultLayoutPositions();

        $map = [
            'signature' => [
                'bottom_mm' => 'certificate_sig_bottom_mm',
                'left_mm' => 'certificate_sig_left_mm',
                'width_mm' => 'certificate_sig_width_mm',
                'max_height_mm' => 'certificate_sig_max_height_mm',
            ],
            'signatory' => [
                'top_mm' => 'certificate_signatory_top_mm',
                'left_mm' => 'certificate_signatory_left_mm',
                'width_mm' => 'certificate_signatory_width_mm',
                'font_size_pt' => 'certificate_signatory_font_pt',
            ],
            'date' => [
                'top_mm' => 'certificate_date_top_mm',
                'left_mm' => 'certificate_date_left_mm',
                'width_mm' => 'certificate_date_width_mm',
                'font_size_pt' => 'certificate_date_font_pt',
            ],
        ];

        foreach ($map as $section => $fields) {
            foreach ($fields as $field => $settingKey) {
                $fromSetting = SystemSetting::get($settingKey);
                if ($fromSetting !== null && $fromSetting !== '') {
                    $layout[$section][$field] = is_numeric($fromSetting) ? (float) $fromSetting : $fromSetting;
                }
            }
        }

        foreach ($overrides as $section => $fields) {
            if (! is_array($fields)) {
                continue;
            }
            foreach ($fields as $field => $value) {
                if ($value !== null && $value !== '') {
                    $layout[$section][$field] = is_numeric($value) ? (float) $value : $value;
                }
            }
        }

        if (isset($layout['signature']['bottom_mm'])) {
            unset($layout['signature']['top_mm'], $layout['signature']['height_mm']);
        }

        return $layout;
    }

    /** @return array<string, string> */
    public function defaultLayoutSettingKeys(): array
    {
        $defaults = $this->defaultLayoutPositions();

        return [
            'certificate_sig_bottom_mm' => (string) ($defaults['signature']['bottom_mm'] ?? 40.5),
            'certificate_sig_left_mm' => (string) ($defaults['signature']['left_mm'] ?? 28),
            'certificate_sig_width_mm' => (string) ($defaults['signature']['width_mm'] ?? 55),
            'certificate_sig_max_height_mm' => (string) ($defaults['signature']['max_height_mm'] ?? 9),
            'certificate_signatory_top_mm' => (string) ($defaults['signatory']['top_mm'] ?? 258),
            'certificate_signatory_left_mm' => (string) ($defaults['signatory']['left_mm'] ?? 28),
            'certificate_signatory_width_mm' => (string) ($defaults['signatory']['width_mm'] ?? 95),
            'certificate_signatory_font_pt' => (string) ($defaults['signatory']['font_size_pt'] ?? 8),
            'certificate_date_top_mm' => (string) ($defaults['date']['top_mm'] ?? 250.5),
            'certificate_date_left_mm' => (string) ($defaults['date']['left_mm'] ?? 148.5),
            'certificate_date_width_mm' => (string) ($defaults['date']['width_mm'] ?? 31),
            'certificate_date_font_pt' => (string) ($defaults['date']['font_size_pt'] ?? 10),
        ];
    }

    public function signatureImagePath(string $key = 'default'): ?string
    {
        $settingKey = $key === 'default'
            ? 'certificate_signature'
            : "certificate_signature_{$key}";

        $uploaded = SystemSetting::get($settingKey);

        if (! $uploaded && $key !== 'default') {
            return $this->signatureImagePath('default');
        }

        if ($uploaded) {
            $path = storage_path('app/public/' . ltrim($uploaded, '/'));
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function signatureImageDataUri(string $key = 'default'): ?string
    {
        $path = $this->signatureImagePath($key);

        if (! $path) {
            return null;
        }

        return $this->fileToDataUri($path, 'certificate:sig:' . md5($path));
    }

    private function fileToDataUri(string $path, string $cachePrefix): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        return Cache::remember($cachePrefix . ':' . filemtime($path), 3600, function () use ($path) {
            $data = @file_get_contents($path);
            if ($data === false) {
                return null;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/png',
            };

            return 'data:' . $mime . ';base64,' . base64_encode($data);
        });
    }

    /**
     * Resolve a certificate option from the database (SystemSetting) first,
     * falling back to the config default. Lets admins override in Settings.
     */
    public function setting(string $settingKey, string $configKey, $default = null)
    {
        $value = SystemSetting::get($settingKey);

        if ($value !== null && $value !== '') {
            return $value;
        }

        return config($configKey, $default);
    }

    public function useBackground(): bool
    {
        $value = SystemSetting::get('certificate_use_background');

        if ($value !== null && $value !== '') {
            return in_array($value, ['1', 1, true, 'true'], true);
        }

        return (bool) config('certificate.use_background_image', true);
    }

    public function minProgressPercent(): int
    {
        return (int) $this->setting('certificate_min_progress', 'certificate.min_progress_percent', 80);
    }

    /**
     * Absolute path to the active certificate artwork — an admin-uploaded file
     * (stored on the public disk) if present, otherwise the bundled artwork.
     */
    public function backgroundImagePath(): ?string
    {
        $uploaded = SystemSetting::get('certificate_background');

        if ($uploaded) {
            $candidate = storage_path('app/public/' . ltrim($uploaded, '/'));
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $configPath = config('certificate.background_image');

        return ($configPath && is_file($configPath)) ? $configPath : null;
    }

    /**
     * Reads the active certificate artwork and returns a base64 data URI so it
     * renders reliably in both DomPDF and the browser preview. Null if disabled
     * or missing.
     */
    public function backgroundImageDataUri(): ?string
    {
        if (! $this->useBackground()) {
            return null;
        }

        $path = $this->backgroundImagePath();

        if (! $path || ! is_file($path)) {
            return null;
        }

        return $this->fileToDataUri($path, 'certificate:bg:' . md5($path));
    }

    public function coursesForGenerator(User $staff): Collection
    {
        return Course::query()
            ->select('id', 'title')
            ->whereHas('enrollments')
            ->when(
                ! $staff->isAdmin() && ! $staff->isSupervisor() && ! $staff->isOperationsManager(),
                function ($query) use ($staff) {
                    $query->where(function ($inner) use ($staff) {
                        $inner->where('instructor_id', $staff->id)
                            ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $staff->id));
                    });
                }
            )
            ->orderBy('title')
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function searchStudentsForCertificate(
        int $courseId,
        string $search = '',
        string $filter = 'ready',
        int $limit = 30,
    ): Collection {
        $course = Course::with('modules')->find($courseId);

        if (! $course) {
            return collect();
        }

        $query = CourseEnrollment::query()
            ->where('course_id', $courseId)
            ->with(['user.studentProfile']);

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->whereHas('user', function ($userQuery) use ($term) {
                $userQuery->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('studentProfile', function ($profileQuery) use ($term) {
                        $profileQuery->where('full_name', 'like', $term)
                            ->orWhere('student_id', 'like', $term);
                    });
            });
        }

        return $query
            ->orderByDesc('progress_percentage')
            ->limit($limit * 4)
            ->get()
            ->map(fn (CourseEnrollment $enrollment) => $this->summarizeStudentEligibility($enrollment, $course))
            ->when($filter === 'ready', fn (Collection $rows) => $rows->filter(fn ($row) => $row['is_ready']))
            ->when(
                $filter === 'not_issued',
                fn (Collection $rows) => $rows->filter(fn ($row) => $row['is_ready'] && ! $row['has_certificate'])
            )
            ->take($limit)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function summarizeStudentEligibility(CourseEnrollment $enrollment, ?Course $course = null): array
    {
        $course ??= $enrollment->course;
        $user = $enrollment->user;
        $modules = $this->buildModulesForUser($user, $course?->id);
        $totalModules = $course?->modules?->count() ?? 0;
        $completedModules = count($modules);
        $progress = (float) ($enrollment->progress_percentage ?? 0);
        $minProgress = $this->minProgressPercent();

        $hasCertificate = Certificate::query()
            ->where('user_id', $user->id)
            ->when($course, fn ($q) => $q->where('course_id', $course->id))
            ->exists();

        $isReady = $enrollment->completed_at !== null
            || $completedModules > 0
            || $progress >= $minProgress;

        return [
            'user_id' => $user->id,
            'name' => $user->studentProfile?->full_name ?? $user->name,
            'student_id' => $user->studentProfile?->student_id ?? ('STU-' . $user->id),
            'email' => $user->email,
            'progress' => round($progress, 1),
            'completed_modules' => $completedModules,
            'total_modules' => $totalModules,
            'is_ready' => $isReady,
            'has_certificate' => $hasCertificate,
            'completed_at' => $enrollment->completed_at?->format('Y-m-d'),
        ];
    }

    public function createOrUpdateCertificate(
        User $user,
        Course $course,
        array $modules,
        ?Carbon $issuedAt = null,
        array $meta = [],
    ): Certificate {
        $issuedAt ??= now();
        $profile = $user->studentProfile;
        $issuer = $meta['issuer'] ?? null;
        $signatoryKey = $this->resolveSignatoryKey(
            $course,
            $user,
            $meta['signatory_key'] ?? null,
        );
        $signatoryLine = ! empty($meta['custom_signatory'])
            ? $meta['custom_signatory']
            : $this->signatoryLine($signatoryKey);

        $completionData = [
            'modules' => $modules,
            'generated_at' => now()->toIso8601String(),
            'signatory' => [
                'profile' => $signatoryKey,
                'name_line' => $signatoryLine,
            ],
        ];

        if ($issuer instanceof User) {
            $completionData['issued_by'] = [
                'user_id' => $issuer->id,
                'name' => $issuer->name,
                'email' => $issuer->email,
                'issued_at' => now()->toIso8601String(),
            ];
        }

        $payload = [
            'certificate_number' => $profile?->student_id
                ?? ('CERT-' . strtoupper(substr(md5($user->id . '-' . $course->id), 0, 8))),
            'title' => 'CODE Profile Certificate',
            'description' => 'This certifies that ' . ($profile?->full_name ?? $user->name)
                . ' has successfully completed modules in "' . $course->title . '".',
            'issued_at' => $issuedAt,
            'expires_at' => null,
            'is_verified' => true,
            'completion_data' => $completionData,
        ];

        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            $existing->update($payload);
            $certificate = $existing->fresh();

            app(NotificationService::class)->notifyCertificateIssued($user, $certificate, $course, false);

            return $certificate;
        }

        $certificate = Certificate::create([
            ...$payload,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        app(NotificationService::class)->notifyCertificateIssued($user, $certificate, $course, true);

        return $certificate;
    }

    public function buildPreviewUrl(
        string $candidateName,
        string $candidateNo,
        string $signatureDate,
        array $modules,
        array $context = [],
    ): string {
        $modulesParam = collect($modules)
            ->filter(fn ($row) => ! empty($row['name']))
            ->map(fn ($row) => implode(';', [
                $row['name'],
                $row['version'] ?? config('certificate.default_module_version', '1.0'),
                $row['date'] ?? now()->format('Y-m-d'),
            ]))
            ->implode('|');

        $layout = $context['layout_overrides'] ?? [];
        $layoutParams = [];
        foreach ($layout as $section => $fields) {
            if (! is_array($fields)) {
                continue;
            }
            foreach ($fields as $field => $value) {
                if ($value !== null && $value !== '') {
                    $layoutParams["layout_{$section}_{$field}"] = $value;
                }
            }
        }

        return route('certificates.template-preview', array_filter([
            'name' => $candidateName,
            'no' => $candidateNo,
            'date' => $signatureDate,
            'modules' => $modulesParam ?: null,
            'signatory' => $context['signatory_key'] ?? null,
            'custom_signatory' => $context['custom_signatory'] ?? null,
            ...$layoutParams,
        ]));
    }
}
