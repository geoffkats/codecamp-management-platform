<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserExists
{
    /**
     * Handle an incoming request.
     *
     * Ensures that the authenticated user actually exists in the database.
     * If the session contains a user ID that doesn't exist, log them out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If we can't load the user model, it means the user was deleted
            // but the session still exists
            if (!$user || !$user->exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your account is no longer active. Please contact support if this is an error.');
            }
        }
        
        return $next($request);
    }
}
