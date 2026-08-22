<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourseCatalogAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->canAccessCourseCatalog()) {
            return redirect()
                ->route('enrollments.index')
                ->with('message', 'Open your enrolled courses from My Courses.');
        }

        return $next($request);
    }
}
