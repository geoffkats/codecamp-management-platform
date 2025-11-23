<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add cache headers for static assets
        if ($this->isStaticAsset($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        } else {
            // Don't cache HTML pages
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Allow embedding in iframe for embedded mode (curriculum builder)
        if (!$request->has('embedded')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Enable compression if not already enabled
        if (!$response->headers->has('Content-Encoding') && 
            function_exists('gzencode') && 
            $this->shouldCompress($request, $response)) {
            
            $content = $response->getContent();
            if ($content && strlen($content) > 1024) { // Only compress if > 1KB
                $compressed = gzencode($content, 6); // Level 6 compression
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                }
            }
        }

        return $response;
    }

    /**
     * Check if the request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        $extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'woff', 'woff2', 'ttf', 'otf', 'ico'];
        
        foreach ($extensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }
        
        return str_starts_with($path, 'build/');
    }

    /**
     * Check if response should be compressed
     */
    private function shouldCompress(Request $request, Response $response): bool
    {
        // Check if client accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip')) {
            return false;
        }

        // Check content type
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'text/plain',
        ];

        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }
}
