<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LhdnCredential;
use App\Services\MyInvoisSdkService;
use App\Services\TinValidationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCredentialsController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();
        $credentials = LhdnCredential::where('company_id', $user->company_id)->first();

        return view('user-app.credentials.index', compact('credentials'));
    }

    public function create()
    {
        return view('user-app.credentials.create');
    }

    public function store(Request $request, MyInvoisSdkService $sdk, TinValidationService $tinService)
    {
        $user = Auth::user();

        // Support both legacy (api_key/api_secret/environment) and current (client_id/client_secret/mode)
        $clientId = $request->input('client_id') ?? $request->input('api_key');
        $clientSecret = $request->input('client_secret') ?? $request->input('api_secret');
        $mode = $request->input('mode') ?? $request->input('environment');
        // Map legacy 'uat' to 'sandbox'
        if ($mode === 'uat') {
            $mode = 'sandbox';
        }

        // Validate normalized inputs
        $request->merge([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'mode' => $mode,
        ]);

        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'in:sandbox,production'],
        ]);

        // Check if credentials already exist
        $existing = LhdnCredential::where('company_id', $user->company_id)->first();

        try {
            if ($existing) {
                $existing->update(array_merge($validated, [
                    'updated_by' => $user->id,
                ]));
                $credential = $existing->fresh();
            } else {
                $credential = LhdnCredential::create(array_merge($validated, [
                    'company_id' => $user->company_id,
                    'created_by' => $user->id,
                    'status' => 'active',
                ]));
            }

            // Authenticate with SDK immediately after save
            $auth = $sdk->authenticate($credential);

            $credential->update([
                'access_token' => $auth['access_token'] ?? null,
                'token_type' => $auth['token_type'] ?? null,
                'last_token_refresh' => now(),
                'token_expires_at' => $auth['expires_at'] ?? now()->addHour(),
                'status' => 'active',
            ]);

            // Trigger TIN validation after successful auth
            $tinResult = $tinService->validateCompanyTin($user->company);
            $message = 'Credentials verified and saved.';
            if ($tinResult['success']) {
                $message .= ' '.$tinResult['message'];
            } else {
                $message .= ' Note: '.$tinResult['message'];
            }

            return redirect()->route('user.credentials.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            // Mark as invalid but keep saved client id/secret for correction
            if (isset($credential)) {
                $credential->update([
                    'status' => 'invalid',
                ]);
            }

            return redirect()->back()->withInput()->withErrors([
                'client_id' => 'Authentication failed: '.$e->getMessage(),
            ]);
        }
    }

    public function show(LhdnCredential $credential)
    {
        $this->authorize('view', $credential);

        return view('user-app.credentials.show', compact('credential'));
    }

    public function edit(LhdnCredential $credential)
    {
        $this->authorize('update', $credential);

        return view('user-app.credentials.edit', compact('credential'));
    }

    public function update(Request $request, LhdnCredential $credential, MyInvoisSdkService $sdk, TinValidationService $tinService)
    {
        $this->authorize('update', $credential);

        // Normalize inputs (support legacy field names)
        $clientId = $request->input('client_id') ?? $request->input('api_key');
        $clientSecret = $request->input('client_secret') ?? $request->input('api_secret');
        $mode = $request->input('mode') ?? $request->input('environment');
        if ($mode === 'uat') {
            $mode = 'sandbox';
        }

        $request->merge([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'mode' => $mode,
        ]);

        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'in:sandbox,production'],
        ]);

        try {
            $credential->update(array_merge($validated, [
                'updated_by' => Auth::id(),
            ]));

            $auth = $sdk->authenticate($credential->fresh());
            $credential->update([
                'access_token' => $auth['access_token'] ?? null,
                'token_type' => $auth['token_type'] ?? null,
                'last_token_refresh' => now(),
                'token_expires_at' => $auth['expires_at'] ?? now()->addHour(),
                'status' => 'active',
            ]);

            // Trigger TIN validation after successful auth
            $tinResult = $tinService->validateCompanyTin($credential->company);
            $message = 'Credentials verified and updated.';
            if ($tinResult['success']) {
                $message .= ' '.$tinResult['message'];
            } else {
                $message .= ' Note: '.$tinResult['message'];
            }

            return redirect()->route('user.credentials.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            $credential->update([
                'status' => 'invalid',
            ]);

            return redirect()->back()->withInput()->withErrors([
                'client_id' => 'Authentication failed: '.$e->getMessage(),
            ]);
        }
    }

    public function destroy(LhdnCredential $credential)
    {
        $this->authorize('delete', $credential);

        $credential->delete();

        return redirect()->route('user.credentials.index')
            ->with('success', 'LHDN credentials deleted successfully.');
    }

    public function test(Request $request, MyInvoisSdkService $sdk)
    {
        $user = Auth::user();
        $credentials = LhdnCredential::where('company_id', $user->company_id)->first();
        if (! $credentials) {
            return redirect()->route('user.credentials.index')->with('error', 'No credentials found to test.');
        }

        try {
            // Step 1: Ensure we have a valid token
            $credentials = $sdk->ensureValidToken($credentials);

            // Log token details for debugging
            Log::info('Test Connection - Token validated', [
                'credential_id' => $credentials->id,
                'has_token' => $credentials->access_token ? 'yes' : 'no',
                'token_expires_at' => $credentials->token_expires_at,
                'access_token' => $credentials->access_token,
                'mode' => $credentials->mode,
            ]);

            // Step 2: Make an actual API call to test the connection
            // We'll use a lightweight API call - TIN validation with a dummy TIN
            // This will test both authentication and API connectivity
            $testTin = 'E100000000010'; // Dummy TIN for testing
            Log::info('Test Connection - Starting TIN validation call', [
                'test_tin' => $testTin,
                'test_type' => 'NRIC',
                'test_value' => '120386039012',
            ]);

            $testResult = $sdk->validateTaxPayerTinDirect($testTin, 'NRIC', '120386039012', $credentials);

            Log::info('Test Connection - TIN validation result', [
                'result' => $testResult,
                'has_error' => isset($testResult['error']),
                'status_code' => $testResult['status_code'] ?? null,
            ]);

            // Check if the direct method returned an error
            if (isset($testResult['error'])) {
                // For 404 Not Found, the API is reachable but TIN doesn't exist - treat as success
                if (($testResult['status_code'] ?? 0) == 404) {
                    Log::info('Test Connection - 404 received, API is reachable', [
                        'test_tin' => $testTin,
                        'status_code' => $testResult['status_code'],
                    ]);

                    return redirect()->route('user.credentials.index')
                        ->with('success', 'Connection successful! LHDN API is accessible.');
                } else {
                    // Other errors (like 401) should fail the test
                    throw new \Exception('TIN validation failed: '.$testResult['error'].' (Status: '.($testResult['status_code'] ?? 'unknown').')');
                }
            }

            // If no error, connection is working
            return redirect()->route('user.credentials.index')
                ->with('success', 'Connection successful! LHDN API is accessible and credentials are valid.');

        } catch (\Throwable $e) {
            // Log the detailed error for debugging
            Log::error('LHDN Connection Test Failed', [
                'company_id' => $user->company_id,
                'credential_id' => $credentials->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Check if it's a 404 (TIN not found) - this actually means the API is working
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                Log::info('Test Connection - 404 received, API is reachable', [
                    'company_id' => $user->company_id,
                    'credential_id' => $credentials->id,
                ]);

                return redirect()->route('user.credentials.index')
                    ->with('success', 'Connection successful! LHDN API is accessible.');
            }

            // Provide user-friendly error messages for other errors
            $errorMessage = 'Connection failed: '.$e->getMessage();

            if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), 'Unauthorized')) {
                $errorMessage = 'Authentication failed. Please check your Client ID and Client Secret are correct.';
            } elseif (str_contains($e->getMessage(), '403') || str_contains($e->getMessage(), 'Forbidden')) {
                $errorMessage = 'Access forbidden. Your account may not have the required permissions.';
            } elseif (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                $errorMessage = 'API endpoint not found. Please check your environment settings (sandbox vs production).';
            }

            return redirect()->route('user.credentials.index')
                ->with('error', $errorMessage);
        }
    }
}
