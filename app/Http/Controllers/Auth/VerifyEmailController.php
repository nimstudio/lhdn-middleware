<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Check if user has paid
            $hasActiveSub = $request->user()->company?->subscription_status === 'active';

            return redirect()->intended(
                $hasActiveSub ? route('dashboard', absolute: false).'?verified=1' : route('payment.index')
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // After verification, redirect to payment page
        return redirect()->route('payment.index');
    }
}
