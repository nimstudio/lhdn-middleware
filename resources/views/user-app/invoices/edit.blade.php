@extends('layouts.user-app', ['title' => 'Edit Invoice #' . $invoice->invoice_number])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
<style>
    .choices__inner {
        @apply !px-4 !py-2.5 border-2 border-gray-300 rounded-lg bg-white;
        min-height: 42px;
    }
    .choices__list--dropdown {
        @apply border border-gray-300 rounded-lg shadow-xl;
    }
    .choices__item--selectable {
        @apply !px-4 !py-3;
    }
</style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto" x-data="invoiceForm()" x-init="init()">
        <!-- Header + Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Invoice #{{ $invoice->invoice_number }}</h1>
                    <p class="text-gray-600 mt-1">Make changes to your invoice. Only draft invoices can be edited.</p>
                </div>
                <div class="flex items-center gap-2">
                        <a href="{{ route('user.invoices.show', $invoice) }}" onclick="return confirm('Are you sure you want to cancel editing? Any unsaved changes will be lost.')" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    <button type="submit" form="invoice-form" @click="setStatus('draft')" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gray-700 hover:bg-gray-800">Save as Draft</button>
                    <button type="submit" form="invoice-form" @click="setStatus('pending')" name="action" value="update" class="px-5 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700">Update & Continue</button>
                </div>
            </div>
        </div>

        <form id="invoice-form" method="POST" action="{{ route('user.invoices.update', $invoice) }}" @submit="syncItems()" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Body: From & Bill To side-by-side, then wide Items -->
            <div class="p-6 space-y-8">
                <!-- Top: From and Bill To in two columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- From -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-5">
                        <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3">From</h3>
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1.5">
                                <p class="text-sm font-semibold text-gray-900">{{ $invoice->company->name }}</p>
                                <p class="text-sm text-gray-600">{{ $invoice->company->address_line_1 }}@if($invoice->company->address_line_2), {{ $invoice->company->address_line_2 }}@endif</p>
                                <p class="text-sm text-gray-600">{{ $invoice->company->postcode }} {{ $invoice->company->city }}, {{ $invoice->company->state->name ?? '' }}</p>
                            </div>
                            <div class="text-right text-sm text-gray-600 space-y-1">
                                <p><span class="text-xs text-gray-500">Phone:</span> {{ $invoice->company->phone }}</p>
                                <p><span class="text-xs text-gray-500">Email:</span> {{ $invoice->company->email }}</p>
                                <p><span class="text-xs text-gray-500">SSM:</span> {{ $invoice->company->registration_number }}</p>
                                <p><span class="text-xs text-gray-500">TIN:</span> {{ $invoice->company->tin_number }}</p>
                            </div>
                        </div>

                        <!-- Invoice Meta inside From card -->
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Document Type</label>
                                <select name="document_type" {{ $invoice->lhdn_status !== 'draft' ? 'disabled' : '' }}
                                        class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('document_type') border-red-500 @enderror">
                                    <option value="invoice" {{ $invoice->document_type == 'invoice' ? 'selected' : '' }}>Invoice</option>
                                    <option value="credit_note" {{ $invoice->document_type == 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                                    <option value="debit_note" {{ $invoice->document_type == 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                                    <option value="self_billed_invoice" {{ $invoice->document_type == 'self_billed_invoice' ? 'selected' : '' }}>Self-Billed Invoice</option>
                                </select>
                                @if($invoice->lhdn_status !== 'draft')
                                    <p class="text-xs text-gray-500 mt-1">Cannot change document type for submitted documents</p>
                                @endif
                            </div>
                            <div x-show="documentType === 'credit_note' || documentType === 'debit_note'">
                                <label class="block text-xs font-medium text-gray-600">Original Invoice</label>
                                <select name="original_invoice_id"
                                        @change="loadOriginalInvoice($event.target.value)"
                                        class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('original_invoice_id') border-red-500 @enderror">
                                    <option value="">Select Original Invoice</option>
                                    @foreach(\App\Models\Invoice::where('company_id', $invoice->company_id)->orderBy('created_at', 'desc')->limit(50)->get() as $originalInvoice)
                                        <option value="{{ $originalInvoice->id }}" data-uuid="{{ $originalInvoice->uuid }}" {{ old('original_invoice_id', $invoice->original_invoice_id) == $originalInvoice->id ? 'selected' : '' }}>
                                            {{ $originalInvoice->invoice_number }} - {{ $originalInvoice->customer->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice No. <span class="text-red-500">*</span></label>
                                <input type="text" name="invoice_number" required x-model="invoiceNumber"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono @error('invoice_number') border-red-500 @enderror"
                                       value="{{ old('invoice_number', $invoice->invoice_number) }}">
                                @error('invoice_number')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice Date <span class="text-red-500">*</span></label>
                                <input type="date" name="invoice_date" required x-model="invoiceDate" @change="autoCalculateDueDate()"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('invoice_date') border-red-500 @enderror"
                                       value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}">
                                @error('invoice_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Due Date <span class="text-red-500">*</span></label>
                                <input type="date" name="due_date" required x-model="dueDate"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('due_date') border-red-500 @enderror"
                                       value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}">
                                @error('due_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Currency</label>
                                <input type="text" value="MYR" readonly class="mt-2 w-full !px-4 !py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-700">
                            </div>
                        </div>

                        <!-- Invoice Status & Payment Mode -->
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice Status <span class="text-red-500">*</span></label>
                                <select name="invoice_status" x-model="invoiceStatus" class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white @error('invoice_status') border-red-500 @enderror">
                                    <option value="draft" {{ old('invoice_status', $invoice->invoice_status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="pending" {{ old('invoice_status', $invoice->invoice_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('invoice_status', $invoice->invoice_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="cancelled" {{ old('invoice_status', $invoice->invoice_status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('invoice_status')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">"Save as Draft" sets to Draft, "Update & Continue" sets to Pending</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Mode of Payment</label>
                                <select name="payment_method" x-model="paymentMethod" class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                    <option value="Bank Transfer" {{ old('payment_method', $invoice->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Cash" {{ old('payment_method', $invoice->payment_method) === 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Credit Card" {{ old('payment_method', $invoice->payment_method) === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="E-Wallet" {{ old('payment_method', $invoice->payment_method) === 'E-Wallet' ? 'selected' : '' }}>E-Wallet</option>
                                    <option value="Cheque" {{ old('payment_method', $invoice->payment_method) === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bill To -->
                    <div class="bg-white rounded-xl border-2 border-primary-100 p-5" x-data="customerSearchData" x-init="init()">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs uppercase tracking-wide font-semibold text-primary-700">Bill To <span class="text-red-500">*</span></h3>
                            <a href="{{ route('user.customers.create') }}" target="_blank" class="inline-flex items-center text-xs text-primary-600 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                New Customer
                            </a>
                        </div>

                        <!-- Customer Dropdown with Search -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Select Customer</label>
                            <select id="customer-select-edit" class="w-full !px-4 !py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option value="">-- Change customer or keep current details --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-customer="{{ json_encode([
                                                'id' => $customer->id,
                                                'name' => $customer->name,
                                                'email' => $customer->email,
                                                'phone' => $customer->phone,
                                                'street_address' => $customer->street_address,
                                                'city' => $customer->city,
                                                'state_id' => $customer->state_id,
                                                'postal_code' => $customer->postal_code,
                                                'country' => $customer->country,
                                                'tin' => $customer->tin,
                                                'document_type' => $customer->document_type,
                                                'document_number' => $customer->document_number,
                                            ]) }}"
                                            {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} @if($customer->email)- {{ $customer->email }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose a different customer or keep the current information</p>
                        </div>

                        <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" required x-model="customerName" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Customer Co. / Person" value="{{ old('customer_name', $invoice->customer ? $invoice->customer->name : '') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" name="customer_email" x-model="customerEmail" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="customer@email.com" value="{{ old('customer_email', $invoice->customer ? $invoice->customer->email : '') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_phone" required x-model="customerPhone" @input="formatPhone($event)" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_phone') border-red-500 @enderror" placeholder="+60 12-345 6789 or international format" value="{{ old('customer_phone', $invoice->customer->phone ?: '') }}">
                                @error('customer_phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Enter full international phone number (e.g., +60123456789, +1234567890)</p>
                            </div>

                            <!-- Address Breakdown -->
                            <div class="pt-2 border-t border-gray-200">
                                <h4 class="text-xs font-semibold text-gray-700 mb-3">Address</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                                        <input type="text" name="customer_street_address" required x-model="customerStreetAddress" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_street_address') border-red-500 @enderror" placeholder="Street address" value="{{ old('customer_street_address', $invoice->customer->street_address) }}">
                                        @error('customer_street_address')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">City</label>
                                            <input type="text" name="customer_city" x-model="customerCity" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="City" value="{{ old('customer_city', $invoice->customer->city) }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                                            <select name="customer_state_id" required x-model="customerStateId" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_state_id') border-red-500 @enderror">
                                                <option value="">Select state</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->id }}" {{ old('customer_state_id', $invoice->customer->state_id) == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer_state_id')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
                                            <input type="text" name="customer_postal_code" required x-model="customerPostalCode" maxlength="10" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_postal_code') border-red-500 @enderror" placeholder="12345" value="{{ old('customer_postal_code', $invoice->customer->postal_code) }}">
                                            @error('customer_postal_code')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Country</label>
                                            <input type="text" name="customer_country" x-model="customerCountry" value="{{ old('customer_country', $invoice->customer->country ?? 'MYS') }}" readonly class="block w-full !px-3.5 !py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700" placeholder="MYS">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- eInvoice additional fields -->
                            <div class="pt-2 border-t border-gray-200">
                                <h4 class="text-xs font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    eInvoice Information (Optional)
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">TIN</label>
                                        @php
                                            $customerTin = $invoice->customer ? $invoice->customer->tin : '';
                                        @endphp
                                        <input type="text" name="customer_tin" x-model="customerTin" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_tin') border-red-500 @enderror" placeholder="Tax Identification Number" value="{{ old('customer_tin', $customerTin) }}">
                                        @error('customer_tin')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Document Type</label>
                                        @php
                                            $documentType = $invoice->customer ? $invoice->customer->document_type : '';
                                        @endphp
                                        <select name="customer_document_type" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_document_type') border-red-500 @enderror">
                                            <option value="">Select type</option>
                                            <option value="BRN" {{ $documentType === 'BRN' ? 'selected' : '' }}>BRN</option>
                                            <option value="NRIC" {{ $documentType === 'NRIC' ? 'selected' : '' }}>NRIC</option>
                                            <option value="PASSPORT" {{ $documentType === 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                                            <option value="ARMY" {{ $documentType === 'ARMY' ? 'selected' : '' }}>Army ID</option>
                                        </select>
                                        @error('customer_document_type')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Document Number</label>
                                        @php
                                            $documentNumber = $invoice->customer ? $invoice->customer->document_number : '';
                                        @endphp
                                        <input type="text" name="customer_document_number" class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_document_number') border-red-500 @enderror" placeholder="Document number" value="{{ $documentNumber }}">
                                        @error('customer_document_number')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items wide -->
                <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">Items <span class="ml-2 text-xs font-normal text-gray-500">(<span x-text="items.length"></span>)</span></h3>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                                    Add Item
                                </button>
                            </div>
                        </div>

                        <div class="hidden md:block overflow-x-auto -mx-3" style="overflow: visible;">
                            <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-visible">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-600">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Rate</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Tax %</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600">Classification</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Line Total</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <template x-for="(item, idx) in items" :key="idx">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <input type="text" x-model="item.description" required class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Item description">
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <input type="number" min="1" x-model.number="item.quantity" class="w-24 !px-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">RM</span>
                                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-32 !pl-10 !pr-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div x-data="{ showCustom: false }">
                                                    <select x-show="!showCustom" x-model.number="item.tax_rate" @change="if ($event.target.value === 'custom') { showCustom = true; item.tax_rate = 0; $nextTick(() => $refs.customInput?.focus()); }"
                                                            class="min-w-[140px] !px-2 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                                        @foreach($defaultTaxRates as $rate)
                                                            <option value="{{ $rate['value'] }}">{{ $rate['value'] }}%@if(!empty($rate['label'])) - {{ $rate['label'] }}@endif</option>
                                                        @endforeach
                                                        <option value="custom">Custom</option>
                                                    </select>
                                                    <div x-show="showCustom" class="flex items-center gap-1">
                                                        <input x-ref="customInput" type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate"
                                                               class="w-16 !px-2 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                               placeholder="0">
                                                        <span class="text-xs text-gray-500">%</span>
                                                        <button type="button" @click="showCustom = false; item.tax_rate = 0;" class="text-gray-400 hover:text-gray-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                             </td>
                                         <td class="px-4 py-3 text-center">
                                              <x-searchable-select
                                                  :options="$itemClassifications->map(fn($c) => ['id' => $c->id, 'label' => $c->code . ' - ' . substr($c->description, 0, 30), 'code' => $c->code, 'description' => $c->description])->toArray()"
                                                  value-key="id"
                                                  label-key="label"
                                                  :search-keys="['code', 'description']"
                                                  @input="items[idx].item_classification_id = $event.detail"
                                                  class="inline-block"
                                              />
                                         </td>
                                             <td class="px-4 py-3 text-right">
                                                 <span class="font-medium text-gray-900 text-sm" x-text="formatMoney(lineTotalWithTax(item))"></span>
                                             </td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button" @click="removeItem(idx)" class="text-gray-400 hover:text-red-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile -->
                        <div class="md:hidden space-y-4">
                            <template x-for="(item, idx) in items" :key="'m-'+idx">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 relative">
                                    <button type="button" @click="removeItem(idx)" class="absolute top-3 right-3 text-gray-400 hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="space-y-3 pr-8">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="item.description" required class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Item description">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                                <input type="number" step="0.01" min="0.01" x-model.number="item.quantity" class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Rate</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">RM</span>
                                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-full !pl-10 !pr-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Tax Rate</label>
                                            <div x-data="{ showCustom: false }">
                                                <select x-show="!showCustom" x-model.number="item.tax_rate" @change="if ($event.target.value === 'custom') { showCustom = true; item.tax_rate = 0; }"
                                                        class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                                    @foreach($defaultTaxRates as $rate)
                                                        <option value="{{ $rate['value'] }}">{{ $rate['value'] }}% @if(!empty($rate['label'])) - {{ $rate['label'] }}@endif</option>
                                                    @endforeach
                                                    <option value="custom">Custom</option>
                                                </select>
                                                <div x-show="showCustom" class="flex items-center gap-2">
                                                    <input type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate"
                                                           class="flex-1 !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                           placeholder="Enter custom %">
                                                    <button type="button" @click="showCustom = false; item.tax_rate = 0;" class="p-2 text-gray-400 hover:text-gray-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                            <span class="text-xs text-gray-600">Line Total</span>
                                            <span class="font-semibold text-gray-900" x-text="formatMoney(lineTotalWithTax(item))"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Hidden input for items JSON -->
                        <input type="hidden" name="items_json" x-bind:value="JSON.stringify(items)">

                        <!-- Totals inline -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" rows="4" class="mt-2 block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Additional information (optional)">{{ old('notes', $invoice->notes) }}</textarea>
                            </div>
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                                <dl class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm text-gray-600">Subtotal</dt>
                                        <dd class="text-base font-semibold text-gray-900" x-text="formatMoney(subtotal())"></dd>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm text-gray-600">Tax</dt>
                                        <dd class="text-base font-semibold text-gray-900" x-text="formatMoney(totalTax())"></dd>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm text-gray-600">Discount</dt>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">RM</span>
                                            <input type="number" step="0.01" min="0" x-model.number="discountAmount" @input="$refs.discountField.value = discountAmount"
                                                   class="w-28 !px-2.5 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                    <input type="hidden" name="discount_amount" x-ref="discountField" value="{{ old('discount_amount', $invoice->discount_amount) }}">
                                    <div class="flex items-center justify-between border-t border-gray-200 pt-3">
                                        <dt class="text-sm font-semibold text-gray-900">Total</dt>
                                        <dd class="text-xl font-extrabold text-gray-900" x-text="formatMoney(grandTotalAfterDiscount())"></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                </div>
            </div>

            <!-- Sticky bottom bar -->
            <div class="px-6 py-4 border-t border-gray-100 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 sticky bottom-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-base font-semibold text-gray-900" x-text="formatMoney(subtotal())"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Tax</span>
                            <span class="text-base font-semibold text-gray-900" x-text="formatMoney(totalTax())"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Discount</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">RM</span>
                                <input type="number" step="0.01" min="0" x-model.number="discountAmount" @input="$refs.discountField.value = discountAmount"
                                       class="w-24 !px-2 !py-1 text-sm text-right border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900">Total</span>
                            <span class="text-xl font-extrabold text-gray-900" x-text="formatMoney(grandTotalAfterDiscount())"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                    <a href="{{ route('user.invoices.show', $invoice) }}" onclick="return confirm('Are you sure you want to cancel editing? Any unsaved changes will be lost.')" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gray-700 hover:bg-gray-800">Save as Draft</button>
                        <!--<button type="submit" name="action" value="update" class="px-5 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700">Update & Continue</button>-->
                        <button type="submit" form="invoice-form" @click="setStatus('pending')" name="action" value="update" class="px-5 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700">Update & Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        window.customerSearchData = function() {
            return {
                selectedCustomerId: '{{ old('customer_id', $invoice->customer_id) }}',
                customerName: '{{ old('customer_name', $invoice->customer ? $invoice->customer->name : '') }}',
                customerEmail: '{{ old('customer_email', $invoice->customer ? $invoice->customer->email : '') }}',
                customerPhone: '{{ old('customer_phone', $invoice->customer->phone ?: '') }}',
                customerStreetAddress: '{{ old('customer_street_address', $invoice->customer->street_address) }}',
                customerCity: '{{ old('customer_city', $invoice->customer->city) }}',
                customerStateId: '{{ old('customer_state_id', $invoice->customer->state_id) }}',
                customerPostalCode: '{{ old('customer_postal_code', $invoice->customer->postal_code) }}',
                customerCountry: '{{ old('customer_country', $invoice->customer->country ?? 'MYS') }}',
                customerTin: '{{ old('customer_tin', $invoice->customer->tin) }}',
                customerDocumentType: '{{ $invoice->customer->document_type ?? '' }}',
                customerDocumentNumber: '{{ $invoice->customer->document_number ?? '' }}',

                onCustomerChange(event) {
                    const selectedOption = event.target.options[event.target.selectedIndex];
                    const customerData = selectedOption.getAttribute('data-customer');

                    if (customerData) {
                        const customer = JSON.parse(customerData);
                        this.selectedCustomerId = customer.id;
                        this.customerName = customer.name || '';
                        this.customerEmail = customer.email || '';
                        this.customerPhone = this.stripPhonePrefix(customer.phone || '');
                        this.customerStreetAddress = customer.street_address || '';
                        this.customerCity = customer.city || '';
                        this.customerStateId = customer.state_id || '';
                        this.customerPostalCode = customer.postal_code || '';
                        this.customerCountry = customer.country || 'MYS';
                        // Auto-fill eInvoice information
                        this.customerTin = customer.tin || '';
                        this.customerDocumentType = customer.document_type || '';
                        this.customerDocumentNumber = customer.document_number || '';
                    }
                },

                formatPhone(event) {
                    let value = event.target.value;

                    // Clean the value: keep + and digits
                    value = value.replace(/[^\d\+]/g, '');

                    // Ensure starts with +
                    if (!value.startsWith('+')) {
                        value = '+' + value;
                    }

                    // Remove extra +
                    value = value.replace(/\+/g, (match, offset) => offset === 0 ? '+' : '');

                    // Limit length
                    value = value.substring(0, 20);

                    // Update the model
                    this.customerPhone = value;
                    event.target.value = value;
                },

                stripPhonePrefix(phone) {
                    if (!phone) return '';
                    return phone.replace(/^\+?60/, '');
                },
                init() {
                    // Listen for customer selection events
                    window.addEventListener('customerSelected', (event) => {
                        const customer = event.detail;
                        console.log('Received customer selection event:', customer);
                        this.selectedCustomerId = customer.id;
                        this.customerName = customer.name;
                        this.customerEmail = customer.email;
                        this.customerPhone = customer.phone;
                        this.customerStreetAddress = customer.street_address;
                        this.customerCity = customer.city;
                        this.customerStateId = customer.state_id;
                        this.customerPostalCode = customer.postal_code;
                        this.customerTin = customer.tin;
                        this.customerDocumentType = customer.document_type;
                        this.customerDocumentNumber = customer.document_number;
                    });
                }
            };
        }

        function invoiceForm() {
            return {
                items: {!! json_encode($invoice->items->map(function($item) {
                    return [
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'tax_rate' => (float) $item->tax_rate,
                        'item_classification_id' => $item->item_classification_id
                    ];
                })) !!},
                documentType: '{{ old('document_type', $invoice->document_type ?? 'invoice') }}',
                invoiceNumber: '{{ old('invoice_number', $invoice->invoice_number) }}',
                invoiceDate: '{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}',
                dueDate: '{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}',
                invoiceStatus: '{{ old('invoice_status', $invoice->invoice_status) }}',
                lhdnStatus: '{{ old('lhdn_status', $invoice->lhdn_status) }}',
                paymentMethod: '{{ old('payment_method', $invoice->payment_method) }}',
                discountAmount: Number('{{ old('discount_amount', $invoice->discount_amount) }}') || 0,
                formChanged: false,
                init() {
                    this.$watch('items', () => { this.formChanged = true; }, { deep: true });
                    this.$watch('invoiceDate', () => { this.formChanged = true; });
                    this.$watch('dueDate', () => { this.formChanged = true; });
                },
                addItem() {
                    this.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0, item_classification_id: '{{ $invoice->company->default_item_classification_id }}' });
                    this.formChanged = true;
                },
                removeItem(idx) {
                    if (this.items.length > 1) {
                        this.items.splice(idx, 1);
                        this.formChanged = true;
                    }
                },
                autoCalculateDueDate() {
                    if (this.invoiceDate) {
                        const invoiceDate = new Date(this.invoiceDate);
                        const dueDate = new Date(invoiceDate);
                        dueDate.setDate(dueDate.getDate() + 30);
                        this.dueDate = dueDate.toISOString().split('T')[0];
                    }
                },
            formatPhoneForDisplay(phone) {
                if (!phone) return '';

                // Remove +60 prefix if present
                let cleanPhone = phone.replace(/^\+60/, '');

                // Remove leading 0 if present
                if (cleanPhone.startsWith('0')) {
                    cleanPhone = cleanPhone.substring(1);
                }

                return cleanPhone;
            },
            confirmCancel(event) {
                console.log('confirmCancel called, formChanged:', this.formChanged);
                if (this.formChanged) {
                    console.log('Showing confirmation dialog');
                    if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                        console.log('User cancelled, preventing navigation');
                        event.preventDefault();
                        return false;
                    }
                } else {
                    console.log('No changes detected, allowing navigation');
                }
                return true;
            },
            lineTotal(item) {
                    return (Number(item.quantity||0) * Number(item.unit_price||0));
                },
                lineTotalWithTax(item) {
                    const lt = this.lineTotal(item);
                    const tax = lt * Number((item.tax_rate||0)/100);
                    return lt + tax;
                },
                subtotal() {
                    return this.items.reduce((s, i) => s + this.lineTotal(i), 0);
                },
                totalTax() {
                    return this.items.reduce((s, i) => s + (this.lineTotal(i) * Number((i.tax_rate||0)/100)), 0);
                },
                grandTotal() {
                    return this.subtotal() + this.totalTax();
                },
                grandTotalAfterDiscount() {
                    const g = this.grandTotal() - Number(this.discountAmount||0);
                    return g < 0 ? 0 : g;
                },
                formatMoney(v) { return 'RM ' + (Number(v||0)).toFixed(2); },
                syncItems() { this.formChanged = false; },
                setStatus(status) {
                    this.invoiceStatus = status;
                    // Update the select field
                    const statusInput = document.querySelector('select[name="invoice_status"]');
                    if (statusInput) {
                        statusInput.value = status;
                    }
                },
                async loadOriginalInvoice(invoiceId) {
                    if (!invoiceId) return;

                    console.log('Loading original invoice with ID:', invoiceId);

                    try {
                        const response = await fetch(`/app/invoices/${invoiceId}/data`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        console.log('Response status:', response.status, 'URL:', `/app/invoices/${invoiceId}/data`);

                        if (response.ok) {
                            const invoice = await response.json();
                            console.log('Invoice data:', invoice);

                            // Populate form fields
                            const prefix = this.documentType === 'credit_note' ? 'CN-' : 'DN-';
                            // Generate a unique invoice number
                            const timestamp = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                            this.invoiceNumber = `${prefix}${invoice.invoice_number}-${timestamp}`;
                            this.invoiceDate = new Date().toISOString().split('T')[0]; // Today
                            this.dueDate = invoice.due_date ? new Date(invoice.due_date).toISOString().split('T')[0] : '';

                            // Dispatch event to update customer search component
                            console.log('Dispatching customer update event:', invoice.customer);
                            if (invoice.customer) {
                                // Dispatch custom event with customer data
                                window.dispatchEvent(new CustomEvent('customerSelected', {
                                    detail: {
                                        id: invoice.customer_id,
                                        name: invoice.customer.name || '',
                                        email: invoice.customer.email || '',
                                        phone: this.formatPhoneForDisplay(invoice.customer.phone || ''),
                                        street_address: invoice.customer.street_address || '',
                                        city: invoice.customer.city || '',
                                        state_id: invoice.customer.state_id || '',
                                        postal_code: invoice.customer.postal_code || '',
                                        tin: invoice.customer.tin || '',
                                        document_type: invoice.customer.document_type || '',
                                        document_number: invoice.customer.document_number || ''
                                    }
                                }));
                            }

                            // Copy items
                            this.items = invoice.items.map(item => ({
                                description: item.description,
                                quantity: item.quantity,
                                unit_price: item.unit_price,
                                tax_rate: item.tax_rate,
                                item_classification_id: item.item_classification_id
                            }));

                            this.formChanged = true;
                        } else {
                            console.error('Failed to load invoice:', response.status, response.statusText);
                            alert('Failed to load original invoice data. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error loading original invoice:', error);
                        alert('An error occurred while loading the original invoice. Please try again.');
                    }
                },
            };
        }
    </script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        // Wait for Alpine to initialize first
    });

    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer-select-edit');
        if (customerSelect) {
            const choices = new Choices(customerSelect, {
                searchEnabled: true,
                searchPlaceholderValue: 'Search customer by name or email...',
                itemSelectText: '',
                shouldSort: false,
                removeItemButton: false,
                allowHTML: true,
            });

            // Listen to Choices.js change event
            customerSelect.addEventListener('change', function(event) {
                console.log('Customer selected, value:', event.target.value);

                // Manually trigger Alpine.js onCustomerChange
                const billToSection = customerSelect.closest('[x-data]');
                if (billToSection && billToSection._x_dataStack) {
                    const alpineData = billToSection._x_dataStack[0];
                    if (alpineData.onCustomerChange) {
                        console.log('Calling onCustomerChange');
                        alpineData.onCustomerChange(event);
                    }
                }
            });
        }
    });
</script>
@endpush
