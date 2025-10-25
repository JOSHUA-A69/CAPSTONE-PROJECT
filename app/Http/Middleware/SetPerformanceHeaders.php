<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPerformanceHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Cache control for static assets
        if ($this->isStaticAsset($request)) {
            // Cache static assets for 1 year
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        } elseif ($this->isApiRoute($request)) {
            // API routes - no cache
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        } else {
            // HTML pages - cache for 5 minutes with revalidation
            $response->headers->set('Cache-Control', 'public, max-age=300, must-revalidate');
        }

        // Enable compression hint
        if (!$response->headers->has('Content-Encoding')) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        // Preconnect hints for better performance
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $response->headers->set('Link', '<https://fonts.bunny.net>; rel=preconnect', false);
        }

        return $response;
    }

    /**
     * Check if the request is for a static asset
     */
    protected function isStaticAsset(Request $request): bool
    {
        $path = $request->path();

        return preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|ico|webp)$/i', $path)
            || str_starts_with($path, 'build/')
            || str_starts_with($path, 'storage/');
    }

    /**
     * Check if the request is for an API route
     */
    protected function isApiRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/');
    }
}
