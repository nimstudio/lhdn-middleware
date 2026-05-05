@extends('layouts.user-app', ['title' => 'Create Invoice'])

@section('content')
<div class="max-w-7xl mx-auto" x-data="invoiceFormData" x-init="init()">
        <!-- Header + Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                    <p class="text-gray-600 mt-1" x-text="getDocumentTypeInstructions()"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('user.invoices.index') }}" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    <button type="submit" form="invoice-form" @click="setStatus('draft')" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gray-700 hover:bg-gray-800">Save Draft</button>
                    <button type="submit" form="invoice-form" @click="setStatus('pending'); console.log('Save & Continue clicked')" name="action" value="create" :disabled="documentType !== 'invoice' && !isAdjusted" class="px-5 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">Save & Continue</button>
                </div>
            </div>
        </div>

        <form id="invoice-form" method="POST" action="{{ route('user.invoices.store') }}" @submit="console.log('Form submitting...'); syncItems()" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            @csrf

            <!-- Body: From & Bill To side-by-side, then wide Items -->
            <div class="p-6 space-y-8">
                <!-- Top: From and Bill To in two columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- From -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-5">
                        <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3" x-text="['11','12','13','14'].includes(documentType) ? 'Supplier' : 'From'"></h3>
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1.5">
                                <p class="text-sm font-semibold text-gray-900">{{ $company->name }}</p>
                                <p class="text-sm text-gray-600">{{ $company->address_line_1 }}@if($company->address_line_2), {{ $company->address_line_2 }}@endif</p>
                                <p class="text-sm text-gray-600">{{ $company->postcode }} {{ $company->city }}, {{ $company->state->name ?? '' }}</p>
                            </div>
                            <div class="text-right text-sm text-gray-600 space-y-1">
                                <p><span class="text-xs text-gray-500">Phone:</span> {{ $company->phone }}</p>
                                <p><span class="text-xs text-gray-500">Email:</span> {{ $company->email }}</p>
                                <p><span class="text-xs text-gray-500">SSM:</span> {{ $company->registration_number }}</p>
                                <p><span class="text-xs text-gray-500">TIN:</span> {{ $company->tin_number }}</p>
                            </div>
                        </div>

                        <!-- Invoice Meta inside From card -->
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Document Type</label>
                                <select name="document_type" x-model="documentType"
                                        class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('document_type') border-red-500 @enderror">
                                    @foreach($invoiceTypes as $type)
                                        <option value="{{ $type->code }}" {{ old('document_type', '01') == $type->code ? 'selected' : '' }}>{{ $type->code }} - {{ $type->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="documentType === '02' || documentType === '03' || documentType === '04' || documentType === '12' || documentType === '13' || documentType === '14'">
                                <label class="block text-xs font-medium text-gray-600">Original Invoice</label>
                                <x-searchable-select-table
                                    :options="$originalInvoices->map(fn($i) => ['id' => $i->id, 'label' => $i->lhdn_uuid])->toArray()"
                                    :selected="old('original_invoice_id')"
                                    value-key="id"
                                    label-key="label"
                                    :search-keys="['label']"
                                    model="originalInvoiceId"
                                    placeholder="Select UUID"
                                    class="mt-2 w-full"
                                />
                                <input type="hidden" name="original_invoice_id" x-model="originalInvoiceId">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice No. <span class="text-red-500">*</span></label>
                                <input type="text" name="invoice_number" required x-model="invoiceNumber"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono @error('invoice_number') border-red-500 @enderror"
                                       placeholder="{{ $nextInvoiceNumber }}" value="{{ old('invoice_number', $nextInvoiceNumber) }}">
                                @error('invoice_number')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice Date <span class="text-red-500">*</span></label>
                                <input type="date" name="invoice_date" required x-model="invoiceDate"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('invoice_date') border-red-500 @enderror"
                                       value="{{ old('invoice_date', now()->toDateString()) }}">
                                @error('invoice_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                             <div>
                                 <label class="block text-xs font-medium text-gray-600">Billing Start</label>
                                 <input type="date" name="billing_start" x-model="billingStart"
                                        class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('billing_start') border-red-500 @enderror"
                                        value="{{ old('billing_start') }}">
                                 @error('billing_start')
                                     <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                 @enderror
                             </div>
                             <div>
                                 <label class="block text-xs font-medium text-gray-600">Billing End</label>
                                 <input type="date" name="billing_end" x-model="billingEnd"
                                        class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('billing_end') border-red-500 @enderror"
                                        value="{{ old('billing_end') }}">
                                 @error('billing_end')
                                     <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                 @enderror
                             </div>
                             <div>
                                <label class="block text-xs font-medium text-gray-600">Currency</label>
                                <select name="currency" x-model="currency" class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                    <option value="">Select Currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->code }}" {{ old('currency', 'MYR') == $currency->code ? 'selected' : '' }}>
                                            {{ $currency->code }} - {{ $currency->currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Invoice Status and Payment Mode -->
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Invoice Status <span class="text-red-500">*</span></label>
                                <select name="invoice_status" x-model="invoiceStatus" class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white @error('invoice_status') border-red-500 @enderror">
                                    <option value="draft" {{ old('invoice_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="pending" {{ old('invoice_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('invoice_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="cancelled" {{ old('invoice_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('invoice_status')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">"Save Draft" sets to Draft, "Save & Continue" sets to Pending</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Mode of Payment</label>
                                <select name="payment_method" x-model="paymentMethod" class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                    <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Credit Card" {{ old('payment_method') === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="E-Wallet" {{ old('payment_method') === 'E-Wallet' ? 'selected' : '' }}>E-Wallet</option>
                                    <option value="Cheque" {{ old('payment_method') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bill To -->
                    <div class="bg-white rounded-xl border-2 border-primary-100 p-5" x-data="customerSearchData" x-init="init()">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs uppercase tracking-wide font-semibold text-primary-700" x-text="['11','12','13','14'].includes(documentType) ? 'Customer' : 'Bill To'"><span class="text-red-500" x-show="!['11','12','13','14'].includes(documentType)">*</span></h3>
                            <a href="{{ route('user.customers.create') }}" target="_blank" class="inline-flex items-center text-xs text-primary-600 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                New Customer
                            </a>
                        </div>

                        <!-- Self-billed notice -->
                        <div x-show="['11','12','13','14'].includes(documentType)" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                <strong>Note:</strong> For self-billed documents, the supplier and customer roles are reversed for LHDN submission. The "Supplier" section shows customer information, and the "Customer" section shows company information.
                            </p>
                        </div>

                        <!-- Customer Search -->
                        <div class="mb-5 relative">
                            <div class="relative">
                                <input type="text" x-model="searchQuery" @input="searchCustomers()" @focus="showDropdown = true"
                                       class="w-full !px-4 !py-3 !pr-10 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                       placeholder="Search customer by name, email, or phone..."
                                       autocomplete="off">
                                <svg class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Dropdown Results -->
                            <div x-show="showDropdown && searchResults.length > 0"
                                 @click.outside="showDropdown = false"
                                 class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                <template x-for="customer in searchResults" :key="customer.id">
                                    <button type="button" @click="selectCustomer(customer)"
                                            class="w-full px-4 py-3 text-left hover:bg-primary-50 border-b border-gray-100 last:border-0 transition-colors">
                                        <div class="font-medium text-gray-900 text-sm" x-text="customer.name"></div>
                                        <div class="text-xs text-gray-600 mt-0.5">
                                            <span x-show="customer.email" x-text="customer.email"></span>
                                            <span x-show="customer.email && customer.phone"> • </span>
                                            <span x-show="customer.phone" x-text="customer.phone"></span>
                                        </div>
                                </button>
                                </template>
                            </div>
                        </div>

                        <!-- Hidden customer_id -->
                        <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                        <!-- Customer Details Form -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" required x-model="customerName" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Customer Co. / Person" value="{{ old('customer_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" name="customer_email" x-model="customerEmail" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="customer@email.com" value="{{ old('customer_email') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="text"
                                       name="customer_phone"
                                       required
                                       x-model="customerPhone"
                                       @input="formatPhoneNumber($event)"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                                       placeholder="Phone number"
                                       value="{{ old('customer_phone') }}">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Phone number is required and used to prevent duplicate customers</p>
                            </div>

                            <!-- Address Breakdown -->
                            <div class="pt-2 border-t border-gray-200">
                                <h4 class="text-xs font-semibold text-gray-700 mb-3">Address</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Street Address</label>
                                        <input type="text" name="customer_street_address" x-model="customerStreetAddress" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Street address" value="{{ old('customer_street_address') }}">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">City</label>
                                            <input type="text" name="customer_city" x-model="customerCity" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="City" value="{{ old('customer_city') }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">State</label>
                                            <select name="customer_state_id" x-model="customerStateId" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                                <option value="">Select state</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->id }}" {{ old('customer_state_id') == $state->id ? 'selected' : '' }}>
                                                        {{ $state->lhdn_code }} - {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Postal Code</label>
                                            <input type="text" name="customer_postal_code" maxlength="10" x-model="customerPostalCode" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="12345" value="{{ old('customer_postal_code') }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Country</label>
                                            <select name="customer_country" x-model="customerCountry" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                                <option value="">Select Country</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->code }}" {{ old('customer_country', 'MYS') == $country->code ? 'selected' : '' }}>
                                                        {{ $country->code }} - {{ $country->country }}
                                                    </option>
                                                @endforeach
                                            </select>
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
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">TIN</label>
                                        <input type="text" name="customer_tin" x-model="customerTin" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Tax Identification Number" value="{{ old('customer_tin') }}">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Document Type</label>
                                            <select name="customer_document_type" x-model="customerDocumentType" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                                <option value="">Select type</option>
                                                <option value="BRN" {{ old('customer_document_type') == 'BRN' ? 'selected' : '' }}>BRN</option>
                                                <option value="NRIC" {{ old('customer_document_type') == 'NRIC' ? 'selected' : '' }}>NRIC</option>
                                                <option value="PASSPORT" {{ old('customer_document_type') == 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                                                <option value="ARMY" {{ old('customer_document_type') == 'ARMY' ? 'selected' : '' }}>Army ID</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Document Number</label>
                                            <input type="text" name="customer_document_number" x-model="customerDocumentNumber" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Document number" value="{{ old('customer_document_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white rounded-xl border-2 border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-700">Items</h3>
                        <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Add Item
                                </button>
                        </div>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto" style="overflow: visible;">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600">Description</th>
                                    <th class="px-1 py-2 text-right text-xs font-semibold text-gray-600">Qty</th>
                                    <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600">Unit Price</th>
                                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600">Tax Type</th>
                                    <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600">Tax Rate %</th>
                                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600">Classification</th>
                                    <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600">Total</th>
                                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                            <tbody>
                                    <template x-for="(item, idx) in items" :key="idx">
                                    <tr class="border-b border-gray-100">
                                            <td class="px-2 py-2">
                                                <input type="text" x-model="item.description" required class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Item description">
                                            </td>
                                            <td class="px-1 py-2 text-right">
                                            <input type="number" min="1" x-model.number="item.quantity" class="w-24 !px-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </td>
                                            <td class="px-2 py-2 text-right">
                                                <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">RM</span>
                                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-32 !pl-10 !pr-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                            <select x-model="item.tax_type_id"
                                                    class="min-w-[80px] !px-2 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                                 <option value="">Select Type</option>
                                                 @foreach($taxTypes as $taxType)
                                                     <option value="{{ $taxType->id }}">{{ $taxType->code }} - {{ $taxType->description }}</option>
                                                 @endforeach
                                            </select>
                                            </td>
                                            <td class="px-2 py-2 text-right">
                                                <div class="relative">
                                                    <input type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate"
                                                           class="w-24 !px-2 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-500">%</span>
                                                </div>
                                            </td>
                                        <td class="px-2 py-2 text-center">
                                            <x-searchable-select-table
                                                :options="$itemClassifications->map(fn($c) => ['id' => $c->id, 'label' => $c->code . ' - ' . Str::limit($c->description, 30), 'code' => $c->code, 'description' => $c->description])->toArray()"
                                                :selected="$company->default_item_classification_id ?? ''"
                                                value-key="id"
                                                label-key="label"
                                                :search-keys="['code', 'description']"
                                                model="item.item_classification_id"
                                                class="inline-block"
                                            />
                                        </td>
                                        <td class="px-2 py-2 text-right text-sm font-medium text-gray-900" x-text="formatMoney(lineTotalWithTax(item))">
                                            </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" @click="removeItem(idx)" x-show="items.length > 1" class="text-red-600 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-3">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <input type="text" x-model="item.description" required class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Item description">
                                    </div>
                                    <button type="button" @click="removeItem(idx)" x-show="items.length > 1" class="ml-2 text-red-600 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                        <label class="block text-xs text-gray-600 mb-1">Quantity</label>
                                        <input type="number" min="1" x-model.number="item.quantity" class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                            <div>
                                        <label class="block text-xs text-gray-600 mb-1">Unit Price</label>
                                                <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">RM</span>
                                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-full !pl-10 !pr-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                                </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Tax Type</label>
                                        <select x-model="item.tax_type_id"
                                                class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                            <option value="">Select Type</option>
                                            @foreach($taxTypes as $taxType)
                                                <option value="{{ $taxType->id }}">{{ $taxType->code }} - {{ $taxType->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Tax Rate</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate"
                                                   class="w-full !px-3 !py-2 !pr-8 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                   placeholder="0.00">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-500">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                    <span class="text-sm text-gray-600">Total (incl. tax)</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="formatMoney(lineTotalWithTax(item))"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Hidden inputs for items -->
                        <template x-for="(item, idx) in items" :key="'h-'+idx">
                            <div class="hidden">
                                <input :name="`items[${idx}][description]`" :value="item.description">
                                <input :name="`items[${idx}][quantity]`" :value="item.quantity">
                                <input :name="`items[${idx}][unit_price]`" :value="item.unit_price">
                                <input :name="`items[${idx}][tax_rate]`" :value="item.tax_rate">
                                <input :name="`items[${idx}][tax_type_id]`" :value="item.tax_type_id">
                                <input :name="`items[${idx}][item_classification_id]`" :value="item.item_classification_id">
                            </div>
                        </template>

                        <!-- Totals inline -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="4" class="mt-2 block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Additional information (optional)"></textarea>
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
                                    <input type="hidden" name="discount_amount" x-ref="discountField" value="{{ old('discount_amount', 0) }}">
                                <div class="flex items-center justify-between pt-2 border-t border-gray-300">
                                    <dt class="text-lg font-semibold text-gray-900">Total</dt>
                                    <dd class="text-xl font-bold text-gray-900" x-text="formatMoney(grandTotalAfterDiscount())"></dd>
                                    </div>
                                </dl>
                            </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection





    <script>
        // Define customer search data object
        window.customerSearchData = {
            searchQuery: '',
            searchResults: [],
            showDropdown: false,
            selectedCustomerId: '{{ old('customer_id') }}',
            searchTimeout: null,
            customerName: '{{ old('customer_name') }}',
            customerEmail: '{{ old('customer_email') }}',
            customerPhone: '{{ old('customer_phone') }}',
            customerStreetAddress: '{{ old('customer_street_address') }}',
            customerCity: '{{ old('customer_city') }}',
            customerStateId: '{{ old('customer_state_id') }}',
            customerPostalCode: '{{ old('customer_postal_code') }}',
            customerTin: '{{ old('customer_tin') }}',
            customerDocumentType: '{{ old('customer_document_type') }}',
            customerDocumentNumber: '{{ old('customer_document_number') }}',
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
            },
            searchCustomers() {
                clearTimeout(this.searchTimeout);

                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }

                this.searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('user.customers.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        this.searchResults = await response.json();
                        this.showDropdown = true;
                    } catch (error) {
                        console.error('Error searching customers:', error);
                    }
                }, 300);
            },
            selectCustomer(customer) {
                this.selectedCustomerId = customer.id;
                this.customerName = customer.name;
                this.customerEmail = customer.email || '';
                this.customerPhone = this.formatPhoneForDisplay(customer.phone || '');
                this.customerStreetAddress = customer.street_address || '';
                this.customerCity = customer.city || '';
                this.customerStateId = customer.state_id || '';
                this.customerPostalCode = customer.postal_code || '';
                this.customerTin = customer.tin || '';
                this.customerDocumentType = customer.document_type || '';
                this.customerDocumentNumber = customer.document_number || '';

                this.searchQuery = customer.name;
                this.showDropdown = false;
            },
            formatPhoneNumber(event) {
                let value = event.target.value;

                // Remove any non-numeric characters except spaces, hyphens, and plus
                value = value.replace(/[^\d\s\-\+]/g, '');

                event.target.value = value;
            },
            formatPhoneForDisplay(phone) {
                if (!phone) return '';

                return phone;
            },
            async checkDuplicateTin() {
                if (!this.customerTin || this.customerTin.length < 3) return false;

                try {
                    const response = await fetch(`{{ route('user.customers.search') }}?q=${encodeURIComponent(this.customerTin)}`);
                    const customers = await response.json();

                    // Check if any existing customer has this TIN
                    const duplicate = customers.find(c => c.tin === this.customerTin);
                    if (duplicate) {
                        alert(`A customer with TIN "${this.customerTin}" already exists: ${duplicate.name}. Please select the existing customer instead of creating a duplicate.`);
                        return true; // Duplicate found
                    }
                } catch (error) {
                    console.error('Error checking for duplicate TIN:', error);
                }
                return false; // No duplicate
            }
        };
    </script>

    @push('scripts')
    <script>
        // Define invoice form data object
        window.invoiceFormData = {
            items: [ { description: '', quantity: 1, unit_price: 0, tax_rate: 0, tax_type_id: '', item_classification_id: '{{ $company->default_item_classification_id }}' } ],
            documentType: '{{ old('document_type', '01') }}',
            originalInvoiceId: '{{ old('original_invoice_id') }}',
            currency: '{{ old('currency', 'MYR') }}',
            customerCountry: '{{ old('customer_country', 'MYS') }}',
            invoiceNumber: '{{ old('invoice_number', $nextInvoiceNumber) }}',
            invoiceDate: '{{ old('invoice_date', now()->toDateString()) }}',
            billingStart: '{{ old('billing_start') }}',
            billingEnd: '{{ old('billing_end') }}',
            invoiceStatus: '{{ old('invoice_status', 'draft') }}',
            paymentMethod: '{{ old('payment_method', 'Bank Transfer') }}',
            discountAmount: Number('{{ old('discount_amount', 0) }}') || 0,
            formChanged: false,
            isAdjusted: true, // Default true for invoice, will be set false for credit/debit when original loaded
                init() {
                    this.$watch('items', () => {
                        this.formChanged = true;
                        if (this.documentType === '02' || this.documentType === '03' || this.documentType === '04' || this.documentType === '12' || this.documentType === '13' || this.documentType === '14') {
                            this.isAdjusted = true;
                        }
                    }, { deep: true });
                    this.$watch('invoiceDate', () => { this.formChanged = true; });
                    this.$watch('documentType', () => {
                        this.formChanged = true;
                        // If switching away from credit/debit note, clear original invoice
                        if (this.documentType === '01' || this.documentType === '11') {
                            this.originalInvoiceId = '';
                            this.isAdjusted = true;
                        }
                    });
                    this.$watch('originalInvoiceId', (newVal) => {
                        if (newVal) {
                            this.loadOriginalInvoice(newVal);
                        }
                    });
                    // Watch for TIN changes to check for duplicates
                    this.$watch('customerTin', async (newTin) => {
                        if (newTin && newTin.length >= 3) {
                            // Check for duplicates after a short delay
                            setTimeout(async () => {
                                if (window.customerSearchData) {
                                    await window.customerSearchData.checkDuplicateTin();
                                }
                            }, 500);
                        }
                    });
                },
            addItem() {
                this.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0, tax_type_id: '', item_classification_id: '{{ $company->default_item_classification_id }}' });
                this.formChanged = true;
            },
            removeItem(idx) {
                if (this.items.length > 1) {
                    this.items.splice(idx, 1);
                    this.formChanged = true;
                }
            },
            subtotal() {
                return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
            },
            totalTax() {
                return this.items.reduce((sum, item) => {
                    const lineTotal = Number(item.quantity || 0) * Number(item.unit_price || 0);
                    return sum + (lineTotal * (Number(item.tax_rate || 0) / 100));
                }, 0);
            },
            grandTotal() {
                return this.subtotal() + this.totalTax();
            },
            grandTotalAfterDiscount() {
                const g = this.grandTotal() - Number(this.discountAmount||0);
                return g < 0 ? 0 : g;
            },
            formatMoney(v) { return 'RM ' + (Number(v||0)).toFixed(2); },
            lineTotal(item) {
                return (Number(item.quantity||0) * Number(item.unit_price||0));
            },
            lineTotalWithTax(item) {
                const lineTotal = this.lineTotal(item);
                const tax = lineTotal * (Number(item.tax_rate||0) / 100);
                return lineTotal + tax;
            },
            syncItems() {
                console.log('Syncing items...');
                this.formChanged = false;
            },
            setStatus(status) {
                console.log('Setting status to:', status);
                this.invoiceStatus = status;
                // Update the select field
                const statusInput = document.querySelector('select[name=invoice_status]');
                if (statusInput) {
                    statusInput.value = status;
                    console.log('Status input updated to:', statusInput.value);
                } else {
                    console.error('Status input not found');
                }
            },
            getDocumentTypeInstructions() {
                if (this.documentType === '02' || this.documentType === '12') {
                    return 'Creating a credit note. Select an original invoice and adjust the amounts to reflect the credit amount.';
                } else if (this.documentType === '03' || this.documentType === '13') {
                    return 'Creating a debit note. Select an original invoice and adjust the amounts to reflect the additional charges.';
                } else {
                    return 'Creating an invoice. Fill in the invoice details.';
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
                        console.log('Invoice data received:', invoice);

                        // Populate form fields
                        // Generate a unique invoice number with increment
                        this.invoiceNumber = `${invoice.invoice_number}-${invoice.next_increment}`;
                        this.invoiceDate = new Date().toISOString().split('T')[0]; // Today
                        this.dueDate = invoice.due_date ? new Date(invoice.due_date).toISOString().split('T')[0] : '';
                        this.billingStart = invoice.billing_start || '';
                        this.billingEnd = invoice.billing_end || '';

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

                        // For credit/debit notes, you might want to reverse the amounts or adjust
                        if (this.documentType === 'credit_note') {
                            // For credit notes, keep amounts as-is or make them negative
                            // The user can adjust as needed
                        }

                        this.formChanged = true;
                        // For credit/debit notes, reset isAdjusted since items are loaded from original
                        if (this.documentType === 'credit_note' || this.documentType === 'debit_note') {
                            this.isAdjusted = false;
                        }
                    } else {
                        console.error('Failed to load invoice:', response.status, response.statusText);
                        let errorMessage = `Failed to load original invoice data (Status: ${response.status}).`;
                        if (response.status === 404) {
                            errorMessage += ' Invoice not found.';
                        } else if (response.status === 403) {
                            // Try to get error message from response
                            response.json().then(data => {
                                alert(data.message || 'Access denied - you can only access invoices from your own company.');
                            }).catch(() => {
                                alert('Access denied - you can only access invoices from your own company.');
                            });
                            return;
                        } else {
                            errorMessage += ' Server error occurred.';
                        }
                        alert(errorMessage + ' Please check browser console for details.');
                    }
                } catch (error) {
                    console.error('Error loading original invoice:', error);
                    alert('An error occurred while loading the original invoice. Please try again.');
                }
            },
            formatPhoneForDisplay(phone) {
                if (!phone) return '';

                return phone;
            },
            confirmCancel(event) {
                if (this.formChanged) {
                    if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                        event.preventDefault();
                        return false;
                    }
                }
                return true;
            },
        };

        // Simple customer selection - no complex dropdown needed
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Invoice form loaded successfully');
        });
    </script>
    @endpush
