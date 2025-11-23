<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasStudentProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow admins, supervisors, operations managers, and teachers to access courses
        if ($user && $user->hasAnyRole(['admin', 'supervisor', 'operations_manager', 'teacher'])) {
            return $next($request);
        }

        // For regular users, check if they have a student profile
        if ($user && !$user->studentProfile) {
            // Redirect to a page explaining they need to be registered as a student
            return redirect()->route('dashboard')->with('error', 'You need to be registered as a student by the operations manager before accessing courses. Please contact the administration.');
        }

        return $next($request);
    }
}
