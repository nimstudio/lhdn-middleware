<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\State;
use App\Services\MyInvoisSdkService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Customer::where('company_id', $user->company_id)
            ->with(['state', 'invoices']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tin', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('state', function ($stateQuery) use ($search) {
                        $stateQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // State filter
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->get('state_id'));
        }

        // Document type filter
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        // Date range filter (created_at)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSortFields = ['name', 'email', 'city', 'created_at', 'is_active'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->get('per_page', 20);
        $perPageOptions = [10, 20, 50, 100];
        if (! in_array($perPage, $perPageOptions)) {
            $perPage = 20;
        }

        $customers = $query->paginate($perPage)->appends($request->query());

        // Get states for filter dropdown
        $states = State::orderBy('name')->get();

        // Get statistics
        $totalCustomers = Customer::where('company_id', $user->company_id)->count();
        $activeCustomers = Customer::where('company_id', $user->company_id)->where('is_active', true)->count();
        $inactiveCustomers = $totalCustomers - $activeCustomers;

        return view('user-app.customers.index', compact(
            'customers',
            'states',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers'
        ));
    }

    public function create()
    {
        $states = State::orderBy('name')->get();

        return view('user-app.customers.create', compact('states'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_id' => ['nullable', 'exists:states,id'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'size:3'],
            'tin' => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:BRN,NRIC,PASSPORT,ARMY'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $user->company_id;
        $validated['country'] = $validated['country'] ?? 'MYS';

        Customer::create($validated);

        return redirect()->route('user.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load(['state', 'invoices' => fn ($q) => $q->latest()->limit(10)]);

        return view('user-app.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        $states = State::orderBy('name')->get();

        return view('user-app.customers.edit', compact('customer', 'states'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_id' => ['nullable', 'exists:states,id'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'size:3'],
            'tin' => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:BRN,NRIC,PASSPORT,ARMY'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return redirect()->route('user.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('user.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * API endpoint for customer search (used in invoice create)
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        $customers = Customer::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('tin', 'like', "%{$query}%");
            })
            ->with('state')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    /**
     * Bulk actions for customers
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        $action = $request->get('action');
        $customerIdsInput = $request->get('customer_ids', []);

        // Handle customer_ids as string (comma-separated) or array
        if (is_string($customerIdsInput)) {
            $customerIds = array_filter(explode(',', $customerIdsInput));
        } else {
            $customerIds = $customerIdsInput;
        }

        if (empty($customerIds) || ! $action) {
            return redirect()->back()->with('error', 'No customers selected or action specified.');
        }

        $customers = Customer::where('company_id', $user->company_id)
            ->whereIn('id', $customerIds)
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            $this->authorize('update', $customer);

            switch ($action) {
                case 'activate':
                    $customer->update(['is_active' => true]);
                    $count++;
                    break;
                case 'deactivate':
                    $customer->update(['is_active' => false]);
                    $count++;
                    break;
                case 'delete':
                    $customer->delete();
                    $count++;
                    break;
            }
        }

        $message = match ($action) {
            'activate' => "{$count} customers activated successfully.",
            'deactivate' => "{$count} customers deactivated successfully.",
            'delete' => "{$count} customers deleted successfully.",
            default => 'Action completed successfully.'
        };

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export customers to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = Customer::where('company_id', $user->company_id)
            ->with(['state']);

        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tin', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('state', function ($stateQuery) use ($search) {
                        $stateQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->get('state_id'));
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $customers = $query->orderBy('name')->get();

        $filename = 'customers_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Street Address',
                'City',
                'State',
                'Postal Code',
                'Country',
                'TIN',
                'Document Type',
                'Document Number',
                'Status',
                'Notes',
                'Created At',
                'Updated At',
            ]);

            // CSV data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->street_address,
                    $customer->city,
                    $customer->state?->name,
                    $customer->postal_code,
                    $customer->country,
                    $customer->tin,
                    $customer->document_type,
                    $customer->document_number,
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->notes,
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function validateTin(Request $request, Customer $customer, MyInvoisSdkService $sdk)
    {
        $this->authorize('view', $customer);

        $request->validate([
            'tin' => 'required|string|max:50',
            'document_type' => 'required|in:BRN,NRIC,PASSPORT,ARMY',
            'document_number' => 'required|string|max:50',
        ]);

        try {
            $credentials = $customer->company->lhdnCredential;

            Log::info('Web TIN Validation - Customer credentials check', [
                'customer_id' => $customer->id,
                'customer_company_id' => $customer->company_id,
                'user_company_id' => Auth::user()->company_id,
                'credentials_found' => $credentials ? 'yes' : 'no',
                'credential_id' => $credentials?->id,
                'credential_mode' => $credentials?->mode,
                'has_token' => $credentials && $credentials->access_token ? 'yes' : 'no',
                'token_expired' => $credentials?->isTokenExpired(),
            ]);

            if (! $credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'LHDN credentials not configured for this company.',
                ]);
            }

            // Ensure we have a valid token
            $credentials = $sdk->ensureValidToken($credentials);

            Log::info('Web TIN Validation - After token refresh', [
                'credential_id' => $credentials->id,
                'has_token' => $credentials->access_token ? 'yes' : 'no',
                'token_expired' => $credentials->isTokenExpired(),
                'token_expires_at' => $credentials->token_expires_at,
            ]);

            $response = $sdk->validateTaxPayerTinDirect(
                $request->tin,
                $request->document_type,
                $request->document_number,
                $credentials
            );

            // Check response status based on direct API response structure
            // 200 status code indicates valid TIN, even if response body is empty
            $isValid = isset($response['status_code']) && $response['status_code'] == 200;

            // Provide more specific error messages based on response
            if ($isValid) {
                $message = 'Taxpayer information validated successfully.';
                $details = 'TIN is valid and registered with LHDN.';
            } else {
                $message = 'Taxpayer information validation failed.';
                if (isset($response['error'])) {
                    $details = 'LHDN API Error: '.$response['error'];
                    if (isset($response['status_code'])) {
                        $details .= ' (HTTP '.$response['status_code'].')';
                    }
                } else {
                    $details = 'TIN validation returned empty response. Please check the TIN format or try again later.';
                }
            }

            return response()->json([
                'success' => $isValid,
                'message' => $message,
                'details' => $details,
                'response_data' => $response, // Include full response for debugging
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
