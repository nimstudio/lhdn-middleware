<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LhdnCredential;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Klsheng\Myinvois\Helper\MyInvoisHelper;
use Klsheng\Myinvois\MyInvoisClient;
use Klsheng\Myinvois\Ubl\AccountingParty;
use Klsheng\Myinvois\Ubl\Address;
use Klsheng\Myinvois\Ubl\AddressLine;
use Klsheng\Myinvois\Ubl\AllowanceCharge;
use Klsheng\Myinvois\Ubl\Builder\JsonDocumentBuilder;
use Klsheng\Myinvois\Ubl\CommodityClassification;
use Klsheng\Myinvois\Ubl\Constant\InvoiceTypeCodes;
use Klsheng\Myinvois\Ubl\Constant\MSICCodes;
use Klsheng\Myinvois\Ubl\Contact;
use Klsheng\Myinvois\Ubl\Country;
use Klsheng\Myinvois\Ubl\Invoice as UblInvoice;
use Klsheng\Myinvois\Ubl\InvoiceLine;
use Klsheng\Myinvois\Ubl\InvoicePeriod;
use Klsheng\Myinvois\Ubl\Item;
use Klsheng\Myinvois\Ubl\ItemPriceExtension;
use Klsheng\Myinvois\Ubl\LegalEntity;
use Klsheng\Myinvois\Ubl\LegalMonetaryTotal;
use Klsheng\Myinvois\Ubl\Party;
use Klsheng\Myinvois\Ubl\PartyIdentification;
use Klsheng\Myinvois\Ubl\PayeeFinancialAccount;
use Klsheng\Myinvois\Ubl\PaymentMeans;
use Klsheng\Myinvois\Ubl\PaymentTerms;
use Klsheng\Myinvois\Ubl\Price;
use Klsheng\Myinvois\Ubl\TaxCategory;
use Klsheng\Myinvois\Ubl\TaxScheme;
use Klsheng\Myinvois\Ubl\TaxSubTotal;
use Klsheng\Myinvois\Ubl\TaxTotal;

class MyInvoisSdkService
{
    public function getClient(LhdnCredential $credentials): MyInvoisClient
    {
        $prodMode = $credentials->mode === 'production';
        $client = new MyInvoisClient($credentials->client_id, $credentials->client_secret, $prodMode);
        if (! empty($credentials->access_token) && ! $credentials->isTokenExpired()) {
            $client->setAccessToken($credentials->access_token);
        }

        return $client;
    }

