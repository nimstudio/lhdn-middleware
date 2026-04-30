<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasCompany
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
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Check if user has a company
        if (! $user->company_id) {
            return redirect()->route('user.company.edit')
                ->with('error', 'Please set up your company information before accessing this feature.');
        }

        return $next($request);
    }
}
