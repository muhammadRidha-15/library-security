<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and attach Content-Security-Policy headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // VULNERABLE BRANCH: Disable restrictive CSP so XSS alert popup executes in browser (TKT-24 / SEC-09)
        // Main branch enforces: "default-src 'self'; script-src 'self'; object-src 'none'..."

        return $response;
    }
}
