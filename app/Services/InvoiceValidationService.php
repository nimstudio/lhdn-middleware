<?php

namespace App\Services;

use Illuminate\Http\Request;

class InvoiceValidationService
{
    /**
     * Get validation rules for invoice creation via API
     */
    public function getApiValidationRules(): array
    {
        return [
            'invoices' => 'required|array',
            'invoices.*.document_type' => 'nullable|in:invoice,credit_note,debit_note',
            'invoices.*.original_invoice_id' => 'nullable|exists:invoices,id',
            'invoices.*.invoice_number' => 'required|string|max:50',
            'invoices.*.invoice_date' => 'required|date',
            'invoices.*.customer_name' => 'required|filled|string|max:200',
            'invoices.*.customer_tin' => 'required|filled|string|max:50',
            'invoices.*.customer_phone' => 'required|filled|string|max:20',
            'invoices.*.customer_street_address' => 'required|filled|string|max:200',
            'invoices.*.customer_city' => 'required|filled|string|max:100',
            'invoices.*.customer_state' => 'required|filled|string|max:100',
            'invoices.*.customer_postal_code' => 'required|filled|string|max:5',
            'invoices.*.customer_country' => 'required|filled|string|max:3',
            'invoices.*.customer_document_type' => 'required|filled|in:BRN,NRIC,PASSPORT,ARMY',
            'invoices.*.customer_document_number' => 'required|filled|string|max:100',
            'invoices.*.currency' => 'required|string|max:3',
            'invoices.*.total_amount' => 'required|numeric|min:0',
            'invoices.*.items' => 'required|array|min:1',
            'invoices.*.items.*.description' => 'required|string|max:200',
            'invoices.*.items.*.quantity' => 'required|numeric|min:0',
            'invoices.*.items.*.unit_price' => 'required|numeric|min:0',
            'invoices.*.items.*.line_total' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get validation rules for invoice via web form
     * Uses store() validation as default, with optional overrides for update
     */
    public function getWebValidationRules(bool $isUpdate = false): array
    {
        $rules = [
            'document_type' => ['nullable', 'in:invoice,credit_note,debit_note,self_billed_invoice'],
            'original_invoice_id' => ['nullable', 'exists:invoices,id'],
            'invoice_number' => ['required', 'string', 'max:50'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_street_address' => ['nullable', 'string', 'max:255'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'customer_state_id' => ['nullable', 'exists:states,id'],
            'customer_postal_code' => ['nullable', 'string', 'max:10'],
            'customer_country' => ['nullable', 'string', 'max:3'],
            'customer_tin' => ['nullable', 'string', 'max:50'],
            'customer_document_type' => ['nullable', 'in:BRN,NRIC,PASSPORT,ARMY'],
            'customer_document_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:invoice_date'],
            'invoice_status' => ['required', 'in:draft,pending,paid,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.item_classification_id' => ['nullable', 'exists:item_classifications,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ];

        // Apply update-specific overrides
        if ($isUpdate) {
            $rules['customer_tin'] = ['nullable', 'string', 'max:50'];
            $rules['customer_document_type'] = ['nullable', 'in:BRN,NRIC,PASSPORT,ARMY'];
            $rules['customer_document_number'] = ['nullable', 'string', 'max:100'];
            $rules['customer_street_address'] = ['required', 'string', 'max:255'];
            $rules['customer_state_id'] = ['required', 'exists:states,id'];
            $rules['customer_postal_code'] = ['required', 'string', 'max:10'];
        }

        return $rules;
    }

    /**
     * Validate API request
     */
    public function validateApiRequest(Request $request): array
    {
        return $request->validate($this->getApiValidationRules());
    }

    /**
     * Validate web request
     */
    public function validateWebRequest(Request $request): array
    {
        return $request->validate($this->getWebValidationRules());
    }

    /**
     * Validate web update request
     */
    public function validateWebUpdateRequest(Request $request): array
    {
        return $request->validate($this->getWebValidationRules(true));
    }
}
