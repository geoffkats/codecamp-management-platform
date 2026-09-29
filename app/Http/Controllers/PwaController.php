<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PwaController extends Controller
{
    public const ICON_SIZES = [192, 512];

    public const THEME_COLOR = '#1e3a8a';

    public function manifest(): JsonResponse
    {
        $appName = (string) SystemSetting::get('app_name', config('app.name'));
        $version = $this->iconVersion();

        $icons = [];
        foreach (self::ICON_SIZES as $size) {
            $src = route('pwa.icon', ['size' => $size, 'v' => $version]);
            $icons[] = ['src' => $src, 'sizes' => "{$size}x{$size}", 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => route('pwa.icon', ['size' => $size, 'maskable' => 1, 'v' => $version]), 'sizes' => "{$size}x{$size}", 'type' => 'image/png', 'purpose' => 'maskable'];
        }

        $scope = rtrim(url('/'), '/').'/';

        return response()->json([
            'id' => $scope,
            'name' => $appName,
            'short_name' => mb_strimwidth($appName, 0, 12, ''),
            'description' => (string) SystemSetting::get('app_tagline', 'Digital Excellence Through Education'),
            'start_url' => url('/dashboard').'?source=pwa',
            'scope' => $scope,
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#ffffff',
            'theme_color' => self::THEME_COLOR,
            'icons' => $icons,
        ], 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function icon(int $size): BinaryFileResponse
    {
        abort_unless(in_array($size, self::ICON_SIZES, true), 404);

        $maskable = request()->boolean('maskable');
        $cachePath = storage_path('app/pwa/icon-'.$size.($maskable ? '-maskable' : '').'-'.$this->iconVersion().'.png');

        if (! is_file($cachePath)) {
            if (! is_dir(dirname($cachePath))) {
                mkdir(dirname($cachePath), 0755, true);
            }
            imagepng($this->renderIcon($size, $maskable), $cachePath, 9);
        }

        return response()->file($cachePath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function offline()
    {
        return response()->view('pwa.offline', [
            'appName' => (string) SystemSetting::get('app_name', config('app.name')),
        ]);
    }

    /**
     * Changes whenever the uploaded logo/favicon changes, so installed apps pick up new icons.
     */
    private function iconVersion(): string
    {
        $source = $this->sourceImagePath();

        return substr(md5(($source ?? 'fallback').'|'.($source ? filemtime($source) : '0')), 0, 10);
    }

    private function sourceImagePath(): ?string
    {
        foreach (['favicon', 'logo', 'logo_dark'] as $key) {
            $relative = SystemSetting::get($key);
            if (! is_string($relative) || $relative === '') {
                continue;
            }

            $path = Storage::disk('public')->path($relative);
            if (is_file($path) && $this->loadImage($path) !== null) {
                return $path;
            }
        }

        return null;
    }

    private function loadImage(string $path): ?\GdImage
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        return $image instanceof \GdImage ? $image : null;
    }

    private function renderIcon(int $size, bool $maskable): \GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);

        $source = $this->sourceImagePath();
        $sourceImage = $source ? $this->loadImage($source) : null;

        if (! $sourceImage) {
            return $this->renderFallbackIcon($canvas, $size);
        }

        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        // Maskable icons get cropped to a circle/squircle by Android, so keep the logo inside the safe zone.
        $padding = (int) round($size * ($maskable ? 0.2 : 0.08));
        $box = $size - ($padding * 2);

        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);
        $scale = min($box / $srcW, $box / $srcH);
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));

        imagecopyresampled(
            $canvas,
            $sourceImage,
            (int) (($size - $dstW) / 2),
            (int) (($size - $dstH) / 2),
            0,
            0,
            $dstW,
            $dstH,
            $srcW,
            $srcH
        );

        return $canvas;
    }

    private function renderFallbackIcon(\GdImage $canvas, int $size): \GdImage
    {
        [$r, $g, $b] = sscanf(self::THEME_COLOR, '#%02x%02x%02x');
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, $r, $g, $b));

        $letter = strtoupper(mb_substr((string) SystemSetting::get('app_name', config('app.name')), 0, 1)) ?: 'C';
        $font = 5;
        $scale = max(1, (int) floor($size / 40));

        $glyph = imagecreatetruecolor(imagefontwidth($font), imagefontheight($font));
        imagefill($glyph, 0, 0, imagecolorallocate($glyph, $r, $g, $b));
        imagestring($glyph, $font, 0, 0, $letter, imagecolorallocate($glyph, 255, 255, 255));

        $w = imagefontwidth($font) * $scale;
        $h = imagefontheight($font) * $scale;
        imagecopyresized($canvas, $glyph, (int) (($size - $w) / 2), (int) (($size - $h) / 2), 0, 0, $w, $h, imagefontwidth($font), imagefontheight($font));

        return $canvas;
    }
}
