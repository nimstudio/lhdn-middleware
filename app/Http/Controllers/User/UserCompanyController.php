<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Msic;
use App\Models\State;
use App\Services\TinValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCompanyController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $company = $user->company;

        if (! $company) {
            return redirect()->route('user.company.edit');
        }

        return view('user-app.company.show', compact('company'));
    }

    public function edit()
    {
        $user = Auth::user();
        $company = $user->company;
        $states = State::orderBy('name')->get();
        $msics = Msic::orderBy('code')->get();
        $itemClassifications = \App\Models\ItemClassification::active()->orderBy('sort_order')->get();

        return view('user-app.company.edit', compact('company', 'states', 'msics', 'itemClassifications'));
    }

    public function update(Request $request, TinValidationService $tinService)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build validation rules
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'tin_number' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:500'],
            'address_line_2' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'exists:states,id'],
            'postcode' => ['required', 'string', 'max:10'],
            'business_type_id' => ['required', 'exists:msics,id'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'default_item_classification_id' => ['required', 'exists:item_classifications,id'],
        ];

        // Add TIN uniqueness validation
        if ($company) {
            // For updates, exclude current company from uniqueness check
            $validationRules['tin_number'][] = 'unique:companies,tin_number,' . $company->id;
            $validationRules['registration_number'][] = 'unique:companies,registration_number,' . $company->id;
        } else {
            // For new companies, check full uniqueness
            $validationRules['tin_number'][] = 'unique:companies,tin_number';
            $validationRules['registration_number'][] = 'unique:companies,registration_number';
        }

        $validated = $request->validate($validationRules, [
            'tin_number.unique' => 'This TIN number is already registered with another company. Please use a different TIN number or contact support if you believe this is an error.',
            'registration_number.unique' => 'This registration number is already registered with another company. Please use a different registration number or contact support if you believe this is an error.',
        ]);

        if ($company) {
            $company->update($validated);
        } else {
            // For new companies, ensure default_item_classification_id is set
            if (!isset($validated['default_item_classification_id'])) {
                $defaultClassification = \App\Models\ItemClassification::where('code', '022')->first();
                if ($defaultClassification) {
                    $validated['default_item_classification_id'] = $defaultClassification->id;
                }
            }

            $company = Company::create(array_merge($validated, [
                'subscription_plan_id' => $user->subscription_plan_id,
            ]));

            // Update user's company_id
            $user->update(['company_id' => $company->id]);
        }

        $message = 'Company information updated successfully.';

        // If LHDN credentials exist, trigger TIN validation
        if ($company->lhdnCredential && $company->lhdnCredential->status === 'active') {
            $tinResult = $tinService->validateCompanyTin($company);
            if ($tinResult['success']) {
                $message .= ' ' . $tinResult['message'];
                return redirect()->route('user.company.show')
                    ->with('success', $message);
            } else {
                // TIN validation failed - show error message
                return redirect()->route('user.company.edit')
                    ->with('error', $tinResult['message'])
                    ->withInput();
            }
        }

        return redirect()->route('user.company.show')
            ->with('success', $message);
    }

}
