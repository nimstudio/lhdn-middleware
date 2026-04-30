<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LhdnCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        $credentials = LhdnCredential::where('company_id', $user->company_id)->first();
        $taxTypes = \App\Models\TaxType::active()->orderBy('sort_order')->get();

        return view('user-app.settings.index', compact('user', 'company', 'credentials', 'taxTypes'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        // Verify current password if changing password
        if ($validated['password'] && ! Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('user.settings')
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        if ($validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('user.settings')
            ->with('success', 'Profile updated successfully.');
    }

    public function updateCompany(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (! $company) {
            return redirect()->route('user.settings')
                ->with('error', 'Please set up your company first.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:500'],
            'address_line_2' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'exists:states,id'],
            'postcode' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'default_item_classification_id' => ['required', 'exists:item_classifications,id'],
        ]);

        $company->update($validated);

        return redirect()->route('user.settings')
            ->with('success', 'Company information updated successfully.');
    }

    public function updateCredentials(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['required', 'string', 'max:255'],
            'environment' => ['required', 'in:uat,production'],
            'is_active' => ['boolean'],
        ]);

        $credentials = LhdnCredential::where('company_id', $user->company_id)->first();

        if ($credentials) {
            $credentials->update($validated);
        } else {
            LhdnCredential::create(array_merge($validated, [
                'company_id' => $user->company_id,
            ]));
        }

        return redirect()->route('user.settings')
            ->with('success', 'LHDN credentials updated successfully.');
    }

    public function updateInvoiceSettings(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (! $company) {
            return redirect()->route('user.settings')
                ->with('error', 'Please set up your company first.');
        }

        $validated = $request->validate([
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'default_tax_rates' => ['nullable', 'json'],
        ]);

        // Decode and validate tax rates
        $taxRates = json_decode($validated['default_tax_rates'] ?? '[]', true);

        // Filter out empty rates and validate structure
        $taxRates = collect($taxRates)
            ->filter(fn ($rate) => isset($rate['value']) && $rate['value'] !== '')
            ->map(function ($rate) {
                // Validate tax_type_id (required for LHDN compliance)
                if (!isset($rate['tax_type_id']) || !$rate['tax_type_id']) {
                    throw new \Exception('LHDN Tax Type is required for all tax rates.');
                }

                $taxType = \App\Models\TaxType::find($rate['tax_type_id']);
                if (!$taxType) {
                    throw new \Exception('Invalid LHDN Tax Type selected.');
                }

                $rate['tax_type_code'] = $taxType->code;
                return $rate;
            })
            ->values()
            ->toArray();

        $company->update([
            'invoice_prefix' => $validated['invoice_prefix'],
            'default_tax_rates' => $taxRates,
        ]);

        return redirect()->route('user.settings')
            ->with('success', 'Invoice settings updated successfully.');
    }
}
