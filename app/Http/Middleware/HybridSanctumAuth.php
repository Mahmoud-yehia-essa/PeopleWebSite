<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HybridSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth('sanctum')->user();
            if ($user) {
                Auth::setUser($user);
                auth('sanctum')->setUser($user);
                $request->setUserResolver(fn () => $user);
            }
        } catch (\Throwable $e) {
            // Silently continue to next pipeline
        }

        return $next($request);
    }
}
