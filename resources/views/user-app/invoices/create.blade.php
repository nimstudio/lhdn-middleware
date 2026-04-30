@extends('layouts.user-app', ['title' => 'Create Invoice'])

@section('content')
    <div class="max-w-7xl mx-auto" x-data="invoiceForm()" x-init="init()">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
            <p class="text-gray-600 mt-1">Issue a new invoice to your customer. Fields marked with <span class="text-red-500">*</span> are required.</p>
        </div>

        <!-- Card -->
        <form method="POST" action="{{ route('user.invoices.store') }}" @submit="syncItems()" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            @csrf

            <!-- Top bar -->
            <div class="px-6 py-5 bg-gradient-to-r from-primary-50 to-brand-50 border-b border-primary-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>
                            <p class="text-sm text-gray-600">Set invoice number and dates</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Invoice No. <span class="text-red-500">*</span></label>
                            <input type="text" name="invoice_number" required x-model="invoiceNumber"
                                   class="mt-2 w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono @error('invoice_number') border-red-500 @enderror"
                                   placeholder="{{ $nextInvoiceNumber }}" value="{{ old('invoice_number', $nextInvoiceNumber) }}">
                            <p class="mt-1 text-xs text-gray-500">Auto-generated. You can edit if needed.</p>
                            @error('invoice_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Invoice Date <span class="text-red-500">*</span></label>
                            <input type="date" name="invoice_date" required x-model="invoiceDate" @change="autoCalculateDueDate()"
                                   class="mt-2 w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('invoice_date') border-red-500 @enderror"
                                   value="{{ old('invoice_date', now()->toDateString()) }}">
                            @error('invoice_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Due Date <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" required x-model="dueDate"
                                   class="mt-2 w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('due_date') border-red-500 @enderror"
                                   value="{{ old('due_date') }}">
                            @error('due_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-8">
                <!-- Parties -->
                <div class="space-y-6">
                    <!-- From (Company) - Full Width -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3">From</h3>
                                <div class="space-y-1.5">
                                    <p class="text-lg font-bold text-gray-900">{{ $company->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $company->address_line_1 }}@if($company->address_line_2), {{ $company->address_line_2 }}@endif</p>
                                    <p class="text-sm text-gray-600">{{ $company->postcode }} {{ $company->city }}, {{ $company->state->name ?? '' }}</p>
                                </div>
                            </div>
                            <div class="text-right text-sm text-gray-600 space-y-1">
                                <p><span class="text-xs text-gray-500">Phone:</span> {{ $company->phone }}</p>
                                <p><span class="text-xs text-gray-500">Email:</span> {{ $company->email }}</p>
                                <p><span class="text-xs text-gray-500">SSM:</span> {{ $company->registration_number }}</p>
                                <p><span class="text-xs text-gray-500">TIN:</span> {{ $company->tin_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bill To (Customer) - Full Width -->
                    <div class="bg-white rounded-xl border-2 border-primary-100 p-6" x-data="customerSearch()">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-xs uppercase tracking-wide font-semibold text-primary-700">Bill To <span class="text-red-500">*</span></h3>
                            <a href="{{ route('user.customers.create') }}" target="_blank" class="inline-flex items-center text-xs text-primary-600 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                New Customer
                            </a>
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

                        <!-- Customer Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <!-- Name (Full Width) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Customer Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" required x-model="customerName"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_name') border-red-500 @enderror"
                                       placeholder="Customer Co. / Person" value="{{ old('customer_name') }}">
                                @error('customer_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                                <input type="email" name="customer_email" x-model="customerEmail"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_email') border-red-500 @enderror"
                                       placeholder="customer@email.com" value="{{ old('customer_email') }}">
                                @error('customer_email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                <input type="text" name="customer_phone" x-model="customerPhone"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_phone') border-red-500 @enderror"
                                       placeholder="+60 12-345 6789" value="{{ old('customer_phone') }}">
                                @error('customer_phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Street Address (Full Width) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Street Address</label>
                                <input type="text" name="customer_street_address" x-model="customerStreetAddress"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_street_address') border-red-500 @enderror"
                                       placeholder="Street address" value="{{ old('customer_street_address') }}">
                                @error('customer_street_address')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">City</label>
                                <input type="text" name="customer_city" x-model="customerCity"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_city') border-red-500 @enderror"
                                       placeholder="City" value="{{ old('customer_city') }}">
                                @error('customer_city')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Postal Code</label>
                                <input type="text" name="customer_postal_code" x-model="customerPostalCode"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_postal_code') border-red-500 @enderror"
                                       placeholder="Postcode" value="{{ old('customer_postal_code') }}">
                                @error('customer_postal_code')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- State (Full Width) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">State</label>
                                <select name="customer_state_id" x-model="customerStateId"
                                        class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_state_id') border-red-500 @enderror">
                                    <option value="">Select state</option>
                                    @foreach(\App\Models\State::orderBy('name')->get() as $state)
                                        <option value="{{ $state->id }}" {{ old('customer_state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_state_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- eInvoice Fields Section -->
                            <div class="md:col-span-2 pt-4 border-t border-gray-200">
                                <h4 class="text-xs font-semibold text-gray-700 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    eInvoice Information (Optional)
                                </h4>
                            </div>

                            <!-- TIN (Full Width) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">TIN</label>
                                <input type="text" name="customer_tin" x-model="customerTin"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_tin') border-red-500 @enderror"
                                       placeholder="Tax Identification Number" value="{{ old('customer_tin') }}">
                                @error('customer_tin')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Document Type -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Document Type</label>
                                <select name="customer_document_type" x-model="customerDocumentType"
                                        class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_document_type') border-red-500 @enderror">
                                    <option value="">Select type</option>
                                    <option value="BRN" {{ old('customer_document_type') == 'BRN' ? 'selected' : '' }}>BRN</option>
                                    <option value="NRIC" {{ old('customer_document_type') == 'NRIC' ? 'selected' : '' }}>NRIC</option>
                                    <option value="PASSPORT" {{ old('customer_document_type') == 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                                    <option value="ARMY" {{ old('customer_document_type') == 'ARMY' ? 'selected' : '' }}>Army ID</option>
                                </select>
                                @error('customer_document_type')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Document Number -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Document Number</label>
                                <input type="text" name="customer_document_number" x-model="customerDocumentNumber"
                                       class="block w-full !px-3.5 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm @error('customer_document_number') border-red-500 @enderror"
                                       placeholder="Document number" value="{{ old('customer_document_number') }}">
                                @error('customer_document_number')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">
                            Items <span class="ml-2 text-xs font-normal text-gray-500">(<span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>)</span>
                        </h3>
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto -mx-3">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600">Description <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Tax</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">Total (incl. tax)</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <template x-for="(item, idx) in items" :key="idx">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text" x-model="item.description" required
                                                   class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                   placeholder="Item description">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                   class="w-20 !px-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">RM</span>
                                                <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                       class="w-28 !pl-10 !pr-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-medium text-gray-900 text-sm" x-text="formatMoney(lineTotalWithTax(item))"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" @click="removeItem(idx)" class="text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <template x-for="(item, idx) in items" :key="'m-'+idx">
                            <div class="bg-white border border-gray-200 rounded-lg p-4 relative">
                                <button type="button" @click="removeItem(idx)" class="absolute top-3 right-3 text-gray-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <div class="space-y-3 pr-8">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="item.description" required
                                               class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                               placeholder="Item description">
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                   class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Unit Price</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">RM</span>
                                                <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                       class="w-full !pl-10 !pr-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>
                                    </div>
                                    <div x-data="{ showCustom: false }">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tax Rate</label>
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
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                        <span class="text-xs text-gray-600">Total (incl. tax)</span>
                                        <span class="font-semibold text-gray-900" x-text="formatMoney(lineTotalWithTax(item))"></span>
                                    </div>
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
                        </div>
                    </template>
                </div>

                <!-- Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="4" class="mt-2 block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Additional information (optional)">{{ old('notes') }}</textarea>
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
                            <div class="flex items-center justify-between border-t border-gray-200 pt-3">
                                <dt class="text-sm font-semibold text-gray-900">Total</dt>
                                <dd class="text-xl font-extrabold text-gray-900" x-text="formatMoney(grandTotal())"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-500">Currency: MYR</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.invoices.index') }}" @click.prevent="confirmCancel($event)" class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">Create Invoice</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function customerSearch() {
            return {
                searchQuery: '',
                searchResults: [],
                showDropdown: false,
                selectedCustomerId: '{{ old('customer_id') }}',
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
                searchTimeout: null,
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
                    this.customerPhone = customer.phone || '';
                    this.customerStreetAddress = customer.street_address || '';
                    this.customerCity = customer.city || '';
                    this.customerStateId = customer.state_id || '';
                    this.customerPostalCode = customer.postal_code || '';
                    this.customerTin = customer.tin || '';
                    this.customerDocumentType = customer.document_type || '';
                    this.customerDocumentNumber = customer.document_number || '';

                    this.searchQuery = customer.name;
                    this.showDropdown = false;
                }
            };
        }

        function invoiceForm() {
            return {
                items: [
                    { description: '', quantity: 1, unit_price: 0, tax_rate: 0 },
                ],
                invoiceNumber: '{{ old('invoice_number', $nextInvoiceNumber) }}',
                invoiceDate: '{{ old('invoice_date', now()->toDateString()) }}',
                dueDate: '{{ old('due_date') }}',
                formChanged: false,
                init() {
                    // Auto-calculate due date on first load if not set
                    if (!this.dueDate) {
                        this.autoCalculateDueDate();
                    }

                    // Track form changes
                    this.$watch('items', () => { this.formChanged = true; }, { deep: true });
                    this.$watch('invoiceDate', () => { this.formChanged = true; });
                    this.$watch('dueDate', () => { this.formChanged = true; });

                    // Keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        // Ctrl/Cmd + Enter to add new item
                        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                            e.preventDefault();
                            this.addItem();
                        }
                    });
                },
                addItem() {
                    this.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0 });
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
                confirmCancel(event) {
                    if (this.formChanged) {
                        if (!confirm('You have unsaved changes. Are you sure you want to cancel?')) {
                            return;
                        }
                    }
                    window.location.href = event.target.href;
                },
                lineTotal(item) {
                    const lt = (Number(item.quantity||0) * Number(item.unit_price||0));
                    return lt;
                },
                lineTotalWithTax(item) {
                    const lt = this.lineTotal(item);
                    const tax = lt * Number((item.tax_rate||0)/100);
                    return lt + tax;
                },
                subtotal() {
                    return this.items.reduce((s,i)=> s + this.lineTotal(i), 0);
                },
                totalTax() {
                    return this.items.reduce((s,i)=> s + (this.lineTotal(i) * Number((i.tax_rate||0)/100)), 0);
                },
                grandTotal() {
                    return this.subtotal() + this.totalTax();
                },
                formatMoney(v) {
                    return 'RM ' + (Number(v||0)).toFixed(2);
                },
                syncItems() {
                    this.formChanged = false; // Reset on submit
                },
            };
        }

        // Warn before leaving page with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            const form = document.querySelector('[x-data]');
            if (form && form.__x && form.__x.$data.formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
@endsection
