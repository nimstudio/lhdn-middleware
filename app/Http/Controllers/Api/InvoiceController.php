<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
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
     * Push invoice data - accepts array of invoice data from merchant and automatically submits to LHDN
     *
     * Invoices are created as 'draft', then automatically marked as 'paid' and submitted to LHDN.
     * The response includes the LHDN submission result for each invoice.
     *
     * Expected request format:
     * {
     *   "invoices": [
     *     {
     *       "document_type": "01", // or "01 - Invoice" (code extraction supported)
     *       "billing_start": "2025-01-01",
     *       "billing_end": "2025-01-31",
     *       "original_invoice": "ABC12345-6789-0123-4567-890123456789", // LHDN UUID of original invoice
     *       "invoice_number": "INV-2025-0001",
     *       "invoice_date": "2025-01-15",
     *       "customer_name": "ABC Sdn Bhd",
     *       "customer_tin": "ABC123456789",
     *       "customer_email": "billing@abc.com", // optional
     *       "customer_phone": "+60123456789",
     *       "customer_street_address": "123 Main Street",
     *       "customer_city": "Kuala Lumpur",
     *       "customer_state": "10", // or "10 - Selangor" (code extraction supported)
     *       "customer_postal_code": "50000",
     *       "customer_country": "MYS",
     *       "customer_document_type": "BRN",
     *       "customer_document_number": "202501010000",
     *       "currency": "MYR", // optional
     *       "subtotal": 1000.00, // optional
     *       "tax_amount": 100.00, // optional
     *       "discount_amount": 0.00, // optional
     *       "total_amount": 1100.00,
     *       "payment_method": "cash", // optional
     *       "notes": "Thank you for your business", // optional
     *       "items": [
     *         {
     *           "description": "Product A",
     *           "quantity": 10,
     *           "unit_price": 100.00,
     *           "tax_rate": 6.00,
     *           "tax_type": "SR", // or "06 - Not Applicable" (code extraction supported)
     *           "classification_code": "001", // or "022 - Others" (code extraction supported)
     *           "tax_amount": 60.00, // optional
     *           "discount_amount": 0.00, // optional
     *           "line_total": 1000.00,
     *           "total_amount": 1060.00 // optional
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
        $submissionErrors = [];

        foreach ($request->input('invoices') as $index => $invoiceData) {
            try {
                $invoice = $this->creationService->createFromApiData($invoiceData, $companyId, $userId);

                // Mark invoice as paid and attempt LHDN submission
                $invoice->update(['invoice_status' => 'paid']);

                // Load necessary relationships for LHDN submission
                $invoice->load('customer.state', 'items.itemClassification', 'originalInvoice');

                $submissionResult = $this->submitInvoiceToLHDN($invoice, $userId);

                $createdInvoices[] = [
                    'uuid' => $invoice->uuid,
                    'invoice_number' => $invoice->invoice_number,
                    'lhdn_status' => $invoice->lhdn_status,
                    'lhdn_uuid' => $invoice->lhdn_uuid,
                    'long_id' => $invoice->long_id,
                    'submission_result' => $submissionResult,
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
            'message' => 'Invoices created and submitted to LHDN',
            'created_count' => count($createdInvoices),
            'invoices' => $createdInvoices,
            'errors' => $errors,
        ], 201);
    }

    /**
     * Submit a single invoice to LHDN
     */
    private function submitInvoiceToLHDN(Invoice $invoice, int $userId): array
    {
        try {
            // Call submitInvoiceViaClient to submit invoice to LHDN
            $response = $this->myInvoisSdkService->submitInvoiceViaClient($invoice);
        } catch (\Exception $e) {
            // Handle submission errors gracefully
            $errorMessage = $e->getMessage();

            // Parse the error if it's a JSON response
            if (str_contains($errorMessage, 'Body: ')) {
                $bodyPart = strstr($errorMessage, 'Body: ');
                $json = json_decode(substr($bodyPart, 6), true);
                if ($json && isset($json['error'])) {
                    $errorMessage = $json['error'];
                    // Handle if error is an array
                    if (is_array($errorMessage)) {
                        if (isset($errorMessage['details']) && is_array($errorMessage['details'])) {
                            $messages = [];
                            foreach ($errorMessage['details'] as $detail) {
                                $messages[] = $detail['message'] ?? 'Unknown error';
                            }
                            $errorMessage = implode('; ', $messages);
                        } elseif (isset($errorMessage['message'])) {
                            $errorMessage = $errorMessage['message'];
                        } else {
                            $errorMessage = json_encode($errorMessage);
                        }
                    }
                }
            }

            $invoice->update([
                'lhdn_status' => 'rejected',
                'lhdn_error_message' => $errorMessage,
                'lhdn_response' => json_encode(['error' => $errorMessage]),
                'submitted_by' => $userId,
                'lhdn_submitted_at' => now(),
            ]);

            return [
                'status' => 'failed',
                'error' => $errorMessage,
            ];
        }

        // Assign the response data to local variables - use same logic as processSubmissionResponse
        $submissionUID = $response['submissionUid'] ?? $response['submissionUID'] ?? $response['submissionId'] ?? null;
        $acceptedDocuments = $response['acceptedDocuments'] ?? [];
        $rejectedDocuments = $response['rejectedDocuments'] ?? [];

        // Log variables
        \Log::info('API LHDN Submit Response - submissionUID: '.$submissionUID);
        \Log::info('API LHDN Submit Response - acceptedDocuments: ', $acceptedDocuments);
        \Log::info('API LHDN Submit Response - rejectedDocuments: ', $rejectedDocuments);
        \Log::info('API LHDN Submit Response - full response structure: ', array_keys($response));

        // Check if submission was successful
        $hasAccepted = ! empty($acceptedDocuments);
        $hasRejected = ! empty($rejectedDocuments);

        if ($hasAccepted || $hasRejected) {
            // Process the final response
            $this->myInvoisSdkService->processSubmissionResponse(['response' => $response]);

            $invoice->update([
                'lhdn_response' => json_encode($response),
                'submitted_by' => $userId,
                'lhdn_submitted_at' => now(),
            ]);

            // Set final status based on result
            if ($hasAccepted && ! $hasRejected) {
                $invoice->update([
                    'lhdn_status' => 'accepted',
                    'lhdn_error_message' => null,
                ]);
            }

            // Reload invoice to get updated values from processSubmissionResponse
            $invoice->refresh();

            if ($hasAccepted && ! $hasRejected) {
                return [
                    'status' => 'accepted',
                    'submission_uid' => $submissionUID,
                    'message' => 'Invoice accepted by LHDN successfully.',
                ];
            } elseif ($hasRejected) {
                // Submission has rejected documents
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

                return [
                    'status' => 'rejected',
                    'submission_uid' => $submissionUID,
                    'error' => implode('; ', array_unique($errorMessages)),
                ];
            }
        } else {
            // Unexpected response
            $invoice->update([
                'lhdn_response' => json_encode($response),
                'submitted_by' => $userId,
                'lhdn_submitted_at' => now(),
            ]);

            // Reload invoice to get updated values from processSubmissionResponse
            $invoice->refresh();

            return [
                'status' => 'unknown',
                'submission_uid' => $submissionUID,
                'error' => 'Unexpected response from LHDN. Please check the invoice and try again.',
            ];
        }
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
