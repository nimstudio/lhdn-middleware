<?php

namespace App\Http\Middleware;

use App\Services\TinValidationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TinVerified
{
    public function __construct(
        private TinValidationService $tinService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || !$user->company) {
            return $next($request);
        }

        $company = $user->company;

        // Check if TIN validation is required
        if ($this->tinService->requiresTinValidation($company)) {
            // Allow access to company management and credentials
            $allowedRoutes = [
                'user.company.show',
                'user.company.edit',
                'user.company.update',
                'user.credentials.index',
                'user.credentials.create',
                'user.credentials.store',
                'user.credentials.edit',
                'user.credentials.update',
                'user.credentials.test',
                'user.settings',
                'user.settings.pdf',
                'user.settings.pdf.update',
            ];

            $currentRoute = $request->route()?->getName();

            if (!in_array($currentRoute, $allowedRoutes)) {
                return redirect()->route('user.company.edit')
                    ->with('error', 'Your company TIN must be validated with LHDN before you can access this feature. Please update your company details.');
            }
        }

        return $next($request);
    }
}
