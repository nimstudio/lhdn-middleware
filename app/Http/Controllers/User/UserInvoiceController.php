<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Imports\BulkInvoiceImport;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ItemClassification;
use App\Models\State;
use App\Models\TaxType;
use App\Services\InvoiceCreationService;
use App\Services\InvoiceNumberGenerator;
use App\Services\InvoicePdfService;
use App\Services\InvoiceValidationService;
use App\Services\MyInvoisSdkService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UserInvoiceController extends Controller
{
    use AuthorizesRequests;

    protected InvoiceValidationService $validationService;

    protected InvoiceCreationService $creationService;

    public function __construct(InvoiceValidationService $validationService, InvoiceCreationService $creationService)
    {
        $this->validationService = $validationService;
        $this->creationService = $creationService;
    }

    public function submission(Request $request)
    {

        $request->merge(['title' => 'Invoice Submission']);

        return $this->index($request);
    }

    public function cancellation(Request $request, MyInvoisSdkService $sdk)
    {
        $cancelPeriodHours = 72;
        $request->merge([
            'lhdn_status' => 'valid', // Only show valid invoices for cancellation
            'title' => 'Invoice Cancellation',
            'cancel_period_hours' => $cancelPeriodHours,
        ]);

        // Execute the index logic to get invoices
        $user = Auth::user();
        $query = Invoice::where('company_id', $user->company_id)->with('items');

        // Apply the same filters as index method
        if ($request->filled('lhdn_status')) {
            $query->where('lhdn_status', $request->get('lhdn_status'));
        }

        // Cancellation period filter
        if ($request->routeIs('user.invoices.cancellation')) {
            $query->where('created_at', '>=', now()->subHours($cancelPeriodHours));
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSortFields = ['invoice_number', 'customer_name', 'invoice_date', 'due_date', 'total_amount', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $invoices = $query->paginate($perPage)->appends($request->query());
        $title = $request->get('title', 'Invoices');

        Log::info('About to update LHDN statuses', [
            'invoices_count' => $invoices->count(),
        ]);

        // No need to check LHDN status in real-time - rely on stored status
        // Status is updated only during submission or specific status change operations
        $invoiceStatuses = [];

        return view('user-app.invoices.index', compact('invoices', 'title', 'invoiceStatuses', 'cancelPeriodHours'));
    }

    public function rejection(Request $request, MyInvoisSdkService $sdk)
    {
        $cancelPeriodHours = 72; // Same as cancellation
        $request->merge([
            'lhdn_status' => 'rejected', // Show rejected invoices
            'title' => 'Invoice Rejection',
            'cancel_period_hours' => $cancelPeriodHours,
        ]);

        // Execute the same logic as cancellation method
        $user = Auth::user();
        $query = Invoice::where('company_id', $user->company_id)->with('items');

        // Apply the same filters as index method
        if ($request->filled('lhdn_status')) {
            $query->where('lhdn_status', $request->get('lhdn_status'));
        }

        // Same cancellation period filter as cancellation page
        if ($request->routeIs('user.invoices.rejection')) {
            $query->where('created_at', '>=', now()->subHours($cancelPeriodHours));
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSortFields = ['invoice_number', 'created_at', 'total_amount', 'lhdn_status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 25);
        $invoices = $query->paginate($perPage);

        // Add query parameters to pagination links
        $invoices->appends($request->query());

        return view('user-app.invoices.index', compact('invoices') + $request->only(['title', 'lhdn_status', 'cancel_period_hours']));
    }

    public function cancelInvoice(Request $request, MyInvoisSdkService $sdk)
    {
        $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $invoiceId = $request->input('invoice_id');
        $reason = $request->input('reason', 'Customer refund');
        $user = Auth::user();

        // Get invoice that belongs to the current user's company and is in valid status
        $invoice = Invoice::where('id', $invoiceId)
            ->where('company_id', $user->company_id)
            ->where('lhdn_status', 'valid')
            ->with(['company', 'items'])
            ->first();

        if (! $invoice) {
            return redirect()->route('user.invoices.cancellation')
                ->with('error', 'Invoice not found or not eligible for cancellation. Make sure it is in valid status and belongs to your company.');
        }

        try {
            // Cancel the invoice
            $response = $sdk->cancelInvoice($invoice, $reason);

            return redirect()->route('user.invoices.cancellation')
                ->with('success', "Successfully cancelled invoice {$invoice->invoice_number}.");

        } catch (\Exception $e) {
            Log::error('Invoice cancellation failed: '.$e->getMessage());

            // Check if it's a status-related error that should be handled differently
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Invalid status') || str_contains($errorMessage, 'cannot be cancelled')) {
                return redirect()->route('user.invoices.cancellation')
                    ->with('warning', 'This invoice cannot be cancelled due to its current status. '.$errorMessage);
            }

            return redirect()->route('user.invoices.cancellation')
                ->with('error', 'Failed to cancel invoice: '.$errorMessage);
        }
    }

    public function index(Request $request)
    {

        $user = Auth::user();
        $query = Invoice::where('company_id', $user->company_id)->with('items');

        $cancelPeriodHours = 72; // Default for cancellation pages

        $query = Invoice::where('company_id', $user->company_id)
            ->with('items', 'customer.state');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Invoice Status filter
        if ($request->filled('invoice_status')) {
            $query->where('invoice_status', $request->get('invoice_status'));
        }

        // LHDN Status filter
        if ($request->filled('lhdn_status')) {
            $query->where('lhdn_status', $request->get('lhdn_status'));
        }

        // Cancellation period filter (only for cancellation page)
        if ($request->routeIs('user.invoices.cancellation')) {
            $query->where('created_at', '>=', now()->subHours($cancelPeriodHours));
        }

        Log::info('About to apply sorting', [
            'sort_field' => $request->get('sort', 'created_at'),
            'sort_direction' => $request->get('direction', 'desc'),
        ]);

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['invoice_number', 'customer_name', 'invoice_date', 'due_date', 'total_amount', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        Log::info('Validated sort field', [
            'original_sort' => $request->get('sort', 'created_at'),
            'validated_sort' => $sortField,
            'direction' => $sortDirection,
        ]);

        $query->orderBy($sortField, $sortDirection);
        Log::info('Sorting applied to query');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 25, 50, 100];
        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $invoices = $query->paginate($perPage)->appends($request->query());

        $title = $request->get('title', 'Invoices');

        return view('user-app.invoices.index', compact('invoices', 'title'));
    }

    public function create(InvoiceNumberGenerator $generator)
    {
        $user = Auth::user();
        $company = $user->company;

        // Generate next invoice number
        $nextInvoiceNumber = $generator->generate($company);

        // Get default tax rates from company settings (should always have defaults from Company model boot)
        $defaultTaxRates = $company->default_tax_rates ?? [];

        // Get all Malaysian states
        $states = State::orderBy('name')->get();

        // Get all active customers for the company
        $customers = Customer::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all active tax types
        $taxTypes = TaxType::active()->orderBy('sort_order')->get();

        // Get all active item classifications
        $itemClassifications = ItemClassification::active()->orderBy('sort_order')->get();

        return view('user-app.invoices.create_v3', compact('company', 'nextInvoiceNumber', 'defaultTaxRates', 'states', 'customers', 'taxTypes', 'itemClassifications'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get default tax rates from company settings (should always have defaults from Company model boot)
        $defaultTaxRates = $company->default_tax_rates ?? [];

        // If items_json is provided, use it instead of individual item inputs
        if ($request->has('items_json')) {
            $items = json_decode($request->items_json, true);
            $request->merge(['items' => $items]);
        }

        $validated = $this->validationService->validateWebRequest($request);

        // Format phone number with +60 prefix
        $customerPhone = $validated['customer_phone'];
        // Remove any existing prefix or leading zeros
        $customerPhone = preg_replace('/^\+?60/', '', $customerPhone);
        $customerPhone = ltrim($customerPhone, '0');
        // Add +60 prefix
        $customerPhone = '+60'.$customerPhone;

        // Find or create customer by phone number or TIN (both are unique keys)
        $customer = null;
        if ($validated['customer_id']) {
            // If customer was selected from dropdown, use that
            $customer = Customer::find($validated['customer_id']);
        } else {
            // First, try to find existing customer by TIN for this company (TIN takes precedence)
            if (!empty($validated['customer_tin'])) {
                $customer = Customer::where('company_id', $user->company_id)
                    ->where('tin', $validated['customer_tin'])
                    ->first();

                if ($customer) {
                    \Log::info("Found existing customer by TIN {$validated['customer_tin']}: {$customer->name} (ID: {$customer->id})");
                }
            }

            // If no customer found by TIN, try to find by phone number
            if (!$customer) {
                $customer = Customer::where('company_id', $user->company_id)
                    ->where('phone', $customerPhone)
                    ->first();

                if ($customer) {
                    \Log::info("Found existing customer by phone {$customerPhone}: {$customer->name} (ID: {$customer->id})");
                }
            }

            // If still not found, create new customer
            if (! $customer) {
                $customer = Customer::create([
                    'company_id' => $user->company_id,
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'phone' => $customerPhone,
                    'street_address' => $validated['customer_street_address'] ?? null,
                    'city' => $validated['customer_city'] ?? null,
                    'state_id' => $validated['customer_state_id'] ?? null,
                    'postal_code' => $validated['customer_postal_code'] ?? null,
                    'country' => $validated['customer_country'] ?? 'MYS',
                    'tin' => $validated['customer_tin'] ?? null,
                    'document_type' => $validated['customer_document_type'] ?? null,
                    'document_number' => $validated['customer_document_number'] ?? null,
                    'is_active' => true,
                ]);
            }
        }

        // Update validated data with customer_id and formatted phone
        $validated['customer_id'] = $customer ? $customer->id : null;
        $validated['customer_phone'] = $customerPhone;

        $invoice = $this->creationService->createFromWebData($validated, $company->id, $user->id, $defaultTaxRates);

        return redirect()->route('user.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice, MyInvoisSdkService $sdk)
    {
        $this->authorize('view', $invoice);

        \Log::info('Show method called', [
            'invoice_id' => $invoice->id,
            'invoice_uuid' => $invoice->uuid,
            'invoice_company_id' => $invoice->company_id,
            'user_company_id' => auth()->user()->company_id,
            'is_ajax' => request()->ajax(),
            'headers' => request()->headers->all(),
        ]);

        $invoice->load('items', 'company', 'customer.state');

        // For invoices submitted to LHDN, fetch latest status
        $lhdnDocumentDetails = null;
        if ($invoice->lhdn_uuid && in_array($invoice->lhdn_status, ['submitted', 'accepted', 'valid', 'invalid', 'cancelled'])) {
            $lhdnDocumentDetails = $sdk->getDocumentStatus($invoice);

            // Update local status if we got a response
            if ($lhdnDocumentDetails && isset($lhdnDocumentDetails['status'])) {
                $lhdnStatus = $lhdnDocumentDetails['status'];
                $newLocalStatus = match ($lhdnStatus) {
                    'Valid' => 'valid',
                    'Invalid' => 'invalid',
                    'Cancelled' => 'cancelled',
                    default => 'submitted'
                };

                if ($newLocalStatus !== $invoice->lhdn_status) {
                    $invoice->update(['lhdn_status' => $newLocalStatus]);
                }
            }
        }

        // Return JSON for AJAX requests (used by create form to load original invoice data)
        if (request()->ajax()) {
            // Additional security check: ensure the invoice belongs to the user's company
            if ($invoice->company_id !== auth()->user()->company_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'customer_id' => $invoice->customer_id,
                'customer' => $invoice->customer,
                'currency' => $invoice->currency,
                'subtotal' => $invoice->subtotal,
                'tax_amount' => $invoice->tax_amount,
                'discount_amount' => $invoice->discount_amount,
                'total_amount' => $invoice->total_amount,
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'item_classification_id' => $item->item_classification_id,
                    ];
                }),
            ]);
        }

        return view('user-app.invoices.show', compact('invoice'));
    }

    public function getInvoiceData($id)
    {
        $invoice = Invoice::findOrFail($id);

        // Ensure the invoice belongs to the user's company
        $user = auth()->user();

        \Log::info('Invoice data request', [
            'user_id' => $user ? $user->id : 'null',
            'user_company_id' => $user ? $user->company_id : 'null',
            'invoice_id' => $invoice->id,
            'invoice_company_id' => $invoice->company_id,
            'is_authenticated' => auth()->check(),
        ]);

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to access this feature.',
            ], 401);
        }

        if (!$user->company_id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Your account is not associated with a company. Please contact support.',
            ], 403);
        }

        if ($user->company_id !== $invoice->company_id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You can only access invoices from your own company.',
                'debug' => [
                    'user_company_id' => $user->company_id,
                    'invoice_company_id' => $invoice->company_id,
                ],
            ], 403);
        }

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $invoice->due_date,
            'customer_id' => $invoice->customer_id,
            'customer' => $invoice->customer,
            'currency' => $invoice->currency,
            'subtotal' => $invoice->subtotal,
            'tax_amount' => $invoice->tax_amount,
            'discount_amount' => $invoice->discount_amount,
            'total_amount' => $invoice->total_amount,
            'items' => $invoice->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'item_classification_id' => $item->item_classification_id,
                ];
            }),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Only allow editing if invoice is in draft, rejected, or invalid status
        $editableStatuses = ['draft', 'rejected', 'invalid'];
        if (! in_array($invoice->lhdn_status, $editableStatuses)) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Cannot edit invoice that has been accepted or submitted.');
        }

        $invoice->load('items', 'company');

        // Get all Malaysian states
        $states = State::orderBy('name')->get();

        // Get default tax rates from company settings (should always have defaults from Company model boot)
        $defaultTaxRates = $invoice->company->default_tax_rates ?? [];

        // Get all active customers for the company
        $customers = Customer::where('company_id', $invoice->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all active tax types
        $taxTypes = TaxType::active()->orderBy('sort_order')->get();

        // Get all active item classifications
        $itemClassifications = ItemClassification::active()->orderBy('sort_order')->get();

        return view('user-app.invoices.edit', compact('invoice', 'states', 'defaultTaxRates', 'customers', 'taxTypes', 'itemClassifications'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Get default tax rates from company settings
        $defaultTaxRates = $invoice->company->default_tax_rates ?? [];

        // Only allow editing if invoice is in draft, rejected, or invalid status
        $editableStatuses = ['draft', 'rejected', 'invalid'];
        if (! in_array($invoice->lhdn_status, $editableStatuses)) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Cannot edit invoice that has been accepted or submitted.');
        }

        // Prevent document type changes for documents that have been submitted to LHDN
        if ($invoice->lhdn_status !== 'draft' && isset($request->document_type) && $request->document_type !== $invoice->document_type) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Cannot change document type for invoices that have been submitted to LHDN.');
        }

        // If items_json is provided, use it instead of individual item inputs
        if ($request->has('items_json')) {
            $items = json_decode($request->items_json, true);
            $request->merge(['items' => $items]);
        }

        $validated = $this->validationService->validateWebUpdateRequest($request);

        // Recalculate totals
        $subtotal = 0;
        $totalTax = 0;

        foreach ($validated['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $taxAmount = $lineTotal * ($item['tax_rate'] / 100);

            $subtotal += $lineTotal;
            $totalTax += $taxAmount;
        }

        $discountAmount = (float) ($validated['discount_amount'] ?? 0);
        $total = max(0, ($subtotal + $totalTax) - $discountAmount);

        // Format phone number with +60 prefix
        $customerPhone = $validated['customer_phone'] ?? null;
        if ($customerPhone) {
            // Remove any existing prefix or leading zeros
            $customerPhone = preg_replace('/^\+?60/', '', $customerPhone);
            $customerPhone = ltrim($customerPhone, '0');
            // Add +60 prefix
            $customerPhone = '+60'.$customerPhone;
        }

        // Build full address for backward compatibility
        $addressParts = array_filter([
            $validated['customer_street_address'] ?? null,
            $validated['customer_city'] ?? null,
            $validated['customer_state_id'] ? State::find($validated['customer_state_id'])?->name : null,
            $validated['customer_postal_code'] ?? null,
            ($validated['customer_country'] ?? 'MYS') === 'MYS' ? 'MYS' : $validated['customer_country'],
        ]);
        $fullAddress = implode(', ', $addressParts);

        // Update customer information if customer exists
        if ($invoice->customer_id) {
            $invoice->customer->update([
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'phone' => $customerPhone,
                'street_address' => $validated['customer_street_address'] ?? null,
                'city' => $validated['customer_city'] ?? null,
                'state_id' => $validated['customer_state_id'] ?? null,
                'postal_code' => $validated['customer_postal_code'] ?? null,
                'country' => $validated['customer_country'] ?? 'MYS',
                'tin' => $validated['customer_tin'] ?? null,
                'document_type' => $validated['customer_document_type'] ?? null,
                'document_number' => $validated['customer_document_number'] ?? null,
            ]);
        }

        // Update invoice
        $invoice->update([
            'invoice_number' => $validated['invoice_number'],
            'customer_id' => $validated['customer_id'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'invoice_status' => $validated['invoice_status'],
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
        ]);

        // Delete existing items and create new ones
        $invoice->items()->delete();

        foreach ($validated['items'] as $index => $item) {
            // Find the tax type ID based on the selected tax rate
            $taxTypeId = null;
            foreach ($defaultTaxRates as $defaultRate) {
                if ($defaultRate['value'] == $item['tax_rate']) {
                    $taxTypeId = $defaultRate['tax_type_id'] ?? null;
                    break;
                }
            }

            // Use provided classification or default company classification
            $classificationId = $item['item_classification_id'] ?? $invoice->company->default_item_classification_id;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_type_id' => $taxTypeId,
                'item_classification_id' => $classificationId,
                'line_total' => $item['quantity'] * $item['unit_price'],
                'sort_order' => $index + 1,
            ]);
        }

        return redirect()->route('user.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // Only allow deletion if invoice is in draft status
        if ($invoice->lhdn_status !== 'draft') {
            return redirect()->route('user.invoices.index')
                ->with('error', 'Cannot delete invoice that has been submitted.');
        }

        $invoice->delete();

        return redirect()->route('user.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->invoice_status === 'paid') {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('info', 'Invoice is already marked as paid.');
        }

        $invoice->update([
            'invoice_status' => 'paid',
        ]);

        return redirect()->route('user.invoices.show', $invoice)
            ->with('success', 'Invoice marked as paid successfully.');
    }

    public function submit(Invoice $invoice, MyInvoisSdkService $sdk)
    {
        $this->authorize('update', $invoice);

        // Only allow LHDN submission if invoice is paid
        if ($invoice->invoice_status !== 'paid') {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Invoice must be marked as paid before submitting to LHDN.');
        }

        // Only allow LHDN submission if lhdn status allows resubmission (draft, rejected, or invalid)
        if (! in_array($invoice->lhdn_status, ['draft', 'rejected', 'invalid'])) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Invoice has already been submitted to LHDN and cannot be resubmitted.');
        }

        // Load customer with state and items with classification for LHDN submission
        $invoice->load('customer.state', 'items.itemClassification');

        try {
            // Call submitInvoiceViaClient to submit invoice to LHDN
            $response = $sdk->submitInvoiceViaClient($invoice);
        } catch (\Exception $e) {
            // Handle submission errors gracefully
            $errorMessage = $e->getMessage();

            // Parse the error if it's a JSON response
            if (str_contains($errorMessage, 'Body: ')) {
                $bodyPart = strstr($errorMessage, 'Body: ');
                $json = json_decode(substr($bodyPart, 6), true);
                if ($json && isset($json['error'])) {
                    $errorMessage = $json['error'];
                }
            }

            $invoice->update([
                'lhdn_status' => 'rejected',
                'lhdn_error_message' => $errorMessage,
                'lhdn_response' => json_encode(['error' => $errorMessage]),
                'submitted_by' => Auth::id(),
                'lhdn_submitted_at' => now(),
            ]);

            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Invoice submission to LHDN failed: ' . $errorMessage);
        }

        // Assign the response data to local variables
        $submissionUID = $response['submissionUID'] ?? null;
        $acceptedDocuments = $response['acceptedDocuments'] ?? [];
        $rejectedDocuments = $response['rejectedDocuments'] ?? [];

        // Log variables to console
        \Log::info('LHDN Submit Response - submissionUID: '.$submissionUID);
        \Log::info('LHDN Submit Response - acceptedDocuments: ', $acceptedDocuments);
        \Log::info('LHDN Submit Response - rejectedDocuments: ', $rejectedDocuments);

        // Check if submission was successful
        $hasAccepted = ! empty($acceptedDocuments);
        $hasRejected = ! empty($rejectedDocuments);

        if ($hasAccepted || $hasRejected) {
            // Process the final response with accepted/rejected documents
            $sdk->processSubmissionResponse(['response' => $response]);

            $invoice->update([
                'lhdn_response' => json_encode($response),
                'submitted_by' => Auth::id(),
                'lhdn_submitted_at' => now(),
            ]);

            if ($hasAccepted && ! $hasRejected) {
                $invoice->update([
                    'lhdn_status' => 'accepted', // Update status to accepted
                    'lhdn_error_message' => null, // Clear any previous error messages
                ]);

                return redirect()->route('user.invoices.show', $invoice)
                    ->with('success', 'Invoice accepted by LHDN successfully.');
            } elseif ($hasRejected) {
                // Submission has rejected documents - processSubmissionResponse will set status to rejected
                $errorMessages = [];
                foreach ($rejectedDocuments as $rejected) {
                    if (isset($rejected['error']['details'])) {
                        foreach ($rejected['error']['details'] as $detail) {
                            $errorMessages[] = $detail['message'] ?? 'Unknown error';
                        }
                    } elseif (isset($rejected['error']['message'])) {
                        $errorMessages[] = $rejected['error']['message'];
                    }
                }

                $invoice->update([
                    'lhdn_response' => json_encode($response),
                    'lhdn_error_message' => implode('; ', $errorMessages),
                    'submitted_by' => Auth::id(),
                    'lhdn_submitted_at' => now(),
                ]);

                return redirect()->route('user.invoices.show', $invoice)
                    ->with('error', 'Invoice submission to LHDN was rejected: '.implode('; ', $errorMessages));
            }
        } else {
            // Unexpected response - no accepted or rejected documents
            $invoice->update([
                'lhdn_response' => json_encode($response),
                'submitted_by' => Auth::id(),
                'lhdn_submitted_at' => now(),
            ]);

            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Unexpected response from LHDN. Please check the invoice and try again.');
        }
    }

    public function bulkSubmit(Request $request, MyInvoisSdkService $sdk)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|integer|exists:invoices,id',
        ]);

        $invoiceIds = $request->input('invoice_ids');
        $user = Auth::user();

        // Get invoices that belong to the current user's company and are in draft status
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->where('company_id', $user->currentCompany->id)
            ->where('lhdn_status', 'draft')
            ->with(['company', 'items', 'customer.state'])
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->route('user.invoices.index')
                ->with('error', 'No valid invoices found for submission. Make sure they are in draft status and belong to your company.');
        }

        // Check if any invoices were filtered out
        if ($invoices->count() !== count($invoiceIds)) {
            $foundIds = $invoices->pluck('id')->toArray();
            $missingIds = array_diff($invoiceIds, $foundIds);

            return redirect()->route('user.invoices.index')
                ->with('error', 'Some invoices could not be found or are not eligible for submission. Missing/Invalid IDs: '.implode(', ', $missingIds));
        }

        try {
            // Submit all invoices in batch
            $response = $sdk->submitMultipleInvoices($invoices->all());

            // Process the batch submission response
            $this->processBatchSubmissionResponse($response, $invoices);

            $acceptedCount = $response['acceptedDocuments'] ? count($response['acceptedDocuments']) : 0;
            $rejectedCount = $response['rejectedDocuments'] ? count($response['rejectedDocuments']) : 0;

            if ($acceptedCount > 0 && $rejectedCount === 0) {
                return redirect()->route('user.invoices.index')
                    ->with('success', "All {$acceptedCount} invoices accepted by LHDN successfully.");
            } elseif ($rejectedCount > 0) {
                $errorMessages = [];
                foreach ($response['rejectedDocuments'] as $rejected) {
                    if (isset($rejected['error']['details'])) {
                        foreach ($rejected['error']['details'] as $detail) {
                            $errorMessages[] = $detail['message'] ?? 'Unknown error';
                        }
                    } elseif (isset($rejected['error']['message'])) {
                        $errorMessages[] = $rejected['error']['message'];
                    }
                }

                return redirect()->route('user.invoices.index')
                    ->with('warning', "{$acceptedCount} invoices accepted, {$rejectedCount} rejected. Rejected invoices: ".implode('; ', array_unique($errorMessages)));
            } else {
                return redirect()->route('user.invoices.index')
                    ->with('error', 'Unexpected response from LHDN. Please check the invoices and try again.');
            }

        } catch (\Exception $e) {
            \Log::error('Bulk invoice submission failed: '.$e->getMessage());

            return redirect()->route('user.invoices.index')
                ->with('error', 'Failed to submit invoices to LHDN: '.$e->getMessage());
        }
    }

    protected function processBatchSubmissionResponse(array $response, $invoices)
    {
        $submissionUid = $response['submissionUID'] ?? null;

        // Create a map of invoice numbers to invoices for quick lookup
        $invoiceMap = $invoices->keyBy('invoice_number');

        // Process accepted documents
        if (isset($response['acceptedDocuments'])) {
            foreach ($response['acceptedDocuments'] as $document) {
                $invoice = $invoiceMap->get($document['invoiceCodeNumber']);

                if ($invoice) {
                    $invoice->update([
                        'lhdn_uuid' => $document['uuid'],
                        'lhdn_status' => 'accepted',
                        'lhdn_submission_id' => $submissionUid,
                        'lhdn_response' => json_encode($response),
                        'lhdn_error_message' => null, // Clear any previous error messages
                        'submitted_by' => Auth::id(),
                        'lhdn_submitted_at' => now(),
                    ]);

                    Log::channel('myinvois')->info('Invoice updated as accepted in batch', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'uuid' => $document['uuid'],
                        'submission_uid' => $submissionUid,
                    ]);
                }
            }
        }

        // Process rejected documents
        if (isset($response['rejectedDocuments'])) {
            foreach ($response['rejectedDocuments'] as $document) {
                $invoice = $invoiceMap->get($document['invoiceCodeNumber']);

                if ($invoice) {
                    $errorMessages = [];
                    if (isset($document['error']['details'])) {
                        foreach ($document['error']['details'] as $detail) {
                            $errorMessages[] = $detail['message'] ?? 'Unknown error';
                        }
                    } elseif (isset($document['error']['message'])) {
                        $errorMessages[] = $document['error']['message'];
                    }

                    if (empty($errorMessages)) {
                        $errorMessages = ['Rejected by LHDN'];
                    }

                    $invoice->update([
                        'lhdn_status' => 'rejected',
                        'lhdn_submission_id' => $submissionUid,
                        'lhdn_response' => json_encode($response),
                        'lhdn_error_message' => implode('; ', $errorMessages),
                        'submitted_by' => Auth::id(),
                        'lhdn_submitted_at' => now(),
                    ]);

                    Log::channel('myinvois')->info('Invoice updated as rejected in batch', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'submission_uid' => $submissionUid,
                    ]);
                }
            }
        }
    }

    public function checkStatus(Invoice $invoice, MyInvoisSdkService $sdk)
    {
        $this->authorize('update', $invoice);

        if ($invoice->lhdn_status === 'draft') {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Invoice has not been submitted to LHDN yet.');
        }

        $lhdnDocumentDetails = $sdk->getDocumentStatus($invoice);

        if (! $lhdnDocumentDetails) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('error', 'Failed to retrieve status from LHDN. Please try again later.');
        }

        // Update local status based on LHDN response
        if (isset($lhdnDocumentDetails['status'])) {
            $lhdnStatus = $lhdnDocumentDetails['status'];
            $newLocalStatus = match ($lhdnStatus) {
                'Valid' => 'accepted',
                'Invalid' => 'rejected',
                'Cancelled' => 'cancelled',
                default => $invoice->lhdn_status
            };

            if ($newLocalStatus !== $invoice->lhdn_status) {
                $invoice->update(['lhdn_status' => $newLocalStatus]);
            }
        }

        return redirect()->route('user.invoices.show', $invoice)
            ->with('success', 'Status updated successfully from LHDN.');
    }

    public function pdf(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        try {
            return $pdfService->generate($invoice);
        } catch (\Exception $e) {
            \Log::error('PDF Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return response('PDF generation failed: '.$e->getMessage(), 500);
        }
    }

    public function preview(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        return $pdfService->generatePreview($invoice);
    }

    public function bulkUploadTemplate()
    {
        $excelPath = storage_path('app/public/templates/invoice_bulk_upload_template.xlsx');
        $csvPath = storage_path('app/public/templates/invoice_bulk_upload_template_invoice_details.csv');

        if (file_exists($excelPath)) {
            $filename = 'invoice_bulk_upload_template.xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $filePath = $excelPath;
        } elseif (file_exists($csvPath)) {
            $filename = 'invoice_bulk_upload_template_invoice_details.csv';
            $contentType = 'text/csv';
            $filePath = $csvPath;
        } else {
            abort(404, 'Template file not found. Please contact administrator.');
        }

        // Force download with response()->download()
        return response()->download($filePath, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    public function bulkUploadProcess(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $import = new BulkInvoiceImport;

            Excel::import($import, $file);

            $invoiceData = $import->getInvoiceData();
            $lineItemData = $import->getLineItemData();

            // Validate data integrity before proceeding
            $validationErrors = $this->validateBulkUploadData($invoiceData, $lineItemData);

            if (! empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validation failed. Please check your Excel file.',
                    'errors' => $validationErrors,
                ], 422);
            }

            // Store the data in session for creation and submission
            session([
                'bulk_upload_invoice_data' => $invoiceData,
                'bulk_upload_line_item_data' => $lineItemData,
            ]);

            // Prepare data for preview with converted dates
            $previewInvoiceData = array_map(function ($invoice) {
                $invoice['Invoice Date'] = $this->convertExcelDate($invoice['Invoice Date']);
                $invoice['Due Date'] = $this->convertExcelDate($invoice['Due Date']);

                return $invoice;
            }, $invoiceData);

            return response()->json([
                'success' => true,
                'invoice_count' => count($invoiceData),
                'line_item_count' => count($lineItemData),
                'invoice_data' => $previewInvoiceData,
                'line_item_data' => $lineItemData,
                'message' => 'Excel file processed successfully. Review the invoices and click "Submit All Invoices" to create and submit them.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk upload processing error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing Excel file: '.$e->getMessage(),
            ], 422);
        }
    }

    public function bulkCreateAndSubmit(Request $request, MyInvoisSdkService $sdk)
    {
        // Get data from session (uploaded during bulk upload process)
        $invoiceData = session('bulk_upload_invoice_data', []);
        $lineItemData = session('bulk_upload_line_item_data', []);

        \Log::info('Bulk submit attempt', [
            'invoice_data_count' => count($invoiceData),
            'line_item_data_count' => count($lineItemData),
            'session_keys' => array_keys(session()->all()),
        ]);

        if (empty($invoiceData)) {
            return response()->json([
                'success' => false,
                'message' => 'No invoice data found. Please upload an Excel file first.',
            ], 422);
        }

        $user = Auth::user();
        if (! $user || ! $user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'User or company not found.',
            ], 422);
        }

        $createdInvoices = [];
        $errors = [];

        // Track if creation was successful
        $creationSuccessful = true;

        try {
            // Use database transaction to ensure atomicity
            \DB::beginTransaction();

            // Create invoices from the processed data
            foreach ($invoiceData as $index => $invoiceRow) {
                try {
                    \Log::info("Creating invoice {$index}", ['data' => $invoiceRow]);

                    // Map Excel column names to API field names
                    // Convert Excel serial dates to Y-m-d format
                    $invoiceDate = $this->convertExcelDate($invoiceRow['Invoice Date']);
                    $dueDate = $this->convertExcelDate($invoiceRow['Due Date']);

                    // Format phone number with +60 prefix for customer lookup
                    $customerPhone = $invoiceRow['Customer Phone'] ?? null;
                    if ($customerPhone) {
                        // Remove any existing prefix or leading zeros
                        $customerPhone = preg_replace('/^\+?60/', '', $customerPhone);
                        $customerPhone = ltrim($customerPhone, '0');
                        // Add +60 prefix
                        $customerPhone = '+60'.$customerPhone;
                    }

                    // Find or create customer by TIN (check existing by TIN, create if not found)
                    $customerTin = $invoiceRow['Customer TIN'] ?? null;
                    $customer = null;

                    if ($customerTin) {
                        // Try to find existing customer by TIN for this company
                        $customer = Customer::where('company_id', $user->company_id)
                            ->where('tin', $customerTin)
                            ->first();

                        if ($customer) {
                            \Log::info("Found existing customer by TIN {$customerTin}: {$customer->name} (ID: {$customer->id})");
                        } else {
                            \Log::info("Customer with TIN {$customerTin} not found, creating new customer");
                        }
                    }

                    // If no TIN or customer not found by TIN, create new customer
                    if (! $customer) {
                        $customer = Customer::create([
                            'company_id' => $user->company_id,
                            'name' => $invoiceRow['Customer Name'] ?? null,
                            'email' => $invoiceRow['Customer Email'] ?? null,
                            'phone' => $customerPhone,
                            'street_address' => $invoiceRow['Customer Street Address'] ?? null,
                            'city' => $invoiceRow['Customer City'] ?? null,
                            'state_id' => null, // State from Excel is text, need to map or leave null
                            'postal_code' => $invoiceRow['Customer Postal Code'] ?? null,
                            'country' => $invoiceRow['Customer Country'] ?? 'MYS',
                            'tin' => $customerTin,
                            'document_type' => $invoiceRow['Customer Document Type'] ?? null,
                            'document_number' => $invoiceRow['Customer Document Number'] ?? null,
                            'is_active' => true,
                        ]);
                        \Log::info("Created new customer: {$customer->name} (ID: {$customer->id}) with TIN {$customerTin}");
                    }

                    $mappedData = [
                        'invoice_number' => $invoiceRow['Invoice Number'] ?? null,
                        'invoice_date' => $invoiceDate,
                        'due_date' => $dueDate,
                        'customer_id' => $customer ? $customer->id : null,
                        'customer_name' => $invoiceRow['Customer Name'] ?? null,
                        'customer_tin' => $invoiceRow['Customer TIN'] ?? null,
                        'customer_document_type' => $invoiceRow['Customer Document Type'] ?? null,
                        'customer_document_number' => $invoiceRow['Customer Document Number'] ?? null,
                        'customer_email' => $invoiceRow['Customer Email'] ?? null,
                        'customer_phone' => $customerPhone,
                        'customer_street_address' => $invoiceRow['Customer Street Address'] ?? null,
                        'customer_city' => $invoiceRow['Customer City'] ?? null,
                        'customer_state' => $invoiceRow['Customer State'] ?? null,
                        'customer_postal_code' => $invoiceRow['Customer Postal Code'] ?? null,
                        'customer_country' => $invoiceRow['Customer Country'] ?? 'MYS',
                        'currency' => $invoiceRow['Currency'] ?? 'MYR',
                        'subtotal' => floatval($invoiceRow['Subtotal'] ?? 0),
                        'tax_amount' => floatval($invoiceRow['Tax Amount'] ?? 0),
                        'discount_amount' => floatval($invoiceRow['Discount Amount'] ?? 0),
                        'total_amount' => floatval($invoiceRow['Total Amount'] ?? 0),
                        'payment_method' => trim($invoiceRow['Payment Method '] ?? ''), // Note: extra space in Excel
                        'notes' => $invoiceRow['Notes'] ?? null,
                    ];

                    \Log::info('Mapped invoice data', ['mapped' => $mappedData]);

                    // Create the invoice using existing creation service (customer already exists)
                    $invoice = $this->creationService->createFromApiData($mappedData, $user->company_id, $user->id);

                    // Add line items to the invoice using the original invoice number from Excel
                    $originalInvoiceNumber = $invoiceRow['Invoice Number'];
                    $relatedLineItems = array_filter($lineItemData, function ($item) use ($originalInvoiceNumber) {
                        return $item['Invoice Number'] === $originalInvoiceNumber;
                    });

                    \Log::info("Line items for invoice {$originalInvoiceNumber}", [
                        'line_item_count' => count($relatedLineItems),
                        'available_invoice_numbers' => array_unique(array_column($lineItemData, 'Invoice Number')),
                        'line_items' => $relatedLineItems,
                    ]);

                    // Check if invoice has any line items
                    if (empty($relatedLineItems)) {
                        \Log::warning("Invoice {$originalInvoiceNumber} has no matching line items", [
                            'invoice_data' => $invoiceRow,
                            'all_line_item_invoice_numbers' => array_unique(array_column($lineItemData, 'Invoice Number')),
                        ]);
                    }

                    foreach ($relatedLineItems as $lineItem) {
                        $taxType = TaxType::where('code', $lineItem['Tax Type Code'])->first();
                        $itemClassification = ItemClassification::where('code', $lineItem['Item Classification Code'])->first();

                        $createdItem = InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'description' => $lineItem['Description'],
                            'quantity' => floatval($lineItem['Quantity'] ?? 1),
                            'unit_price' => floatval($lineItem['Unit Price'] ?? 0),
                            'tax_rate' => floatval($lineItem['Tax Rate'] ?? 0),
                            'tax_type_id' => $taxType ? $taxType->id : null,
                            'item_classification_id' => $itemClassification ? $itemClassification->id : null,
                            'tax_amount' => floatval(($lineItem['Quantity'] ?? 1) * ($lineItem['Unit Price'] ?? 0) * (($lineItem['Tax Rate'] ?? 0) / 100)),
                            'discount_amount' => floatval($lineItem['Discount Amount'] ?? 0),
                            'line_total' => floatval($lineItem['Line Total'] ?? (($lineItem['Quantity'] ?? 1) * ($lineItem['Unit Price'] ?? 0))),
                        ]);

                        \Log::info('Line item created', [
                            'item_id' => $createdItem->id,
                            'invoice_id' => $invoice->id,
                            'description' => $createdItem->description,
                        ]);
                    }

                    $createdInvoices[] = $invoice->id;

                    \Log::info('Invoice created successfully', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'line_items_count' => $invoice->items()->count(),
                    ]);

                } catch (\Exception $e) {
                    $errorMsg = "Failed to create invoice {$invoiceRow['Invoice Number']}: ".$e->getMessage();
                    $errors[] = $errorMsg;
                    \Log::error("Bulk invoice creation error for {$invoiceRow['Invoice Number']}: ".$e->getMessage(), [
                        'invoice_data' => $invoiceRow,
                        'exception' => $e->getTraceAsString(),
                    ]);

                    // Mark creation as failed
                    $creationSuccessful = false;
                    break; // Stop processing further invoices
                }
            }

            // Only proceed with submission if all invoices were created successfully
            if ($creationSuccessful && ! empty($createdInvoices)) {
                try {
                    // Load all created invoices with their line items for batch submission
                    $invoices = Invoice::with('items', 'customer.state')->whereIn('id', $createdInvoices)->get();

                    // Mark all invoices as paid before submission
                    foreach ($invoices as $invoice) {
                        $invoice->update(['invoice_status' => 'paid']);
                    }

                    \Log::info('Loaded invoices for submission', [
                        'invoice_count' => $invoices->count(),
                        'invoices' => $invoices->map(function ($inv) {
                            return [
                                'id' => $inv->id,
                                'number' => $inv->invoice_number,
                                'items_count' => $inv->items->count(),
                                'items' => $inv->items->toArray(),
                            ];
                        }),
                    ]);

                    // Use submitMultipleInvoices for efficient batch submission
                    $result = $sdk->submitMultipleInvoices($invoices->all());

                    // Process the submission response to update LHDN status in database
                    $sdk->processSubmissionResponse(['response' => $result]);

                    // Update all submitted invoices with submission metadata
                    foreach ($invoices as $invoice) {
                        $invoice->update([
                            'lhdn_response' => json_encode($result),
                            'submitted_by' => $user->id,
                            'lhdn_submitted_at' => now(),
                        ]);
                    }

                    $submissionResults = [
                        'batch_result' => $result,
                        'submitted_count' => count($invoices),
                        'invoice_ids' => $createdInvoices,
                        'invoice_numbers' => $invoices->pluck('invoice_number')->toArray(),
                    ];

                    // All operations successful - commit the transaction
                    \DB::commit();

                    // Clear session data after successful creation and submission
                    session()->forget(['bulk_upload_invoice_data', 'bulk_upload_line_item_data']);

                    return response()->json([
                        'success' => true,
                        'created_count' => count($createdInvoices),
                        'submitted_count' => $submissionResults['submitted_count'],
                        'errors' => $errors,
                        'message' => 'Successfully created and submitted '.count($createdInvoices).' invoice(s) to LHDN.',
                    ]);

                } catch (\Exception $e) {
                    // Submission failed - rollback all database changes
                    \DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice creation and submission failed: '.$e->getMessage().'. All changes have been rolled back.',
                        'errors' => $errors,
                    ], 422);
                }
            } else {
                // No invoices created successfully - rollback
                \DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invoice creation failed: '.implode('; ', $errors),
                    'errors' => $errors,
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('Bulk create and submit error: '.$e->getMessage());

            // Clear session data on any error
            session()->forget(['bulk_upload_invoice_data', 'bulk_upload_line_item_data']);

            return response()->json([
                'success' => false,
                'message' => 'Error during bulk creation and submission: '.$e->getMessage(),
                'errors' => $errors,
            ], 422);
        }
    }

    /**
     * Validate bulk upload data for integrity
     */
    private function validateBulkUploadData(array $invoiceData, array $lineItemData): array
    {
        $errors = [];

        // Get all invoice numbers from invoice data
        $invoiceNumbers = array_column($invoiceData, 'Invoice Number');
        $invoiceNumbers = array_map('strval', array_filter($invoiceNumbers));

        // Get all invoice numbers referenced in line items
        $lineItemInvoiceNumbers = array_column($lineItemData, 'Invoice Number');
        $lineItemInvoiceNumbers = array_map('strval', array_filter($lineItemInvoiceNumbers));

        // Check 1: All line items must reference existing invoice numbers
        $invalidLineItemInvoices = array_diff($lineItemInvoiceNumbers, $invoiceNumbers);
        if (! empty($invalidLineItemInvoices)) {
            $errors[] = 'The following invoice numbers in line items do not exist in the invoice list: '.
                       implode(', ', array_unique($invalidLineItemInvoices)).'. '.
                       'Please ensure all line items reference valid invoice numbers from the invoice sheet.';
        }

        // Check 2: All invoices must have at least one line item
        foreach ($invoiceNumbers as $invoiceNumber) {
            $matchingLineItems = array_filter($lineItemInvoiceNumbers, function ($lineItemInvoice) use ($invoiceNumber) {
                return $lineItemInvoice === $invoiceNumber;
            });

            if (empty($matchingLineItems)) {
                $errors[] = "Invoice '{$invoiceNumber}' has no line items. Each invoice must have at least one line item.";
            }
        }

        // Check 3: Basic data validation
        if (empty($invoiceData)) {
            $errors[] = 'No invoice data found. Please ensure the invoice sheet has data starting from row 3.';
        }

        if (empty($lineItemData)) {
            $errors[] = 'No line item data found. Please ensure the line item sheet has data starting from row 3.';
        }

        // Check 4: Required fields validation
        foreach ($invoiceData as $index => $invoice) {
            $invoiceNum = $invoice['Invoice Number'] ?? '';
            if (empty($invoiceNum)) {
                $errors[] = 'Invoice at row '.($index + 3).' is missing an invoice number.';
            }
            if (empty($invoice['Customer Name'] ?? '')) {
                $errors[] = "Invoice '{$invoiceNum}' is missing a customer name.";
            }
            if (empty($invoice['Customer TIN'] ?? '')) {
                $errors[] = "Invoice '{$invoiceNum}' is missing a customer TIN. TIN is required for bulk upload to prevent duplicate customers.";
            }
        }

        foreach ($lineItemData as $index => $lineItem) {
            $invoiceNum = $lineItem['Invoice Number'] ?? '';
            if (empty($invoiceNum)) {
                $errors[] = 'Line item at row '.($index + 3).' is missing an invoice number.';
            }
            if (empty($lineItem['Description'] ?? '')) {
                $errors[] = "Line item for invoice '{$invoiceNum}' is missing a description.";
            }
        }

        return $errors;
    }

    /**
     * Convert Excel serial date to Y-m-d format
     */
    private function convertExcelDate($excelDate)
    {
        if (! $excelDate || ! is_numeric($excelDate)) {
            return null;
        }

        $excelDate = (int) $excelDate;

        // Excel dates start from 1900-01-01 (serial number 1)
        // Excel incorrectly treats 1900 as a leap year, so dates after 1900-02-28 are off by 1
        $days = $excelDate - 1;
        if ($excelDate > 60) {
            $days -= 1; // Correct for the leap year bug
        }

        $excelEpoch = strtotime('1900-01-01');
        $timestamp = $excelEpoch + ($days * 86400);

        return date('Y-m-d', $timestamp);
    }
}
