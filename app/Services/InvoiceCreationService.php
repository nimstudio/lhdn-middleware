<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ItemClassification;
use App\Models\State;
use App\Models\TaxType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InvoiceCreationService
{


    public function createFromWebData(array $validated, int $companyId, int $userId, array $defaultTaxRates): Invoice
    {
        // Check if invoice number already exists for this company
        $existingInvoice = Invoice::where('company_id', $companyId)
            ->where('invoice_number', $validated['invoice_number'])
            ->first();

        if ($existingInvoice) {
            throw new \Exception('Invoice number already exists');
        }

        // Format phone number with +60 prefix (similar to UserInvoiceController)
        $customerPhone = $validated['customer_phone'];
        // Remove any existing prefix or leading zeros
        $customerPhone = preg_replace('/^\+?60/', '', $customerPhone);
        $customerPhone = ltrim($customerPhone, '0');
        // Add +60 prefix
        $customerPhone = '+60'.$customerPhone;

        // Find or create customer by phone number (phone is the unique key)
        $customer = Customer::where('company_id', $companyId)
            ->where('phone', $customerPhone)
            ->first();

        if (! $customer) {
            $customer = Customer::create([
                'company_id' => $companyId,
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'] ?? null,
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
        } else {
            // Update existing customer with latest document info
            $customer->update([
                'tin' => $validated['customer_tin'] ?? $customer->tin,
                'document_type' => $validated['customer_document_type'] ?? $customer->document_type,
                'document_number' => $validated['customer_document_number'] ?? $customer->document_number,
            ]);
        }

        // Get original invoice UUID if set
        $originalInvoiceUuid = null;
        if (!empty($validated['original_invoice_id'])) {
            $originalInvoice = Invoice::find($validated['original_invoice_id']);
            $originalInvoiceUuid = $originalInvoice ? $originalInvoice->uuid : null;
        }

        // Calculate totals
        $subtotal = 0;
        $totalTax = 0;
        $discountAmount = $validated['discount_amount'] ?? 0;

        foreach ($validated['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $taxAmount = $lineTotal * ($item['tax_rate'] / 100);

            $subtotal += $lineTotal;
            $totalTax += $taxAmount;
        }

        $total = max(0, ($subtotal + $totalTax) - $discountAmount);

        // Create the invoice
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'uuid' => Str::uuid(),
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'document_type' => $validated['document_type'],
            'original_invoice_id' => $validated['original_invoice_id'] ?? null,
            'original_invoice_uuid' => $originalInvoiceUuid,
            'billing_start' => $validated['billing_start'] ?? null,
            'billing_end' => $validated['billing_end'] ?? null,
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'invoice_status' => $validated['invoice_status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'lhdn_status' => 'draft',
            'created_by' => $userId,
        ]);

        // Create invoice items
        $this->createInvoiceItemsFromWeb($invoice, $validated['items'], $defaultTaxRates);

        return $invoice;
    }

    /**
     * Create invoice from API data
     */
    public function createFromApiData(array $validated, int $companyId, int $userId): Invoice
    {
        // Check if invoice number already exists for this company
        $existingInvoice = Invoice::where('company_id', $companyId)
            ->where('invoice_number', $validated['invoice_number'])
            ->first();

        if ($existingInvoice) {
            throw new \Exception('Invoice number already exists');
        }

        // Extract codes from "code - description" format
        $documentType = $this->extractCode($validated['document_type']);
        $customerStateCode = $this->extractCode($validated['customer_state']);

        // Find state by code or name
        $stateId = null;
        if (!empty($customerStateCode)) {
            $state = State::where('lhdn_code', $customerStateCode)->orWhere('name', $validated['customer_state'])->first();
            $stateId = $state ? $state->id : null;
        }

        // Format phone number with +60 prefix
        $customerPhone = $validated['customer_phone'];
        // Remove any existing prefix or leading zeros
        $customerPhone = preg_replace('/^\+?60/', '', $customerPhone);
        $customerPhone = ltrim($customerPhone, '0');
        // Add +60 prefix
        $customerPhone = '+60'.$customerPhone;

        // Find or create customer by phone number (phone is the unique key)
        $customer = Customer::where('company_id', $companyId)
            ->where('phone', $customerPhone)
            ->first();

        if (! $customer) {
            $customer = Customer::create([
                'company_id' => $companyId,
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'] ?? null,
                'phone' => $customerPhone,
                'street_address' => $validated['customer_street_address'],
                'city' => $validated['customer_city'],
                'state_id' => $stateId,
                'postal_code' => $validated['customer_postal_code'],
                'country' => $validated['customer_country'],
                'tin' => $validated['customer_tin'],
                'document_type' => $validated['customer_document_type'],
                'document_number' => $validated['customer_document_number'],
                'is_active' => true,
            ]);
        } else {
            // Update existing customer with latest document info
            $customer->update([
                'tin' => $validated['customer_tin'] ?? $customer->tin,
                'document_type' => $validated['customer_document_type'] ?? $customer->document_type,
                'document_number' => $validated['customer_document_number'] ?? $customer->document_number,
            ]);
        }

        // Get original invoice UUID if set
        $originalInvoiceUuid = null;
        $originalInvoiceId = null;
        if (!empty($validated['original_invoice'])) {
            $originalInvoice = Invoice::where('lhdn_uuid', $validated['original_invoice'])->first();
            $originalInvoiceId = $originalInvoice ? $originalInvoice->id : null;
            $originalInvoiceUuid = $validated['original_invoice'];
        }

        // Calculate totals from provided data
        $subtotal = $validated['subtotal'] ?? 0;
        $totalTax = $validated['tax_amount'] ?? 0;
        $discountAmount = $validated['discount_amount'] ?? 0;
        $total = $validated['total_amount'];

        // Create the invoice
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'uuid' => Str::uuid(),
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'document_type' => $documentType,
            'original_invoice_id' => $originalInvoiceId,
            'original_invoice_uuid' => $originalInvoiceUuid,
            'billing_start' => $validated['billing_start'] ?? null,
            'billing_end' => $validated['billing_end'] ?? null,
            'currency' => $validated['currency'] ?? 'MYR',
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'invoice_status' => 'draft', // API invoices start as draft
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'lhdn_status' => 'draft',
            'created_by' => $userId,
        ]);

        // Create invoice items from API data
        $this->createInvoiceItemsFromApi($invoice, $validated['items']);

        return $invoice;
    }

    /**
     * Create invoice items from API data
     */
    private function createInvoiceItemsFromApi(Invoice $invoice, array $items): void
    {
        \Log::info('Creating ' . count($items) . ' items for invoice ' . $invoice->id . ' from API data');
        foreach ($items as $index => $item) {
            // Extract codes from "code - description" format
            $taxTypeCode = $this->extractCode($item['tax_type'] ?? '');
            $classificationCode = $this->extractCode($item['classification_code'] ?? '');

            // Find tax type by code
            $taxTypeId = null;
            if (!empty($taxTypeCode)) {
                $taxType = TaxType::where('code', $taxTypeCode)->first();
                $taxTypeId = $taxType ? $taxType->id : null;
            }

            // Find item classification by code
            $classificationId = null;
            if (!empty($classificationCode)) {
                $classification = ItemClassification::where('code', $classificationCode)->first();
                $classificationId = $classification ? $classification->id : null;
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'tax_type_id' => $taxTypeId,
                'item_classification_id' => $classificationId,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'line_total' => $item['line_total'],
                'tax_amount' => $item['tax_amount'] ?? ($item['line_total'] * ($item['tax_rate'] ?? 0) / 100),
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Extract code from "code - description" format
     */
    private function extractCode(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Check if it contains " - " separator
        if (strpos($value, ' - ') !== false) {
            return trim(explode(' - ', $value, 2)[0]);
        }

        // Return as-is if no separator found
        return trim($value);
    }

    /**
     * Create invoice items from API data
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $sortOrder = 1;
        foreach ($items as $itemData) {
            // Get item_classification_id from classification_code if provided
            $itemClassificationId = null;
            if (! empty($itemData['classification_code'])) {
                $classification = ItemClassification::findByCode($itemData['classification_code']);
                $itemClassificationId = $classification ? $classification->id : null;
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'tax_type_id' => $itemData['tax_type_id'] ?? null,
                'item_classification_id' => $itemClassificationId,
                'tax_amount' => $itemData['tax_amount'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'line_total' => $itemData['line_total'] ?? 0,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    /**
     * Create invoice items from web form data
     */
    private function createInvoiceItemsFromWeb(Invoice $invoice, array $items, array $defaultTaxRates): void
    {
        \Log::info('Creating ' . count($items) . ' items for invoice ' . $invoice->id . ', items: ' . json_encode($items));
        foreach ($items as $index => $item) {
            \Log::info('Creating item ' . ($index + 1) . ': ' . $item['description']);
            // Find the tax type ID based on the provided tax_type_id or selected tax rate
            $taxTypeId = $item['tax_type_id'] ?? null;
            if (!$taxTypeId) {
                foreach ($defaultTaxRates as $defaultRate) {
                    if ($defaultRate['value'] == $item['tax_rate']) {
                        $taxTypeId = $defaultRate['tax_type_id'] ?? null;
                        break;
                    }
                }
            }

            // Use provided classification or default company classification
            $company = $invoice->company;
            $classificationId = $item['item_classification_id'] ?? $company->default_item_classification_id;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_type_id' => $taxTypeId,
                'item_classification_id' => $classificationId,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'line_total' => $item['line_total'] ?? ($item['quantity'] * $item['unit_price']),
                'tax_amount' => ($item['line_total'] ?? ($item['quantity'] * $item['unit_price'])) * ($item['tax_rate'] / 100),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