    public function authenticate(LhdnCredential $credentials): array
    {
        $client = $this->getClient($credentials);

        Log::channel('myinvois')->info('Auth request to MyInvois', [
            'company_id' => $credentials->company_id,
            'mode' => $credentials->mode,
            'client_id_prefix' => substr($credentials->client_id, 0, 6),
        ]);

        $client->login();

        // log all client details

        $accessToken = $client->getAccessToken();

        Log::channel('myinvois')->info('Auth response from MyInvois', [
            'company_id' => $credentials->company_id,
            'has_token' => ! empty($accessToken),
        ]);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            // SDK token lifetime is typically 3600s; we set a conservative expiry if not provided.
            'expires_at' => now()->addHour(),
        ];
    }

    public function ensureValidToken(LhdnCredential $credentials): LhdnCredential
    {
        Log::info('ensureValidToken called', [
            'credential_id' => $credentials->id,
            'has_token' => $credentials->access_token ? 'yes' : 'no',
            'token_expired' => $credentials->isTokenExpired(),
            'token_expires_at' => $credentials->token_expires_at,
        ]);

        if ($credentials->access_token && ! $credentials->isTokenExpired()) {
            Log::info('Token is valid, no refresh needed');

            return $credentials;
        }

        Log::info('Token expired or missing, refreshing...');
        $auth = $this->authenticate($credentials);

        Log::info('Authentication result', [
            'has_access_token' => isset($auth['access_token']),
            'has_token_type' => isset($auth['token_type']),
            'has_expires_at' => isset($auth['expires_at']),
            'auth_keys' => array_keys($auth),
        ]);

        $credentials->update([
            'access_token' => $auth['access_token'] ?? null,
            'token_type' => $auth['token_type'] ?? null,
            'last_token_refresh' => now(),
            'token_expires_at' => $auth['expires_at'] ?? now()->addHour(),
            'status' => 'active',
        ]);

        Log::info('Token updated in database', [
            'new_has_token' => $credentials->fresh()->access_token ? 'yes' : 'no',
            'new_expires_at' => $credentials->fresh()->token_expires_at,
        ]);

        return $credentials->fresh();
    }

    /**
     * Submit an invoice to LHDN using the MyInvois SDK.
     * This is an example implementation showing how to use setCountrySubentityCode.
     */
    public function submitInvoice(Invoice $invoice): array
    {
        $credentials = $invoice->company->lhdnCredential;

        if (! $credentials) {
            throw new \Exception('LHDN credentials not found for this company.');
        }

        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        Log::channel('myinvois')->info('Submitting invoice to LHDN', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->company_id,
        ]);

        // Example of how to use the SDK with proper state codes
        try {
            // Create invoice object using SDK
            $sdkInvoice = $client->createInvoice();

            // Set basic invoice information
            $sdkInvoice->setInvoiceNumber($invoice->invoice_number);
            $sdkInvoice->setInvoiceDate(new \DateTime($invoice->invoice_date));

            // Set supplier (company) information with LHDN state code
            $sdkInvoice->setSupplierName($invoice->company->name);
            $sdkInvoice->setSupplierTIN($invoice->company->tin_number);

            // Use the LHDN state code for the company
            if ($invoice->company->state && $invoice->company->state->lhdn_code) {
                $sdkInvoice->setSupplierCountrySubentityCode($invoice->company->state->lhdn_code);
            }

            // Set customer information with LHDN state code
            $sdkInvoice->setCustomerName($invoice->customer->name);

            if ($invoice->customer->tin) {
                $sdkInvoice->setCustomerTIN($invoice->customer->tin);
            }

            // Use the LHDN state code for the customer
            if ($invoice->customer->state?->lhdn_code) {
                $sdkInvoice->setCustomerCountrySubentityCode($invoice->customer->state->lhdn_code);
            }

            // Add invoice items
            foreach ($invoice->items as $item) {
                $sdkInvoice->addItem()
                    ->setDescription($item->description)
                    ->setQuantity($item->quantity)
                    ->setUnitPrice($item->unit_price)
                    ->setTaxRate($item->tax_rate);
            }

            // Submit to LHDN
            $response = $client->submitInvoice($sdkInvoice);

            Log::channel('myinvois')->info('Invoice submitted successfully to LHDN', [
                'invoice_id' => $invoice->id,
                'submission_id' => $response['submissionId'] ?? null,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to submit invoice to LHDN', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Submit an invoice to LHDN using the MyInvois Client directly.
     * Alternative method that uses the client more directly.
     */
    public function submitInvoiceViaClient(Invoice $invoice): array
    {
        $credentials = $invoice->company->lhdnCredential;

        if (! $credentials) {
            throw new \Exception('LHDN credentials not found for this company.');
        }

        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        Log::channel('myinvois')->info('Submitting invoice to LHDN via client', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->company_id,
        ]);

        try {
            // Create UBL document - EXACTLY like invoice.php
            $ublDocument = $this->createUblDocument($invoice);

            Log::channel('myinvois')->debug('UBL Document created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'ubl_document' => $ublDocument,
            ]);

            // Prepare document for submission - EXACTLY like invoice.php
            $documents = [];
            $document = MyInvoisHelper::getSubmitDocument($invoice->invoice_number, $ublDocument);
            $documents[] = $document;

            // Submit to LHDN using submitDocument
            $response = $client->submitDocument($documents);

            Log::channel('myinvois')->info('Invoice submitted successfully to LHDN via client', [
                'invoice_id' => $invoice->id,
                'response' => $response,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to submit invoice to LHDN via client', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Submit multiple invoices to LHDN in a single batch submission.
     * Uses the MyInvois Client directly to submit all invoices at once.
     */
    public function submitMultipleInvoices(array $invoices): array
    {
        if (empty($invoices)) {
            throw new \Exception('No invoices provided for submission.');
        }

        // Get credentials from the first invoice (assuming all invoices belong to the same company)
        $firstInvoice = $invoices[0];
        $credentials = $firstInvoice->company->lhdnCredential;

        if (! $credentials) {
            throw new \Exception('LHDN credentials not found for this company.');
        }

        // Validate that all invoices belong to the same company
        $companyId = $firstInvoice->company_id;
        foreach ($invoices as $invoice) {
            if ($invoice->company_id !== $companyId) {
                throw new \Exception('All invoices must belong to the same company for batch submission.');
            }
        }

        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        $invoiceIds = collect($invoices)->pluck('id')->toArray();
        $invoiceNumbers = collect($invoices)->pluck('invoice_number')->toArray();

        Log::channel('myinvois')->info('Submitting multiple invoices to LHDN via client', [
            'invoice_ids' => $invoiceIds,
            'invoice_numbers' => $invoiceNumbers,
            'company_id' => $companyId,
            'batch_size' => count($invoices),
        ]);

        try {
            // Prepare documents for submission
            $documents = [];

            foreach ($invoices as $invoice) {
                // Create UBL document for each invoice
                $ublDocument = $this->createUblDocument($invoice);

                Log::channel('myinvois')->debug('UBL Document created for batch submission', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'ubl_document' => $ublDocument,
                ]);

                // Add document to the batch
                $document = MyInvoisHelper::getSubmitDocument($invoice->invoice_number, $ublDocument);
                $documents[] = $document;
            }

            // Submit all documents to LHDN in one batch
            $response = $client->submitDocument($documents);

            Log::channel('myinvois')->info('Multiple invoices submitted successfully to LHDN via client', [
                'invoice_ids' => $invoiceIds,
                'batch_size' => count($invoices),
                'response' => $response,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to submit multiple invoices to LHDN via client', [
                'invoice_ids' => $invoiceIds,
                'batch_size' => count($invoices),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create UBL document from invoice
     */
    protected function createUblDocument(Invoice $invoice)
    {
        // Create document based on type
        switch ($invoice->document_type) {
            case 'credit_note':
                $document = new CreditNote;
                $document->setInvoiceTypeCode(InvoiceTypeCodes::CREDIT_NOTE);
                break;
            case 'debit_note':
                $document = new DebitNote;
                $document->setInvoiceTypeCode(InvoiceTypeCodes::DEBIT_NOTE);
                break;
            case 'self_billed_invoice':
                $document = new UblInvoice;
                $document->setInvoiceTypeCode(InvoiceTypeCodes::INVOICE); // Self-billed uses standard invoice type
                break;
            default:
                $document = new UblInvoice;
                $document->setInvoiceTypeCode(InvoiceTypeCodes::INVOICE);
                break;
        }

        $document->setId($invoice->invoice_number);
        $document->setIssueDateTime(new \DateTime($invoice->invoice_date));
        $document->setDocumentCurrencyCode($invoice->currency);

        // For credit/debit notes, set reference to original invoice
        if (in_array($invoice->document_type, ['credit_note', 'debit_note']) && $invoice->original_invoice_id) {
            $document->setBillingReferenceId($invoice->originalInvoice->invoice_number);
        }

        // Set supplier information using SDK classes
        $this->setSupplier($document, $invoice);

        // Set customer information using SDK classes
        $this->setCustomer($document, $invoice);

        // Set line items using SDK classes
        $this->setInvoiceLines($document, $invoice);

        // Set tax total using SDK classes
        $this->setTaxTotal($document, $invoice);

        // Set legal monetary total using SDK classes
        $this->setLegalMonetaryTotal($document, $invoice);

        // Set additional required UBL fields
        $this->setInvoicePeriod($document, $invoice);
        $this->setPaymentMeans($document, $invoice);
        $this->setPaymentTerms($document, $invoice);
        $this->setAllowanceCharges($document, $invoice);

        // Use SDK's JsonDocumentBuilder to build the document properly
        $builder = new JsonDocumentBuilder;
        $builder->setDocument($document);
        $jsonDocument = $builder->build();

        // Debug: Log the actual UBL document structure
        $documentArray = json_decode($jsonDocument, true);
        Log::info('UBL Document Debug - Full Structure', [
            'invoice_id' => $invoice->id,
            'tax_amount' => $invoice->tax_amount,
            'line_extension_amount' => $invoice->line_extension_amount,
            'has_tax_total' => isset($documentArray['TaxTotal']),
            'tax_total_structure' => $documentArray['TaxTotal'] ?? 'NOT_FOUND',
            'invoice_lines' => $documentArray['InvoiceLine'] ?? 'NOT_FOUND',
            'full_document_keys' => array_keys($documentArray),
        ]);

        return $jsonDocument;
    }

    /**
     * Set supplier information using SDK classes
     */
    protected function setSupplier($document, Invoice $invoice)
    {
        // For self-billed invoices, the customer is the supplier
        if ($invoice->document_type === 'self_billed_invoice') {
            $entity = $invoice->customer;
            $tinNumber = $entity->tin;
            $registrationNumber = $entity->document_number;
            $entityName = $entity->name;
            $entityEmail = $entity->email;
            $entityPhone = $entity->phone;
            $entityAddress = $entity->getFullAddressAttribute();
            $entityCity = $entity->city ?: 'N/A';
            $entityPostalCode = $entity->postal_code;
            $entityState = $entity->state;
            $entityCountry = $entity->country;
            $msicCode = '01111'; // Default MSIC for customer
        } else {
            $entity = $invoice->company;
            $tinNumber = $entity->tin_number;
            $registrationNumber = $entity->registration_number;
            $entityName = $entity->name;
            $entityEmail = $entity->email;
            $entityPhone = $entity->phone;
            $entityAddress = $entity->getFullAddressAttribute();
            $entityCity = $entity->city ?? 'N/A';
            $entityPostalCode = $entity->postcode;
            $entityState = $entity->state;
            $entityCountry = $entity->country;
            $msicCode = '01111'; // Default MSIC code
        }

        // Build supplier address from company address_line_1 and address_line_2
        $supplierAddress = '';
        if (! empty($company->address_line_1)) {
            $supplierAddress = $company->address_line_1;
        }
        if (! empty($company->address_line_2)) {
            $supplierAddress .= ($supplierAddress ? ' ' : '').$company->address_line_2;
        }

        $address = new Address;
        $address->setCityName($entityCity);
        $address->setPostalZone($entityPostalCode);
        $address->setStreetName($entityAddress);
        $address->setCountrySubentityCode($entityState?->lhdn_code ?: '14'); // Default to Kuala Lumpur

        // Add address lines (required by UBL)
        $addressLine = new AddressLine;
        $addressLine->setLine($entityAddress ?: 'Address not provided');
        $address->addAddressLine($addressLine);

        $country = new Country;
        // Use LHDN-compliant country code (MYS for Malaysia)
        $countryCode = $entityCountry === 'Malaysia' || $entityCountry === 'MYS' ? 'MYS' : $entityCountry;
        $country->setIdentificationCode($countryCode);
        $address->setCountry($country);

        $legalEntity = new LegalEntity;
        $legalEntity->setRegistrationName($entityName);

        $contact = new Contact;
        // Fix phone number format - ensure it starts with +60 for Malaysia
        $phoneNumber = $entityPhone;
        if (! empty($phoneNumber) && ! str_starts_with($phoneNumber, '+60')) {
            $phoneNumber = '+60'.ltrim($phoneNumber, '0');
        }
        $contact->setTelephone($phoneNumber ?: '+60123456789'); // Default if empty
        $contact->setElectronicMail($entityEmail);

        $supplier = new Party;
        $supplier->setPostalAddress($address);
        $supplier->setLegalEntity($legalEntity);
        $supplier->setContact($contact);

        // Add MSIC code (required by LHDN)
        $msicCodeDesc = MSICCodes::getDescription($msicCode);
        $supplier->setIndustryClassificationCode($msicCode, $msicCodeDesc);

        // Add party identifications
        if (! empty($tinNumber)) {
            $tinId = new PartyIdentification;
            $tinId->setId($tinNumber, 'TIN');
            $supplier->addPartyIdentification($tinId);
        }

        if (! empty($registrationNumber)) {
            $brnId = new PartyIdentification;
            $brnId->setId($registrationNumber, 'BRN');
            $supplier->addPartyIdentification($brnId);
        }

        $accountingSupplierParty = new AccountingParty;
        $accountingSupplierParty->setParty($supplier);
        $document->setAccountingSupplierParty($accountingSupplierParty);
    }

    /**
     * Set customer information using SDK classes
     */
    protected function setCustomer($document, Invoice $invoice)
    {
        // For self-billed invoices, the company is the customer
        if ($invoice->document_type === 'self_billed_invoice') {
            $entity = $invoice->company;
            $entityName = $entity->name;
            $entityEmail = $entity->email;
            $entityPhone = $entity->phone;
            $entityAddress = $entity->getFullAddressAttribute();
            $entityCity = $entity->city ?? 'N/A';
            $entityPostalCode = $entity->postcode;
            $entityState = $entity->state;
            $entityCountry = $entity->country;
            $tinNumber = $entity->tin_number;
            $registrationNumber = $entity->registration_number;
        } else {
            $entity = $invoice->customer;
            $entityName = $entity->name;
            $entityEmail = $entity->email;
            $entityPhone = $entity->phone;
            $entityAddress = $entity->getFullAddressAttribute();
            $entityCity = $entity->city ?: 'N/A';
            $entityPostalCode = $entity->postal_code;
            $entityState = $entity->state;
            $entityCountry = $entity->country;
            $tinNumber = $entity->tin;
            $registrationNumber = $entity->document_number ?: 'NA';
        }

        $address = new Address;
        $address->setCityName($entityCity);
        $address->setPostalZone($entityPostalCode);
        $address->setStreetName($entityAddress);
        $address->setCountrySubentityCode($entityState?->lhdn_code ?: '14'); // Default to Kuala Lumpur

        // Add address lines (required by UBL)
        $addressLine = new AddressLine;
        $addressLine->setLine($entityAddress ?: 'Address not provided');
        $address->addAddressLine($addressLine);

        $country = new Country;
        // Use LHDN-compliant country code (MYS for Malaysia)
        $countryCode = $entityCountry === 'Malaysia' || $entityCountry === 'MYS' ? 'MYS' : $entityCountry;
        $country->setIdentificationCode($countryCode);
        $address->setCountry($country);

        $legalEntity = new LegalEntity;
        $legalEntity->setRegistrationName($entityName);

        $contact = new Contact;
        // Fix phone number format
        $phoneNumber = $entityPhone;
        if (! empty($phoneNumber) && ! str_starts_with($phoneNumber, '+60')) {
            $phoneNumber = '+60'.ltrim($phoneNumber, '0');
        }
        $contact->setTelephone($phoneNumber ?: 'NA');
        $contact->setElectronicMail($entityEmail);

        $customer = new Party;
        $customer->setPostalAddress($address);
        $customer->setLegalEntity($legalEntity);
        $customer->setContact($contact);

        // Add party identifications
        if (! empty($tinNumber)) {
            $tinId = new PartyIdentification;
            $tinId->setId($tinNumber, 'TIN');
            $customer->addPartyIdentification($tinId);
        }

        if (! empty($registrationNumber)) {
            $brnId = new PartyIdentification;
            $brnId->setId($registrationNumber, 'BRN');
            $customer->addPartyIdentification($brnId);
        }

        $accountingCustomerParty = new AccountingParty;
        $accountingCustomerParty->setParty($customer);
        $document->setAccountingCustomerParty($accountingCustomerParty);
    }

    /**
     * Set invoice lines using SDK classes
     */
    protected function setInvoiceLines($document, Invoice $invoice)
    {
        $lineNumber = 1;
        foreach ($invoice->items as $lineItem) {
            $invoiceLine = new InvoiceLine;
            $invoiceLine->setId((string) $lineNumber);
            $invoiceLine->setInvoicedQuantity($lineItem->quantity, 'C62');

            // Use line_total as line extension amount
            $lineExtensionAmount = $lineItem->line_total ?? $lineItem->total_amount ?? ($lineItem->quantity * $lineItem->unit_price);
            $discountAmount = $lineItem->discount_amount ?? 0;
            $taxAmount = $lineItem->tax_amount ?? 0;

            $invoiceLine->setLineExtensionAmount($lineExtensionAmount);

            // Create item
            $item = new Item;
            $item->setDescription($lineItem->description);

            // Add commodity classification using user's selected classification code as CLASS (LHDN prioritizes CLASS over PTC)
            if ($lineItem->itemClassification) {
                $commodityClassification = new CommodityClassification;
                $commodityClassification->setItemClassificationCode($lineItem->itemClassification->code, 'CLASS');
                $item->addCommodityClassification($commodityClassification);

                // Debug: Log the CLASS code being added
                Log::info('Adding CLASS Commodity Classification (User Selected)', [
                    'line_item_id' => $lineItem->id,
                    'classification_code_id' => $lineItem->item_classification_id,
                    'commodity_code' => $lineItem->itemClassification->code,
                    'description' => $lineItem->itemClassification->description,
                    'code_type' => 'CLASS',
                ]);
            } elseif (! empty($lineItem->commodity_classification_code)) {
                // Fallback to old text input if no classification code selected
                $commodityClassification = new CommodityClassification;
                $commodityClassification->setItemClassificationCode($lineItem->commodity_classification_code, 'CLASS');
                $item->addCommodityClassification($commodityClassification);

                // Debug: Log the fallback CLASS code being added
                Log::info('Adding Fallback CLASS Commodity Classification', [
                    'line_item_id' => $lineItem->id,
                    'commodity_code' => $lineItem->commodity_classification_code,
                    'code_type' => 'CLASS',
                ]);
            } else {
                // Default CLASS classification if no code selected
                $commodityClassification = new CommodityClassification;
                $commodityClassification->setItemClassificationCode('011', 'CLASS');
                $item->addCommodityClassification($commodityClassification);

                // Debug: Log the default CLASS code being added
                Log::info('Adding Default CLASS Commodity Classification', [
                    'line_item_id' => $lineItem->id,
                    'commodity_code' => '011',
                    'code_type' => 'CLASS',
                ]);
            }

            $invoiceLine->setItem($item);

            // Create price
            $price = new Price;
            $price->setPriceAmount($lineItem->unit_price ?? 0);
            $invoiceLine->setPrice($price);

            // Create item price extension (required by UBL)
            $itemPriceExtension = new ItemPriceExtension;
            $itemPriceExtension->setAmount($lineExtensionAmount);
            $invoiceLine->setItemPriceExtension($itemPriceExtension);

            // Create tax total for line
            $lineTaxTotal = new TaxTotal;
            $lineTaxTotal->setTaxAmount($taxAmount);

            $taxSubTotal = new TaxSubTotal;
            $taxSubTotal->setTaxableAmount($lineExtensionAmount - $discountAmount);
            $taxSubTotal->setTaxAmount($taxAmount);
            $taxSubTotal->setPercent($lineItem->tax_rate ?? 0);

            $taxCategory = new TaxCategory;
            // Use string tax code instead of integer ID
            $taxCode = 'VAT'; // Default to VAT
            if ($lineItem->taxType && $lineItem->taxType->code) {
                $taxCode = $lineItem->taxType->code;
            }
            $taxCategory->setId($taxCode);
            $taxCategory->setPercent($lineItem->tax_rate ?? 0);

            $taxScheme = new TaxScheme;
            $taxScheme->setId('VAT');
            $taxCategory->setTaxScheme($taxScheme);

            $taxSubTotal->setTaxCategory($taxCategory);
            $lineTaxTotal->addTaxSubTotal($taxSubTotal);
            $invoiceLine->setTaxTotal($lineTaxTotal);

            $document->addInvoiceLine($invoiceLine);
            $lineNumber++;
        }
    }

    /**
     * Set tax total using SDK classes
     */
    protected function setTaxTotal($document, Invoice $invoice)
    {
        // Calculate tax amount from line items if invoice tax_amount is null/empty
        $calculatedTaxAmount = $invoice->tax_amount;
        if (empty($calculatedTaxAmount) || $calculatedTaxAmount == 0) {
            $calculatedTaxAmount = $invoice->items->sum('tax_amount');
        }

        // Debug: Log tax values before creating TaxTotal
        Log::info('Creating TaxTotal with values', [
            'invoice_id' => $invoice->id,
            'original_tax_amount' => $invoice->tax_amount,
            'calculated_tax_amount' => $calculatedTaxAmount,
            'line_extension_amount' => $invoice->line_extension_amount,
            'tax_amount_type' => gettype($calculatedTaxAmount),
            'tax_amount_is_zero' => $calculatedTaxAmount == 0,
            'tax_amount_is_null' => is_null($calculatedTaxAmount),
        ]);

        $taxTotal = new TaxTotal;
        $taxTotal->setTaxAmount($calculatedTaxAmount); // Use calculated tax amount

        $taxSubTotal = new TaxSubTotal;
        $taxSubTotal->setTaxableAmount($invoice->line_extension_amount); // Fixed: removed currency parameter
        $taxSubTotal->setTaxAmount($calculatedTaxAmount); // Use calculated tax amount
        $taxSubTotal->setPercent(6.0); // Added missing setPercent method

        $taxCategory = new TaxCategory;
        $taxCategory->setId('01');
        $taxCategory->setPercent(6.0); // Fixed: use numeric value

        $taxScheme = new TaxScheme;
        $taxScheme->setId('VAT');
        $taxCategory->setTaxScheme($taxScheme);

        $taxSubTotal->setTaxCategory($taxCategory);
        $taxTotal->addTaxSubTotal($taxSubTotal);
        $document->setTaxTotal($taxTotal);
    }

    /**
     * Set legal monetary total using SDK classes
     */
    protected function setLegalMonetaryTotal($document, Invoice $invoice)
    {
        $legalMonetaryTotal = new LegalMonetaryTotal;

        // Use subtotal as line extension amount
        $lineExtensionAmount = $invoice->subtotal ?? 0;
        $discountAmount = $invoice->discount_amount ?? 0;
        $taxAmount = $invoice->tax_amount ?? 0;
        $totalAmount = $invoice->total_amount ?? 0;

        // taxExclusiveAmount = lineExtensionAmount - discount
        $taxExclusiveAmount = $lineExtensionAmount - $discountAmount;

        // taxInclusiveAmount = taxExclusiveAmount + tax
        $taxInclusiveAmount = $taxExclusiveAmount + $taxAmount;

        $legalMonetaryTotal->setLineExtensionAmount($lineExtensionAmount);
        $legalMonetaryTotal->setTaxExclusiveAmount($taxExclusiveAmount);
        $legalMonetaryTotal->setTaxInclusiveAmount($taxInclusiveAmount);
        $legalMonetaryTotal->setPayableAmount($totalAmount);
        $document->setLegalMonetaryTotal($legalMonetaryTotal);
    }

    /**
     * Set invoice period using SDK classes
     */
    protected function setInvoicePeriod($document, Invoice $invoice)
    {
        $invoicePeriod = new InvoicePeriod;

        // Cast invoice_date to datetime
        $invoiceDate = $invoice->invoice_date instanceof \DateTime
            ? $invoice->invoice_date
            : new \DateTime($invoice->invoice_date);

        $invoicePeriod->setStartDate($invoiceDate);
        $invoicePeriod->setEndDate($invoiceDate);
        $invoicePeriod->setDescription('Invoice Period');
        $document->setInvoicePeriod($invoicePeriod);
    }

    /**
     * Set payment means using SDK classes
     */
    protected function setPaymentMeans($document, Invoice $invoice)
    {
        $payeeFinancialAccount = new PayeeFinancialAccount;
        $payeeFinancialAccount->setId('1234567890123'); // Default account ID

        $paymentMeans = new PaymentMeans;
        $paymentMeans->setPayeeFinancialAccount($payeeFinancialAccount);
        $document->setPaymentMeans($paymentMeans);
    }

    /**
     * Set payment terms using SDK classes
     */
    protected function setPaymentTerms($document, Invoice $invoice)
    {
        $paymentTerms = new PaymentTerms;
        $paymentTerms->setNote('Payment due within 30 days');
        $document->setPaymentTerms($paymentTerms);
    }

    /**
     * Set allowance charges using SDK classes
     */
    protected function setAllowanceCharges($document, Invoice $invoice)
    {
        $allowanceCharges = [];

        // Add allowance if any
        if ($invoice->allowance_total_amount > 0) {
            $allowanceCharge = new AllowanceCharge;
            $allowanceCharge->setChargeIndicator(false);
            $allowanceCharge->setAllowanceChargeReason('Discount');
            $allowanceCharge->setAmount($invoice->allowance_total_amount);
            $allowanceCharges[] = $allowanceCharge;
        }

        $document->setAllowanceCharges($allowanceCharges);
    }

    /**
     * Process submission response from LHDN and update invoices
     */
    public function processSubmissionResponse(array $response): void
    {
        $submissionUid = $response['response']['submissionUid'] ?? null;

        // Process accepted documents
        if (isset($response['response']['acceptedDocuments'])) {
            foreach ($response['response']['acceptedDocuments'] as $document) {
                $invoice = Invoice::where('invoice_number', $document['invoiceCodeNumber'])->first();

                if ($invoice) {
                    // Log all document fields for debugging
                    Log::channel('myinvois')->info('Document response fields', [
                        'document_keys' => array_keys($document),
                        'document_data' => $document,
                    ]);

                    $invoice->update([
                        'lhdn_uuid' => $document['uuid'],
                        'lhdn_internal_id' => $document['internalId'] ?? null,
                        'lhdn_status' => 'submitted', // Initially submitted, will be updated to valid/invalid later
                        'lhdn_submission_id' => $submissionUid,
                    ]);

                    Log::channel('myinvois')->info('Invoice updated as accepted', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'uuid' => $document['uuid'],
                        'internal_id' => $document['internalId'] ?? null,
                        'submission_uid' => $submissionUid,
                    ]);
                }
            }
        }

        // Process rejected documents
        if (isset($response['response']['rejectedDocuments'])) {
            foreach ($response['response']['rejectedDocuments'] as $document) {
                $invoice = Invoice::where('invoice_number', $document['invoiceCodeNumber'])->first();

                if ($invoice) {
                    $invoice->update([
                        'lhdn_status' => 'rejected',
                        'lhdn_submission_id' => $submissionUid,
                    ]);

                    Log::channel('myinvois')->info('Invoice updated as rejected', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'submission_uid' => $submissionUid,
                    ]);
                }
            }
        }
    }

    /**
     * Validate taxpayer TIN with LHDN
     */
    public function validateTaxPayerTin(string $tin, string $idType, string $idValue, LhdnCredential $credentials): array
    {
        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        Log::channel('myinvois')->info('Validating taxpayer TIN with LHDN', [
            'tin' => $tin,
            'id_type' => $idType,
            'id_value' => $idValue,
        ]);

        try {
            Log::channel('myinvois')->info('About to call validateTaxPayerTin API', [
                'tin' => $tin,
                'id_type' => $idType,
                'id_value' => $idValue,
            ]);

            // Try validateTaxPayerTin first
            $response = $client->validateTaxPayerTin($tin, $idType, $idValue);
            Log::channel('myinvois')->info('validateTaxPayerTin response received', [
                'method' => 'validateTaxPayerTin',
                'tin' => $tin,
                'response_type' => gettype($response),
                'response_value' => $response,
                'response_is_empty' => empty($response),
                'response_length' => is_string($response) ? strlen($response) : 'N/A',
            ]);

            // If validateTaxPayerTin returns empty, try searchTaxPayerTin as fallback
            if (empty($response)) {
                Log::channel('myinvois')->info('validateTaxPayerTin returned empty, trying searchTaxPayerTin', [
                    'tin' => $tin,
                ]);

                try {
                    $response = $client->searchTaxPayerTin($tin);
                    Log::channel('myinvois')->info('searchTaxPayerTin response received', [
                        'method' => 'searchTaxPayerTin',
                        'tin' => $tin,
                        'response_type' => gettype($response),
                        'response_value' => $response,
                        'response_is_empty' => empty($response),
                        'response_length' => is_string($response) ? strlen($response) : 'N/A',
                    ]);
                } catch (\Exception $e2) {
                    Log::channel('myinvois')->warning('searchTaxPayerTin also failed', [
                        'tin' => $tin,
                        'error' => $e2->getMessage(),
                    ]);
                    // Continue with empty response from validateTaxPayerTin
                }
            }

            // Log final response details
            Log::channel('myinvois')->info('TIN validation completed', [
                'tin' => $tin,
                'response_type' => gettype($response),
                'response_length' => is_string($response) ? strlen($response) : 'N/A',
                'has_content' => ! empty($response),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to validate taxpayer TIN', [
                'tin' => $tin,
                'id_type' => $idType,
                'id_value' => $idValue,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Search taxpayer TIN with LHDN
     */
    public function searchTaxPayerTin(string $taxPayerName, string $idType, string $idValue, ?string $fileType, LhdnCredential $credentials): array
    {
        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        Log::channel('myinvois')->info('Searching taxpayer TIN with LHDN', [
            'tax_payer_name' => $taxPayerName,
            'id_type' => $idType,
            'id_value' => $idValue,
            'file_type' => $fileType,
        ]);

        try {
            $response = $client->searchTaxPayerTin($taxPayerName, $idType, $idValue, $fileType);

            Log::channel('myinvois')->info('searchTaxPayerTin response received', [
                'tax_payer_name' => $taxPayerName,
                'id_type' => $idType,
                'id_value' => $idValue,
                'file_type' => $fileType,
                'response_type' => gettype($response),
                'response_length' => is_string($response) ? strlen($response) : 'N/A',
                'has_content' => ! empty($response),
            ]);

            // Try to decode if it's JSON
            if (is_string($response) && ! empty($response)) {
                $decoded = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::channel('myinvois')->info('Decoded searchTaxPayerTin JSON response', [
                        'tax_payer_name' => $taxPayerName,
                        'decoded_response' => $decoded,
                    ]);
                } else {
                    Log::channel('myinvois')->error('Failed to decode searchTaxPayerTin JSON response', [
                        'tax_payer_name' => $taxPayerName,
                        'json_error' => json_last_error_msg(),
                        'raw_response' => $response,
                    ]);
                }
            }

            return $response;
        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to search taxpayer TIN', [
                'tax_payer_name' => $taxPayerName,
                'id_type' => $idType,
                'id_value' => $idValue,
                'file_type' => $fileType,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Validate taxpayer TIN directly with LHDN API (bypassing SDK)
     */
    public function validateTaxPayerTinDirect(string $tin, string $idType, string $idValue, LhdnCredential $credentials): array
    {
        Log::info('validateTaxPayerTinDirect called', [
            'tin' => $tin,
            'id_type' => $idType,
            'id_value' => $idValue,
            'credential_id' => $credentials->id,
            'mode' => $credentials->mode,
        ]);

        $credentials = $this->ensureValidToken($credentials);

        // Determine base URL based on environment from config
        $baseUrl = Config::get('lhdn.api.base_urls.'.$credentials->mode, 'https://api.myinvois.hasil.gov.my');

        Log::channel('myinvois')->info('Direct TIN validation - Config check', [
            'mode' => $credentials->mode,
            'base_url_from_config' => $baseUrl,
            'default_fallback' => 'https://api.myinvois.hasil.gov.my',
        ]);

        // Build the API URL
        $url = $baseUrl.'/api/v1.0/taxpayer/validate/'.urlencode($tin).'?idType='.urlencode($idType).'&idValue='.urlencode($idValue);

        Log::channel('myinvois')->info('Direct TIN validation - URL built', [
            'tin' => $tin,
            'id_type' => $idType,
            'id_value' => $idValue,
            'full_url' => $url,
            'mode' => $credentials->mode,
        ]);

        Log::channel('myinvois')->info('Making direct TIN validation API call', [
            'tin' => $tin,
            'id_type' => $idType,
            'id_value' => $idValue,
            'environment' => $credentials->mode,
            'url' => $url,
            'has_token_after_refresh' => $credentials->access_token ? 'yes' : 'no',
        ]);

        try {
            Log::channel('myinvois')->info('About to create HTTP client', [
                'url' => $url,
                'has_token' => ! empty($credentials->access_token),
                'token_length' => strlen($credentials->access_token ?? ''),
            ]);

            $authHeader = 'Bearer '.$credentials->access_token;
            Log::channel('myinvois')->info('Direct TIN validation - Auth header', [
                'has_token' => ! empty($credentials->access_token),
                'token_length' => strlen($credentials->access_token ?? ''),
                'token_prefix' => substr($credentials->access_token ?? '', 0, 10).'...',
                'auth_header_length' => strlen($authHeader),
            ]);

            $httpClient = Http::withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

            Log::channel('myinvois')->info('HTTP client created successfully', [
                'client_created' => true,
                'mode' => $credentials->mode,
                'will_disable_ssl' => $credentials->mode !== 'production',
            ]);

            // Disable SSL verification for sandbox environment due to certificate issues
            if ($credentials->mode !== 'production') {
                $httpClient = $httpClient->withoutVerifying();
                Log::channel('myinvois')->warning('SSL verification disabled for sandbox environment', [
                    'tin' => $tin,
                    'environment' => $credentials->mode,
                ]);
            }

            Log::channel('myinvois')->info('About to execute HTTP GET request');
            $response = $httpClient->get($url);
            Log::channel('myinvois')->info('HTTP request completed successfully', [
                'response_received' => true,
                'status_code' => $response->status(),
            ]);

            Log::channel('myinvois')->info('Direct TIN validation API response', [
                'tin' => $tin,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'is_successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Try to decode if it's JSON
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::channel('myinvois')->info('Decoded direct TIN validation JSON response', [
                        'tin' => $tin,
                        'decoded_response' => $data,
                    ]);

                    return [
                        'status_code' => $response->status(),
                        'data' => $data,
                        'is_successful' => true,
                    ];
                } else {
                    Log::channel('myinvois')->error('Failed to decode direct TIN validation JSON response', [
                        'tin' => $tin,
                        'json_error' => json_last_error_msg(),
                        'raw_response' => $response->body(),
                    ]);

                    // For 200 status, consider valid even with invalid/empty JSON
                    return [
                        'status_code' => $response->status(),
                        'data' => null,
                        'is_successful' => true,
                        'raw_response' => $response->body(),
                    ];
                }
            } else {
                Log::channel('myinvois')->error('Direct TIN validation API call failed', [
                    'tin' => $tin,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return [
                    'error' => 'API call failed',
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                    'is_successful' => false,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Exception during direct TIN validation API call', [
                'tin' => $tin,
                'id_type' => $idType,
                'id_value' => $idValue,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel a single invoice with LHDN
     */
    public function cancelInvoice(Invoice $invoice, string $reason = 'Customer refund'): array
    {
        // Validate that the invoice is eligible for cancellation
        if ($invoice->lhdn_status !== 'valid') {
            throw new \Exception('Invoice is not in valid status and cannot be cancelled.');
        }

        if (! $invoice->lhdn_uuid && ! $invoice->lhdn_internal_id && ! $invoice->invoice_number) {
            throw new \Exception('Invoice does not have valid LHDN identifiers. It may not have been submitted successfully.');
        }

        $credentials = $invoice->company->lhdnCredential;

        if (! $credentials) {
            throw new \Exception('LHDN credentials not found for this company.');
        }

        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        // Use the LHDN UUID for cancellation - this is what the API expects
        $documentId = $invoice->lhdn_uuid;
        $identifierType = 'uuid';

        if (empty($documentId)) {
            throw new \Exception('Invoice does not have a valid LHDN UUID. It may not have been submitted successfully.');
        }

        Log::channel('myinvois')->info('Using LHDN UUID for cancellation', [
            'invoice_id' => $invoice->id,
            'uuid' => $documentId,
            'invoice_number' => $invoice->invoice_number,
        ]);

        $credentials = $this->ensureValidToken($credentials);
        $client = $this->getClient($credentials);

        try {
            // First, try to get document details to ensure we have the correct identifier
            Log::channel('myinvois')->info('Attempting to get document status before cancellation', [
                'invoice_id' => $invoice->id,
                'document_id' => $documentId,
            ]);

            $docStatus = $client->getDocumentDetail($documentId);

            // Check if document can be cancelled - only Valid status allows cancellation
            if (! isset($docStatus['status']) || $docStatus['status'] !== 'Valid') {
                $currentStatus = $docStatus['status'] ?? 'Unknown';
                throw new \Exception('Document cannot be cancelled because it is in '.$currentStatus.' status. Only Valid documents can be cancelled per LHDN requirements.');
            }

            if (isset($docStatus['status']) && ! in_array($docStatus['status'], ['Valid', 'Accepted'])) {
                throw new \Exception('Document cannot be cancelled because it is in '.$docStatus['status'].' status. Only Valid or Accepted documents can be cancelled.');
            }

            Log::channel('myinvois')->info('Document status allows cancellation', [
                'invoice_id' => $invoice->id,
                'document_status' => $docStatus['status'] ?? 'unknown',
            ]);

            // Cancel document with LHDN
            $response = $client->cancelDocument($documentId, $reason);

            Log::channel('myinvois')->info('Invoice cancellation response from LHDN', [
                'invoice_id' => $invoice->id,
                'document_id' => $documentId,
                'response' => $response,
            ]);

            // Update invoice status if cancellation was successful
            $invoice->update([
                'lhdn_status' => 'cancelled',
            ]);

            Log::channel('myinvois')->info('Invoice updated as cancelled', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'document_id' => $documentId,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to cancel invoice with LHDN', [
                'invoice_id' => $invoice->id,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get document status and details from LHDN
     */
    public function getDocumentStatus(Invoice $invoice): ?array
    {
        Log::channel('myinvois')->info('CheckStatus called for invoice', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'lhdn_uuid' => $invoice->lhdn_uuid,
            'lhdn_status' => $invoice->lhdn_status,
            'customer_tin' => $invoice->customer->tin,
            'customer_name' => $invoice->customer->name,
        ]);

        try {
            $credential = $invoice->company->LhdnCredential;
            $client = $this->getClient($credential);

            Log::channel('myinvois')->info('Making getDocumentDetail API call', [
                'invoice_id' => $invoice->id,
                'lhdn_uuid' => $invoice->lhdn_uuid,
                'company_id' => $credential->company_id,
                'environment' => $credential->mode,
            ]);

            // Use getDocumentDetail to get full document details including status
            $response = $client->getDocumentDetail($invoice->lhdn_uuid);

            Log::channel('myinvois')->info('LHDN document status response', [
                'invoice_id' => $invoice->id,
                'lhdn_uuid' => $invoice->lhdn_uuid,
                'response_type' => gettype($response),
                'response_keys' => is_array($response) ? array_keys($response) : null,
                'response_status' => is_array($response) && isset($response['status']) ? $response['status'] : 'unknown',
                'validation_status' => is_array($response) && isset($response['validationResults']['status']) ? $response['validationResults']['status'] : 'no_validation',
                'has_validation_results' => is_array($response) && isset($response['validationResults']),
            ]);

            // Log validation errors if present
            if (is_array($response) && isset($response['validationResults'])) {
                Log::channel('myinvois')->warning('LHDN validation errors found', [
                    'invoice_id' => $invoice->id,
                    'validation_results' => $response['validationResults'],
                ]);
            }

            // Update the invoice status based on LHDN validation result
            if (is_array($response) && isset($response['status'])) {
                $lhdnStatus = $response['status'];

                // Map LHDN status to our database status
                $dbStatus = match ($lhdnStatus) {
                    'Valid' => 'valid',
                    'Invalid' => 'invalid',
                    'Cancelled' => 'cancelled',
                    default => $invoice->lhdn_status // Keep existing status if unknown
                };

                // Update the invoice status if it changed
                if ($dbStatus !== $invoice->lhdn_status) {
                    $invoice->update(['lhdn_status' => $dbStatus]);
                    Log::channel('myinvois')->info('Updated invoice LHDN status from validation', [
                        'invoice_id' => $invoice->id,
                        'old_status' => $invoice->lhdn_status,
                        'new_status' => $dbStatus,
                        'lhdn_status' => $lhdnStatus,
                    ]);
                }
            } else {
                // Log when no status is found
                Log::channel('myinvois')->warning('No status found in LHDN response', [
                    'invoice_id' => $invoice->id,
                    'response_type' => gettype($response),
                    'response_keys' => is_array($response) ? array_keys($response) : null,
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::channel('myinvois')->error('Failed to get document status from LHDN', [
                'invoice_id' => $invoice->id,
                'uuid' => $invoice->lhdn_uuid,
                'error' => $e->getMessage(),
            ]);

            // Log the error for command debugging
            error_log("LHDN Status Error for invoice {$invoice->id}: {$e->getMessage()}");

            return null;
        }
    }
}
