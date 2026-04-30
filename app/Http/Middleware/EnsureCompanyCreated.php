<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyCreated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for super admins
        if ($user?->is_super_admin) {
            return $next($request);
        }

        // Always allow these routes
        $allowedRoutes = [
            'filament.app.pages.dashboard',
            'filament.app.resources.companies.*',
            'filament.app.auth.*',
        ];

        foreach ($allowedRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        // If user doesn't have a company, redirect to dashboard (not company create)
        // The dashboard will show the Getting Started widget
        if (! $user?->company_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please complete your company setup first.',
                ], 403);
            }

            return redirect()
                ->route('filament.app.pages.dashboard')
                ->with('warning', 'Please complete your company information to access all features.');
        }

        return $next($request);
    }
}
