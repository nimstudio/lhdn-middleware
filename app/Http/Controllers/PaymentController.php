<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Check if user already has a pending or approved payment
        $payment = Payment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with(['subscriptionPlan', 'approver'])
            ->first();

        if ($payment) {
            return view('payment.status', compact('payment'));
        }

        // Get the selected plan from session or registration
        $planId = session('subscription_plan_id') ?? request('plan');
        $plan = SubscriptionPlan::findOrFail($planId);

        return view('payment.index', compact('plan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:bank_transfer,card,fpx,ewallet'],
            'payment_proof' => ['required_if:payment_method,bank_transfer', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'payment_proof.required_if' => 'Please upload your payment receipt.',
        ]);

        $user = auth()->user();
        $planId = session('subscription_plan_id') ?? $request->subscription_plan_id;
        $plan = SubscriptionPlan::findOrFail($planId);

        DB::transaction(function () use ($request, $user, $plan) {
            // Store payment proof if bank transfer
            $proofPath = null;
            if ($request->payment_method === 'bank_transfer' && $request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
            }

            // Create payment record (company will be created after approval)
            Payment::create([
                'company_id' => $user->company_id ?? null,
                'subscription_plan_id' => $plan->id,
                'user_id' => $user->id,
                'amount' => $plan->price_annually,
                'payment_method' => $request->payment_method,
                'payment_proof' => $proofPath,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('payment.status')->with('success', 'Payment submitted successfully! Please wait for admin approval.');
    }

    public function status(): View
    {
        $user = auth()->user();
        $payment = Payment::where('user_id', $user->id)
            ->with(['subscriptionPlan', 'approver'])
            ->latest()
            ->first();

        return view('payment.status', compact('payment'));
    }
}
