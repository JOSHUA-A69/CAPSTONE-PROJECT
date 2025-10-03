<?php

namespace App\Http\Middleware;

use Closure;

class HandleCors
{
    public function handle($request, Closure $next)
    {
        // If the package class exists, delegate to it for full functionality.
        $class = 'Fruitcake\\Cors\\HandleCors';
        if (class_exists($class)) {
            $middleware = new $class();
            return $middleware->handle($request, $next);
        }

        // Minimal CORS pass-through (safe fallback for editor/static analysis).
        $response = $next($request);
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
        foreach ($headers as $k => $v) {
            $response->headers->set($k, $v, false);
        }

        return $response;
    }
}
