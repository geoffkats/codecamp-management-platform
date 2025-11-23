<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Generate optimized image tag with lazy loading
     */
    public static function lazy($src, $alt = '', $class = '', $width = null, $height = null)
    {
        $attributes = [
            'src' => $src,
            'alt' => $alt,
            'loading' => 'lazy',
            'decoding' => 'async',
        ];

        if ($class) {
            $attributes['class'] = $class;
        }

        if ($width) {
            $attributes['width'] = $width;
        }

        if ($height) {
            $attributes['height'] = $height;
        }

        $attrs = collect($attributes)
            ->map(fn($value, $key) => "{$key}=\"{$value}\"")
            ->implode(' ');

        return "<img {$attrs}>";
    }

    /**
     * Generate responsive image with srcset
     */
    public static function responsive($src, $alt = '', $sizes = [])
    {
        $srcset = collect($sizes)
            ->map(fn($size) => "{$src}?w={$size} {$size}w")
            ->implode(', ');

        return sprintf(
            '<img src="%s" srcset="%s" sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw" alt="%s" loading="lazy" decoding="async">',
            $src,
            $srcset,
            $alt
        );
    }
}
