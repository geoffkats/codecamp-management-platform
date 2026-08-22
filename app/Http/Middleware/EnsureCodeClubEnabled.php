<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCodeClubEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('features.code_club', false), 404);

        return $next($request);
    }
}
