<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LhdnCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get recent invoices
        $recentInvoices = Invoice::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get invoice statistics
        $invoiceStats = [
            'total' => Invoice::where('company_id', $user->company_id)->count(),
            'pending' => Invoice::where('company_id', $user->company_id)
                ->where('lhdn_status', 'draft')->count(),
            'submitted' => Invoice::where('company_id', $user->company_id)
                ->whereIn('lhdn_status', ['pending', 'submitted', 'valid', 'invalid', 'cancelled', 'rejected'])->count(),
            'approved' => Invoice::where('company_id', $user->company_id)
                ->where('lhdn_status', 'valid')->count(),
        ];

        // Check if user has LHDN credentials
        $hasCredentials = LhdnCredential::where('company_id', $user->company_id)->exists();

        // Getting started checklist
        $checklist = [
            'company' => $user->company_id ? true : false,
            'credentials' => $hasCredentials,
            'subscription' => $user->subscription_status === 'active',
            'first_invoice' => $invoiceStats['total'] > 0,
        ];

        // API Key info (from company)
        $apiKeyInfo = [
            'has_key' => $user->company && $user->company->hasApiKey() ? true : false,
            'masked_key' => $user->company ? $user->company->getMaskedApiKey() : '',
            'created_at' => $user->company ? $user->company->api_key_created_at : null,
        ];

        return view('user-app.dashboard', compact(
            'user',
            'recentInvoices',
            'invoiceStats',
            'hasCredentials',
            'checklist',
            'apiKeyInfo'
        ));
    }

    /**
     * Generate or regenerate API key for the authenticated user's company.
     */
    public function generateApiKey(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        // Generate new API key (this will replace any existing key)
        $apiKey = $user->company->generateApiKey();

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'api_key' => $apiKey,
            'created_at' => $user->company->api_key_created_at->toISOString(),
        ]);
    }

    /**
     * Get current API key info (without revealing the key).
     */
    public function getApiKeyInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        return response()->json([
            'has_key' => $user->company->hasApiKey(),
            'masked_key' => $user->company->getMaskedApiKey(),
            'created_at' => $user->company->api_key_created_at ? $user->company->api_key_created_at->toISOString() : null,
        ]);
    }
}
