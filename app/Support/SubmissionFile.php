<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFile
{
    public const ALLOWED_DIRECTORIES = [
        'assignments',
        'assessments/submissions',
    ];

    /**
     * Store an upload and keep the student's original filename for downloads.
     *
     * @return array{path: string, name: string}
     */
    public static function store(UploadedFile $file, string $directory): array
    {
        $directory = trim($directory, '/');
        $originalName = self::sanitizeDownloadName($file->getClientOriginalName());

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === '' && $originalName !== '') {
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        }

        $storedName = (string) Str::uuid();
        if ($extension !== '') {
            $storedName .= '.'.$extension;
        }

        $path = $file->storeAs($directory, $storedName, 'public');

        return [
            'path' => $path,
            'name' => $originalName !== '' ? $originalName : basename($path),
        ];
    }

    public static function isAllowedPath(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        foreach (self::ALLOWED_DIRECTORIES as $directory) {
            if (str_starts_with($path, $directory.'/')) {
                return true;
            }
        }

        return false;
    }

    public static function sanitizeDownloadName(?string $name, ?string $fallbackPath = null): string
    {
        $name = str_replace(["\0", '/', '\\'], '', (string) $name);
        $name = basename(trim($name));

        if ($name === '' || $name === '.' || $name === '..') {
            $name = $fallbackPath ? basename(str_replace('\\', '/', $fallbackPath)) : 'download';
        }

        return $name !== '' ? $name : 'download';
    }

    /**
     * Force a download so browsers do not open/extract Scratch .sb3 (ZIP) projects.
     */
    public static function downloadResponse(string $path, ?string $downloadName = null): StreamedResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (! self::isAllowedPath($path) || ! Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        $name = self::sanitizeDownloadName($downloadName, $path);
        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        $storedExtension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        // Recover a usable Scratch extension when the stored path has one but the label does not.
        if ($extension === '' && in_array($storedExtension, ['sb3', 'sb2', 'sb'], true)) {
            $name .= '.'.$storedExtension;
            $extension = $storedExtension;
        }

        $mime = match ($extension) {
            // Scratch projects are ZIP containers. application/zip makes browsers rename/extract them.
            'sb3', 'sb2', 'sb' => 'application/octet-stream',
            default => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
        };

        return Storage::disk('public')->download($path, $name, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function downloadUrl(string $path, ?string $name = null): string
    {
        return route('submissions.file', [
            'path' => $path,
            'name' => self::sanitizeDownloadName($name, $path),
        ]);
    }
}
