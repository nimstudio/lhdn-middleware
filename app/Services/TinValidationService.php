<?php

namespace App\Services;

use App\Models\Company;
use App\Models\LhdnCredential;
use Klsheng\Myinvois\Service\Taxpayer\TaxPayerService;
use Illuminate\Support\Facades\Log;

class TinValidationService
{
    public function __construct(
        private MyInvoisSdkService $sdkService
    ) {}

    public function validateCompanyTin(Company $company): array
    {
        $credentials = $company->lhdnCredential;
        if (!$credentials) {
            return [
                'success' => false,
                'message' => 'No LHDN credentials found',
                'tin_status' => 'invalid'
            ];
        }

        try {
            // Ensure we have a valid token
            $credentials = $this->sdkService->ensureValidToken($credentials);

            // Get authenticated client
            $client = $this->sdkService->getClient($credentials);
            $taxpayerService = new TaxPayerService($client, $credentials->mode === 'production');

            Log::channel('myinvois')->info('TIN validation request', [
                'company_id' => $company->id,
                'registration_number' => $company->registration_number,
                'current_tin' => $company->tin_number,
            ]);

            // Search for TIN using BRN
            $sdkResponse = $taxpayerService->searchTaxPayerTin(
                '', // taxpayerName - empty for BRN search
                'BRN', // idType
                $company->registration_number, // idValue
                '2' // fileType - 2 for non-individual (company)
            );

            // Extract TIN value from response (handle both string and JSON formats)
            $sdkTin = null;
            if (!empty($sdkResponse)) {
                if (is_string($sdkResponse)) {
                    // Try to parse as JSON first
                    $decoded = json_decode($sdkResponse, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['tin'])) {
                        $sdkTin = $decoded['tin'];
                    } else {
                        // If not JSON, use the string directly
                        $sdkTin = $sdkResponse;
                    }
                } elseif (is_array($sdkResponse) && isset($sdkResponse['tin'])) {
                    $sdkTin = $sdkResponse['tin'];
                } else {
                    $sdkTin = $sdkResponse;
                }
            }

            Log::channel('myinvois')->info('TIN validation response', [
                'company_id' => $company->id,
                'sdk_response' => $sdkResponse,
                'extracted_tin' => $sdkTin,
                'tin_matches' => $sdkTin === $company->tin_number,
            ]);

            if (empty($sdkTin)) {
                // TIN not found in LHDN system
                $company->update([
                    'tin_status' => 'invalid',
                    'last_tin_check_at' => now(),
                ]);

                return [
                    'success' => false,
                    'message' => 'TIN not found in LHDN system. Please verify your BRN.',
                    'tin_status' => 'invalid'
                ];
            }

            // TIN found - check if it matches our stored TIN
            $tinMatches = $sdkTin === $company->tin_number;

            if (!$tinMatches) {
                // Check if the SDK TIN is already being used by another company
                $existingCompany = Company::where('tin_number', $sdkTin)
                    ->where('id', '!=', $company->id)
                    ->first();

                if ($existingCompany) {
                    // TIN is already used by another company
                    $company->update([
                        'tin_status' => 'invalid',
                        'last_tin_check_at' => now(),
                    ]);

                    Log::channel('myinvois')->warning('TIN conflict detected', [
                        'company_id' => $company->id,
                        'sdk_tin' => $sdkTin,
                        'existing_company_id' => $existingCompany->id,
                        'existing_company_name' => $existingCompany->name,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'The TIN returned by LHDN is already registered with another company (' . $existingCompany->name . '). Please contact support to resolve this conflict.',
                        'tin_status' => 'invalid'
                    ];
                }

                // Update with SDK TIN (it's unique)
                $company->update([
                    'tin_number' => $sdkTin,
                    'tin_status' => 'valid',
                    'tin_verified_at' => now(),
                    'tin_source' => 'sdk',
                    'last_tin_check_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'TIN updated from LHDN system',
                    'tin_status' => 'valid',
                    'updated_tin' => $sdkTin
                ];
            } else {
                // TIN matches - just update verification status
                $company->update([
                    'tin_status' => 'valid',
                    'tin_verified_at' => now(),
                    'tin_source' => 'sdk',
                    'last_tin_check_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'TIN verified successfully',
                    'tin_status' => 'valid'
                ];
            }

        } catch (\Throwable $e) {
            Log::channel('myinvois')->error('TIN validation failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            $company->update([
                'tin_status' => 'invalid',
                'last_tin_check_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'TIN validation failed: ' . $e->getMessage(),
                'tin_status' => 'invalid'
            ];
        }
    }

    public function hasValidTin(Company $company): bool
    {
        return $company->tin_status === 'valid';
    }

    public function requiresTinValidation(Company $company): bool
    {
        // Check if company has LHDN credentials but TIN is not validated
        return $company->lhdnCredential &&
               $company->lhdnCredential->status === 'active' &&
               $company->tin_status !== 'valid';
    }
}
