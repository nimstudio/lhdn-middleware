<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceCreationService;
use App\Services\InvoiceValidationService;
use App\Services\MyInvoisSdkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceValidationService $validationService;

    protected InvoiceCreationService $creationService;

    protected MyInvoisSdkService $myInvoisSdkService;

    public function __construct(
        InvoiceValidationService $validationService,
        InvoiceCreationService $creationService,
        MyInvoisSdkService $myInvoisSdkService
    ) {
        $this->validationService = $validationService;
        $this->creationService = $creationService;
        $this->myInvoisSdkService = $myInvoisSdkService;
    }

    /**
     * Push invoice data - accepts array of invoice data from merchant
     *
     * Expected request format:
     * {
     *   "invoices": [
     *     {
     *       "invoice_number": "INV-2025-0001",
     *       "invoice_date": "2025-01-15",
     *       "due_date": "2025-02-15",
     *       "customer_name": "ABC Sdn Bhd",
     *       "customer_tin": "ABC123456789",
     *       "customer_registration_number": "202501010000",
     *       "customer_email": "billing@abc.com",
     *       "customer_phone": "+60123456789",
     *       "customer_address": "123 Main Street, Kuala Lumpur",
     *       "customer_city": "Kuala Lumpur",
     *       "customer_state": "WP",
     *       "customer_postal_code": "50000",
     *       "customer_country": "MY",
     *       "currency": "MYR",
     *       "subtotal": 1000.00,
     *       "tax_amount": 100.00,
     *       "discount_amount": 0.00,
     *       "total_amount": 1100.00,
     *       "payment_method": "cash",
     *       "notes": "Thank you for your business",
     *       "items": [
     *         {
     *           "description": "Product A",
     *           "quantity": 10,
     *           "unit_price": 100.00,
     *           "tax_rate": 6.00,
     *           "tax_type_id": 1,
     *           "item_classification_id": 1,
     *           "tax_amount": 60.00,
     *           "discount_amount": 0.00,
     *           "line_total": 1000.00,
     *           "total_amount": 1060.00
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function pushInvoice(Request $request): JsonResponse
    {
        $this->validationService->validateApiRequest($request);

        // Authenticate using API key from header
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header.',
            ], 401);
        }

        // Find company by API key
        $company = Company::where('api_key', $apiKey)->first();

        if (! $company) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.',
            ], 401);
        }

        // Ensure company has at least one user
        $firstUser = $company->users()->first();
        if (! $firstUser) {
            return response()->json([
                'success' => false,
                'message' => 'Company has no active users.',
            ], 422);
        }

        $companyId = $company->id;
        $userId = $firstUser->id;

        $createdInvoices = [];
        $errors = [];

        foreach ($request->input('invoices') as $index => $invoiceData) {
            try {
                $invoice = $this->creationService->createFromApiData($invoiceData, $companyId, $userId);

                $createdInvoices[] = [
                    'uuid' => $invoice->uuid,
                    'invoice_number' => $invoice->invoice_number,
                ];

            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'invoice_number' => $invoiceData['invoice_number'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (empty($createdInvoices)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create any invoices',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoices created successfully',
            'created_count' => count($createdInvoices),
            'invoices' => $createdInvoices,
            'errors' => $errors,
        ], 201);
    }

    /**
     * Validate taxpayer TIN with LHDN
     *
     * Expected request format:
     * {
     *   "customer_tin": "ABC123456789",
     *   "customer_document_type": "BRN",
     *   "customer_document_number": "202501010000"
     * }
     */
    public function validateTin(Request $request): JsonResponse
    {
        Log::info('TIN Validation API - Method entered', [
            'request_all' => $request->all(),
            'has_api_key' => $request->hasHeader('X-API-Key'),
            'api_key_value' => $request->header('X-API-Key'),
        ]);

        $request->validate([
            'customer_tin' => 'required|string|max:20',
            'customer_document_type' => 'required|string|max:50',
            'customer_document_number' => 'required|string|max:50',
        ]);

        // Authenticate using API key from header
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header.',
            ], 401);
        }

        // Find company by API key
        $company = Company::where('api_key', $apiKey)->first();

        if (! $company) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.',
            ], 401);
        }

        // Check if company has LHDN credentials
        $lhdnCredential = $company->lhdnCredential;

        Log::info('TIN Validation API - Company credentials check', [
            'company_id' => $company->id,
            'company_name' => $company->name,
            'has_lhdn_credential' => $lhdnCredential ? 'yes' : 'no',
            'credential_id' => $lhdnCredential?->id,
            'credential_mode' => $lhdnCredential?->mode,
            'has_access_token' => $lhdnCredential && $lhdnCredential->access_token ? 'yes' : 'no',
            'token_expired' => $lhdnCredential?->isTokenExpired(),
        ]);

        if (! $lhdnCredential) {
            return response()->json([
                'success' => false,
                'message' => 'LHDN credentials not configured for this company.',
            ], 422);
        }

        try {
            // Force token refresh to ensure we have a fresh token
            $lhdnCredential = $this->myInvoisSdkService->ensureValidToken($lhdnCredential);

            Log::info('API TIN Validation - After token refresh', [
                'credential_id' => $lhdnCredential->id,
                'has_token' => $lhdnCredential->access_token ? 'yes' : 'no',
                'token_expired' => $lhdnCredential->isTokenExpired(),
                'token_expires_at' => $lhdnCredential->token_expires_at,
            ]);

            $result = $this->myInvoisSdkService->validateTaxPayerTinDirect(
                $request->input('customer_tin'),
                $request->input('customer_document_type'),
                $request->input('customer_document_number'),
                $lhdnCredential
            );

            $customerTin = $request->input('customer_tin');
            $isValid = isset($result['status_code']) && $result['status_code'] == 200;

            if ($isValid) {
                $message = 'TIN validation successful';
            } else {
                if (isset($result['status_code']) && $result['status_code'] == 404) {
                    $message = 'TIN not found in LHDN system';
                } elseif (isset($result['error'])) {
                    $message = 'TIN validation failed: '.$result['error'];
                } else {
                    $message = 'TIN validation failed: Unknown error';
                }
            }

            return response()->json([
                'customer_tin' => $customerTin,
                'status_code' => $result['status_code'] ?? null,
                'message' => $message,
            ], $isValid ? 200 : 422);

        } catch (\Exception $e) {
            Log::error('API TIN Validation failed', [
                'error' => $e->getMessage(),
                'tin' => $request->input('customer_tin'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'TIN validation failed: '.$e->getMessage(),
            ], 422);
        }
    }
}
