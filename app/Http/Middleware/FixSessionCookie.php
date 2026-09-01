<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $isIpHost = filter_var($host, FILTER_VALIDATE_IP) !== false;

        // IP / HTTP logins cannot use Secure cookies — the browser will drop them
        // and Sign in will look like it does nothing (silent CSRF bounce).
        if ($isIpHost || ! $request->secure()) {
            config(['session.secure' => false]);
        } else {
            config(['session.secure' => true]);
        }

        $configuredDomain = config('session.domain');
        if ($isIpHost) {
            config(['session.domain' => null]);
        } elseif (is_string($configuredDomain) && $configuredDomain !== '') {
            $domain = ltrim($configuredDomain, '.');
            if ($host !== $domain && ! str_ends_with($host, '.'.$domain)) {
                config(['session.domain' => null]);
            }
        }

        return $next($request);
    }
}
