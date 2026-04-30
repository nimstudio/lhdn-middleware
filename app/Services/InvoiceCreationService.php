<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ItemClassification;
use App\Models\State;
use Illuminate\Support\Str;

class InvoiceCreationService
{
    /**
     * Create invoice from API data
     */
    public function createFromApiData(array $invoiceData, int $companyId, int $userId): Invoice
    {
        // Check if invoice number already exists for this company
        $existingInvoice = Invoice::where('company_id', $companyId)
            ->where('invoice_number', $invoiceData['invoice_number'])
            ->first();

        if ($existingInvoice) {
            throw new \Exception('Invoice number already exists');
        }

        // Format phone number with +60 prefix (similar to UserInvoiceController)
        $customerPhone = $invoiceData['customer_phone'];
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
                'name' => $invoiceData['customer_name'],
                'email' => $invoiceData['customer_email'] ?? null,
                'phone' => $customerPhone,
                'street_address' => $invoiceData['customer_street_address'] ?? null,
                'city' => $invoiceData['customer_city'] ?? null,
                'state_id' => null, // API doesn't provide state_id, only state name
                'postal_code' => $invoiceData['customer_postal_code'] ?? null,
                'country' => $invoiceData['customer_country'] ?? 'MYS',
                'tin' => $invoiceData['customer_tin'] ?? null,
                'document_type' => $invoiceData['customer_document_type'] ?? null,
                'document_number' => $invoiceData['customer_document_number'] ?? null,
                'is_active' => true,
            ]);
        } else {
            // Update existing customer with latest document info
            $customer->update([
                'tin' => $invoiceData['customer_tin'] ?? $customer->tin,
                'document_type' => $invoiceData['customer_document_type'] ?? $customer->document_type,
                'document_number' => $invoiceData['customer_document_number'] ?? $customer->document_number,
            ]);
        }

        // Build full address for backward compatibility
        $addressParts = array_filter([
            $invoiceData['customer_street_address'] ?? null,
            $invoiceData['customer_city'] ?? null,
            $invoiceData['customer_state'] ?? null,
            $invoiceData['customer_postal_code'] ?? null,
            ($invoiceData['customer_country'] ?? 'MYS') === 'MYS' ? 'MYS' : $invoiceData['customer_country'],
        ]);
        $fullAddress = implode(', ', $addressParts);

        // Create the invoice
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'uuid' => Str::uuid(),
            'invoice_number' => $invoiceData['invoice_number'],
            'invoice_date' => $invoiceData['invoice_date'],
            'due_date' => $invoiceData['due_date'] ?? null,
            'currency' => $invoiceData['currency'] ?? 'MYR',
            'subtotal' => $invoiceData['subtotal'] ?? 0,
            'tax_amount' => $invoiceData['tax_amount'] ?? 0,
            'discount_amount' => $invoiceData['discount_amount'] ?? 0,
            'total_amount' => $invoiceData['total_amount'] ?? 0,
            'invoice_status' => 'draft',
            'payment_method' => $invoiceData['payment_method'] ?? null,
            'notes' => $invoiceData['notes'] ?? null,
            'created_by' => $userId,
        ]);

        // Create invoice items
        $this->createInvoiceItems($invoice, $invoiceData['items'] ?? []);

        return $invoice;
    }

    /**
     * Create invoice from web form data
     */
    public function createFromWebData(array $validated, int $companyId, int $userId, array $defaultTaxRates): Invoice
    {
        // Format phone number with +60 prefix (similar to API)
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
            ]);
        }

        // Calculate totals
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

        // Use the invoice_status from the form
        $invoiceStatus = $validated['invoice_status'];

        // Build full address for backward compatibility
        $addressParts = array_filter([
            $validated['customer_street_address'] ?? null,
            $validated['customer_city'] ?? null,
            $validated['customer_state_id'] ? State::find($validated['customer_state_id'])?->name : null,
            $validated['customer_postal_code'] ?? null,
            ($validated['customer_country'] ?? 'MYS') === 'MYS' ? 'MYS' : $validated['customer_country'],
        ]);
        $fullAddress = implode(', ', $addressParts);

        // Create invoice
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'uuid' => Str::uuid(),
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'invoice_status' => $invoiceStatus,
            'payment_method' => $validated['payment_method'] ?? null,
            'lhdn_status' => 'draft',
            'created_by' => $userId,
        ]);

        // Create invoice items
        $this->createInvoiceItemsFromWeb($invoice, $validated['items'], $defaultTaxRates);

        return $invoice;
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
        foreach ($items as $index => $item) {
            // Find the tax type ID based on the selected tax rate
            $taxTypeId = null;
            foreach ($defaultTaxRates as $defaultRate) {
                if ($defaultRate['value'] == $item['tax_rate']) {
                    $taxTypeId = $defaultRate['tax_type_id'] ?? null;
                    break;
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
                'line_total' => $item['quantity'] * $item['unit_price'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
