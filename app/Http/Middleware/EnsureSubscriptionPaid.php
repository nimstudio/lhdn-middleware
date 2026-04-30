<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionPaid
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

        // Check if user has active subscription
        if ($user->subscription_status !== 'active') {
            return redirect()->route('payment.index');
        }

        return $next($request);
    }
}
