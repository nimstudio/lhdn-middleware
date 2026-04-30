@extends('layouts.user-app', ['title' => 'Create Invoice'])

@section('content')
    <div class="max-w-7xl mx-auto" x-data="invoiceForm()" x-init="init()">
        <!-- Header + Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                    <p class="text-gray-600 mt-1">A blended, productivity-focused layout inspired by Perfex.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('user.invoices.index') }}" @click.prevent="confirmCancel($event)" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    <button type="submit" form="invoice-form" @click="setStatus('draft')" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gray-700 hover:bg-gray-800">Save Draft</button>
                    <button type="submit" form="invoice-form" @click="setStatus('pending'); console.log('Save & Continue clicked')" name="action" value="create" class="px-5 py-2 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700">Save & Continue</button>
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
                        <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3">From</h3>
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
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                <input type="date" name="invoice_date" required x-model="invoiceDate" @change="autoCalculateDueDate()"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('invoice_date') border-red-500 @enderror"
                                       value="{{ old('invoice_date', now()->toDateString()) }}">
                                @error('invoice_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Due Date <span class="text-red-500">*</span></label>
                                <input type="date" name="due_date" required x-model="dueDate"
                                       class="mt-2 w-full !px-4 !py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('due_date') border-red-500 @enderror"
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Currency</label>
                                <input type="text" value="MYR" readonly class="mt-2 w-full !px-4 !py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-700">
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
                    <div class="bg-white rounded-xl border-2 border-primary-100 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs uppercase tracking-wide font-semibold text-primary-700">Bill To <span class="text-red-500">*</span></h3>
                            <a href="{{ route('user.customers.create') }}" target="_blank" class="inline-flex items-center text-xs text-primary-600 hover:text-primary-700 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                New Customer
                            </a>
                        </div>

                        <!-- Simple Customer Selection -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Select Existing Customer (Optional)</label>
                            <select name="customer_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm bg-white">
                                <option value="">Enter new customer details below</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} - {{ $customer->phone }} @if($customer->email)({{ $customer->email }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Select a customer to auto-fill details, or enter new customer information below</p>
                        </div>

                        <!-- Customer Details Form -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Customer Co. / Person" value="{{ old('customer_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" name="customer_email" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="customer@email.com" value="{{ old('customer_email') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">+60</span>
                                    <input type="text" name="customer_phone" required class="block w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="12-345 6789" value="{{ old('customer_phone') }}">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Phone number is required and used to prevent duplicate customers</p>
                            </div>

                            <!-- Address Breakdown -->
                            <div class="pt-2 border-t border-gray-200">
                                <h4 class="text-xs font-semibold text-gray-700 mb-3">Address</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Street Address</label>
                                        <input type="text" name="customer_street_address" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Street address" value="{{ old('customer_street_address') }}">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">City</label>
                                            <input type="text" name="customer_city" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="City" value="{{ old('customer_city') }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">State</label>
                                            <select name="customer_state_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                                <option value="">Select state</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->id }}" {{ old('customer_state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Postal Code</label>
                                            <input type="text" name="customer_postal_code" maxlength="10" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="12345" value="{{ old('customer_postal_code') }}">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Country</label>
                                            <input type="text" name="customer_country" value="MY" readonly class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700" placeholder="Malaysia">
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
                                        <input type="text" name="customer_tin" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Tax Identification Number" value="{{ old('customer_tin') }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Document Type</label>
                                        <select name="customer_document_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                            <option value="">Select type</option>
                                            <option value="BRN" {{ old('customer_document_type') == 'BRN' ? 'selected' : '' }}>BRN</option>
                                            <option value="NRIC" {{ old('customer_document_type') == 'NRIC' ? 'selected' : '' }}>NRIC</option>
                                            <option value="PASSPORT" {{ old('customer_document_type') == 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                                            <option value="ARMY" {{ old('customer_document_type') == 'ARMY' ? 'selected' : '' }}>Army ID</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Document Number</label>
                                        <input type="text" name="customer_document_number" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Document number" value="{{ old('customer_document_number') }}">
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
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Tax %</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in items" :key="idx">
                                    <tr class="border-b border-gray-100">
                                        <td class="px-4 py-3">
                                            <input type="text" x-model="item.description" required class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Item description">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity" class="w-24 !px-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">RM</span>
                                                <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-32 !pl-10 !pr-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate" class="w-24 !px-3 !py-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900" x-text="formatMoney(item.quantity * item.unit_price)">
                                        </td>
                                        <td class="px-4 py-3 text-center">
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
                                        <input type="number" step="0.01" min="0.01" x-model.number="item.quantity" class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Unit Price</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">RM</span>
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price" class="w-full !pl-10 !pr-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-600 mb-1">Tax Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" x-model.number="item.tax_rate" class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                    <span class="text-sm text-gray-600">Total</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="formatMoney(item.quantity * item.unit_price)"></span>
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
                                    <dd class="text-base font-semibold text-gray-900" x-text="formatMoney(discountAmount)"></dd>
                                </div>
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

    <script>
        function invoiceForm() {
            return {
                items: [ { description: '', quantity: 1, unit_price: 0, tax_rate: 0 } ],
                invoiceNumber: '{{ old('invoice_number', $nextInvoiceNumber) }}',
                invoiceDate: '{{ old('invoice_date', now()->toDateString()) }}',
                dueDate: '{{ old('due_date') }}',
                invoiceStatus: '{{ old('invoice_status', 'draft') }}',
                paymentMethod: '{{ old('payment_method', 'Bank Transfer') }}',
                discountAmount: Number('{{ old('discount_amount', 0) }}') || 0,
                formChanged: false,
                init() {
                    if (!this.dueDate) this.autoCalculateDueDate();
                    this.$watch('items', () => { this.formChanged = true; }, { deep: true });
                    this.$watch('invoiceDate', () => { this.formChanged = true; });
                    this.$watch('dueDate', () => { this.formChanged = true; });
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
                        this.formChanged = true;
                    }
                },
                subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },
                totalTax() {
                    return this.items.reduce((sum, item) => {
                        const lineTotal = item.quantity * item.unit_price;
                        return sum + (lineTotal * (item.tax_rate / 100));
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
                syncItems() {
                    console.log('Syncing items...');
                    this.formChanged = false;
                },
                setStatus(status) {
                    console.log('Setting status to:', status);
                    this.invoiceStatus = status;
                    // Update the select field
                    const statusInput = document.querySelector('select[name="invoice_status"]');
                    if (statusInput) {
                        statusInput.value = status;
                        console.log('Status input updated to:', statusInput.value);
                    } else {
                        console.error('Status input not found');
                    }
                },
            };
        }
    </script>
@endsection

@push('scripts')
<script>
    // Simple customer selection - no complex dropdown needed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Invoice form loaded successfully');
    });
</script>
@endpush
