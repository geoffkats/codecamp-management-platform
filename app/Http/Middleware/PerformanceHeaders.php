<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add performance headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Enable compression hint
        if (!$response->headers->has('Content-Encoding')) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }
        
        // Cache static assets
        if ($request->is('assets/*') || $request->is('build/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }
        
        return $response;
    }
}
