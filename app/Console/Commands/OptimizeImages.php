<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--path=storage/app/public/settings : Path to optimize}';
    protected $description = 'Optimize images by resizing and compressing them';

    public function handle()
    {
        $path = storage_path('app/public/settings');
        
        if (!File::exists($path)) {
            $this->error("Path does not exist: {$path}");
            return 1;
        }

        $files = File::files($path);
        $optimized = 0;

        $this->info("Found " . count($files) . " files to process...");

        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            try {
                $originalSize = $file->getSize();
                $image = Image::read($file->getPathname());
                
                // Get dimensions
                $width = $image->width();
                $height = $image->height();
                
                // Only resize if image is larger than 1920px
                if ($width > 1920 || $height > 1920) {
                    $image->scale(width: 1920);
                    $this->line("Resized: {$file->getFilename()} from {$width}x{$height}");
                }
                
                // Save with compression
                $image->save($file->getPathname(), quality: 85);
                
                $newSize = filesize($file->getPathname());
                $saved = $originalSize - $newSize;
                $percentage = round(($saved / $originalSize) * 100, 2);
                
                $this->info("✓ Optimized: {$file->getFilename()} - Saved " . $this->formatBytes($saved) . " ({$percentage}%)");
                $optimized++;
                
            } catch (\Exception $e) {
                $this->error("Failed to optimize {$file->getFilename()}: " . $e->getMessage());
            }
        }

        $this->info("\n✓ Optimized {$optimized} images!");
        return 0;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
