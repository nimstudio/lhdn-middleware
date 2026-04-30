<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanSelectionController extends Controller
{
    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'exists:subscription_plans,id'],
        ]);

        session(['subscription_plan_id' => $request->plan]);

        // If user is authenticated and verified, go to payment
        if (auth()->check() && auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('payment.index');
        }

        // Otherwise, go to register
        return redirect()->route('register', ['plan' => $request->plan]);
    }
}
