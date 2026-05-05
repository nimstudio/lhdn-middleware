@extends('layouts.user-app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">{{ $title ?? 'Invoices' }}</h1>
                <p class="mt-2 text-sm text-gray-700">A list of all your invoices including their current status.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
                @else
                    <div class="flex space-x-3">
                        <button onclick="openBulkUploadModal()"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 sm:w-auto">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Bulk Upload
                        </button>
                        <a href="{{ route('user.invoices.create') }}"
                           class="inline-flex items-center justify-center rounded-md border border-transparent bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 sm:w-auto">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Invoice
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <form method="GET" action="{{ route('user.invoices.index') }}" class="space-y-4">
                <!-- Search and Per Page Row -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Search -->
                    <div class="flex-1 max-w-md">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by invoice number, customer name..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Per Page -->
                    <div class="flex items-center space-x-4">
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                        <select name="per_page" id="per_page" class="block pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm rounded-md">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <!-- Advanced Filters Toggle -->
                    <button type="button"
                            onclick="toggleAdvancedFilters()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                        </svg>
                        Advanced Filters
                    </button>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advancedFilters" class="hidden space-y-4 pt-4 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Invoice Status -->
                        <div>
                            <label for="invoice_status" class="block text-sm font-medium text-gray-700 mb-1">Invoice Status</label>
                            <select name="invoice_status" id="invoice_status" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm rounded-md">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('invoice_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ request('invoice_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('invoice_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ request('invoice_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>


                        <!-- Document Type -->
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                            <select name="document_type" id="document_type" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm rounded-md">
                                <option value="">All Document Types</option>
                                <option value="01" {{ request('document_type') == '01' ? 'selected' : '' }}>Invoice</option>
                                <option value="02" {{ request('document_type') == '02' ? 'selected' : '' }}>Credit Note</option>
                                <option value="03" {{ request('document_type') == '03' ? 'selected' : '' }}>Debit Note</option>
                                <option value="04" {{ request('document_type') == '04' ? 'selected' : '' }}>Refund Note</option>
                                <option value="11" {{ request('document_type') == '11' ? 'selected' : '' }}>Self-billed Invoice</option>
                                <option value="12" {{ request('document_type') == '12' ? 'selected' : '' }}>Self-billed Credit Note</option>
                                <option value="13" {{ request('document_type') == '13' ? 'selected' : '' }}>Self-billed Debit Note</option>
                                <option value="14" {{ request('document_type') == '14' ? 'selected' : '' }}>Self-billed Refund Note</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                        </div>

                        <!-- Amount Range -->
                        <div>
                            <label for="amount_min" class="block text-sm font-medium text-gray-700 mb-1">Amount Range (RM)</label>
                            <div class="flex space-x-2">
                                <input type="number"
                                       name="amount_min"
                                       id="amount_min"
                                       value="{{ request('amount_min') }}"
                                       placeholder="Min"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                                <input type="number"
                                       name="amount_max"
                                       id="amount_max"
                                       value="{{ request('amount_max') }}"
                                       placeholder="Max"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <button type="button"
                                onclick="clearFilters()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear Filters
                        </button>

                        <div class="flex space-x-3">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @if($invoices->count() > 0)
                <!-- Results Summary (merged with filters) -->
                <div class="pt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                             @if(request()->hasAny(['search', 'invoice_status', 'document_type', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                                 <span class="text-gray-500">(filtered)</span>
                             @endif
                        </p>
                        <div class="flex items-center space-x-2">
                             @if(request()->hasAny(['search', 'invoice_status', 'document_type', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                                 <a href="{{ route('user.invoices.index') }}"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                     Clear all filters
                                 </a>
                             @endif
                        </div>
                    </div>
                </div>
        </div>

            <!-- Invoices Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Invoices</h3>
                            @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Cancel accepted invoices within {{ $cancel_period_hours ?? 72 }} hours of creation</p>
                            @else
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage your invoices and track their status</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('sort') === 'invoice_number' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="group inline-flex items-center hover:text-gray-700">
                                        Invoice
                                        <svg class="ml-1 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Status</th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LHDN Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                                @else
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('user.invoices.show', $invoice) }}" class="text-brand-600 hover:text-brand-900">
                                                        {{ $invoice->invoice_number }}
                                                    </a>
                                                </div>
                                                 <div class="text-sm text-gray-500">
                                                     {{ $invoice->invoiceType ? $invoice->invoiceType->description : ($invoice->document_type ?? 'Not specified') }}
                                                 </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $invoice->customer ? $invoice->customer->name : 'N/A' }}</div>
                                        @if($invoice->customer && $invoice->customer->email)
                                            <div class="text-sm text-gray-500">{{ $invoice->customer->email }}</div>
                                        @endif
                                    </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          <div class="text-sm text-gray-900">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                                      </td>
                                     @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                                     <td class="px-6 py-4 whitespace-nowrap">
                                         @switch($invoice->invoice_status)
                                             @case('draft')
                                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                     <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                                         <circle cx="4" cy="4" r="3"/>
                                                     </svg>
                                                     Draft
                                                 </span>
                                                 @break
                                             @case('pending')
                                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                     <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                                         <circle cx="4" cy="4" r="3"/>
                                                     </svg>
                                                     Pending
                                                 </span>
                                                 @break
                                             @case('paid')
                                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                     <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                                         <circle cx="4" cy="4" r="3"/>
                                                     </svg>
                                                     Paid
                                                 </span>
                                                 @break
                                             @case('cancelled')
                                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                     <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                                         <circle cx="4" cy="4" r="3"/>
                                                     </svg>
                                                     Cancelled
                                                 </span>
                                                 @break
                                             @default
                                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                     {{ ucfirst($invoice->invoice_status) }}
                                                 </span>
                                         @endswitch
                                     </td>
                                     @endif
                                     <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($invoice->lhdn_status)
                                                @case('draft')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Draft
                                                    </span>
                                                    @break
                                                @case('pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('submitted')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Submitted
                                                    </span>
                                                    @break
                                                @case('accepted')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Accepted
                                                    </span>
                                                    @break
                                                @case('rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($invoice->lhdn_status) }}
                                                    </span>
                                            @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                                    RM {{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                             @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                                             <a href="{{ route('user.invoices.show', $invoice) }}"
                                                class="text-brand-600 hover:text-brand-900">
                                                 View
                                             </a>
                                              @if(in_array($invoice->lhdn_status, ['draft', 'rejected', 'invalid']))
                                                  <a href="{{ route('user.invoices.edit', $invoice) }}"
                                                     class="text-gray-600 hover:text-gray-900">
                                                      Edit
                                                  </a>
                                              @endif
                                             <a href="{{ route('user.invoices.pdf', $invoice) }}"
                                                class="text-gray-600 hover:text-gray-900">
                                                 PDF
                                             </a>
                                             @else
                                                     @if($invoice->lhdn_status === 'valid')
                                                         <form method="POST" action="{{ route('user.invoices.cancel-invoice') }}" class="inline">
                                                             @csrf
                                                             <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                                             <input type="hidden" name="reason" value="Customer refund">
                                                             <button type="submit"
                                                                     onclick="return confirm('Are you sure you want to cancel this invoice?')"
                                                                     class="text-xs text-red-600 hover:text-red-900">
                                                                 Cancel
                                                             </button>
                                                         </form>
                                                     @elseif($invoice->lhdn_status === 'invalid')
                                                         <span class="text-xs text-gray-500" title="Document has validation errors and cannot be cancelled">
                                                             Invalid Document
                                                         </span>
                                                     @else
                                                         @switch($invoice->lhdn_status)
                                                             @case('cancelled')
                                                                 <span class="text-xs text-gray-600">Already Cancelled</span>
                                                                 @break
                                                             @case('rejected')
                                                                 <span class="text-xs text-orange-600">Rejected</span>
                                                                 @break
                                                             @default
                                                                 <span class="text-xs text-gray-500">Status: {{ ucfirst($invoice->lhdn_status) }}</span>
                                                         @endswitch
                                                     @endif
                                             @endif
                                         </div>
                                     </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden">
                    @foreach($invoices as $invoice)
                        <div class="border-b border-gray-200 p-4">

                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                            <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-lg bg-brand-100 flex items-center justify-center">
                                                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('user.invoices.show', $invoice) }}" class="text-brand-600 hover:text-brand-900">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-500">{{ $invoice->customer ? $invoice->customer->name : 'Customer Deleted' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex flex-col space-y-1">
                                             <div class="flex items-center space-x-2">
                                                 @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                                                 @switch($invoice->invoice_status)
                                                     @case('draft')
                                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                                         @break
                                                     @case('pending')
                                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                                         @break
                                                     @case('paid')
                                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                                                         @break
                                                     @case('cancelled')
                                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                                         @break
                                                 @endswitch
                                                 @endif
                                                 @switch($invoice->lhdn_status)
                                                    @case('draft')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">LHDN: Draft</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">LHDN: Pending</span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">LHDN: Submitted</span>
                                                        @break
                                                    @case('accepted')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">LHDN: Accepted</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">LHDN: Rejected</span>
                                                        @break
                                                @endswitch
                                                </div>
                                            <p class="text-xs text-gray-500">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                                                </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">RM {{ number_format($invoice->total_amount, 2) }}</p>
                                             <div class="flex items-center space-x-2 mt-1">
                                                 @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                                                 <a href="{{ route('user.invoices.show', $invoice) }}" class="text-xs text-brand-600 hover:text-brand-900">View</a>
                                                  @if(in_array($invoice->lhdn_status, ['draft', 'rejected', 'invalid']))
                                                      <a href="{{ route('user.invoices.edit', $invoice) }}" class="text-xs text-gray-600 hover:text-gray-900">Edit</a>
                                                  @endif
                                                 <a href="{{ route('user.invoices.pdf', $invoice) }}" class="text-xs text-gray-600 hover:text-gray-900">PDF</a>
                                             @else
                                                 @if($invoice->lhdn_status === 'valid')
                                                     <form method="POST" action="{{ route('user.invoices.cancel-invoice') }}" class="inline">
                                                         @csrf
                                                         <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                                         <input type="hidden" name="reason" value="Customer refund">
                                                         <button type="submit"
                                                                 onclick="return confirm('Are you sure you want to cancel this invoice?')"
                                                                 class="text-red-600 hover:text-red-900">
                                                             Cancel
                                                         </button>
                                                     </form>
                                                 @elseif($invoice->lhdn_status === 'invalid')
                                                     <span class="text-gray-500 text-xs" title="Document has validation errors and cannot be cancelled">
                                                         Invalid Document
                                                     </span>
                                                 @else
                                                     <span class="text-gray-500 text-xs" title="Document status: {{ $invoice->lhdn_status }}">
                                                         @switch($invoice->lhdn_status)
                                                             @case('cancelled')
                                                                 <span class="text-gray-600">Already Cancelled</span>
                                                                 @break
                                                             @case('rejected')
                                                                 <span class="text-orange-600">Rejected</span>
                                                                 @break
                                                             @default
                                                                 <span class="text-gray-500">Status: {{ ucfirst($invoice->lhdn_status) }}</span>
                                                         @endswitch
                                                     </span>
                                                 @endif
                                                 @endif
                                             </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($invoices->onFirstPage())
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                                    Previous
                                </span>
                            @else
                                <a href="{{ $invoices->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            @endif

                            @if($invoices->hasMorePages())
                                <a href="{{ $invoices->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            @else
                                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                                    Next
                                </span>
                            @endif
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                                </p>
                            </div>
                            <div>
                                {{ $invoices->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
                        @if(request()->routeIs('user.invoices.cancellation'))
                            No cancellable invoices
                        @else
                            No rejected invoices
                        @endif
                    @else
                        No invoices
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
                        @if(request()->routeIs('user.invoices.cancellation'))
                            No accepted invoices found within the cancellation period.
                        @else
                            No rejected invoices found within the processing period.
                        @endif
                    @else
                        Get started by creating a new invoice.
                    @endif
                </p>
                @if(!request()->routeIs('user.invoices.cancellation') && !request()->routeIs('user.invoices.rejection'))
                <div class="mt-6">
                    <a href="{{ route('user.invoices.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Invoice
                    </a>
                </div>
                @endif
            </div>
        @endif
    </div>
    @if(request()->routeIs('user.invoices.cancellation') || request()->routeIs('user.invoices.rejection'))
    </form>
    @endif

    <!-- Bulk Upload Modal -->
    <div id="bulkUploadModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:h-screen" aria-hidden="true">​</span>
            <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:mt-8 sm:max-w-6xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Bulk Upload Invoices</h3>
                            <div class="mt-4">
                                <!-- Buttons -->
                                <div class="flex justify-between space-x-3 mb-4">
                                    <!-- File Upload Input -->
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('user.invoices.bulk-upload-template') }}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download Template
                                        </a>
                                        <input type="file"
                                               id="excelFileInput"
                                               accept=".xlsx,.xls"
                                               class="hidden"
                                               onchange="handleFileSelect(event)">
                                        <button type="button"
                                                onclick="document.getElementById('excelFileInput').click()"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Choose File
                                        </button>
                                        <span id="fileNameDisplay" class="text-sm text-gray-500">No file selected</span>
                                        <button type="button"
                                                id="uploadButton"
                                                onclick="uploadFile()"
                                                disabled
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Upload
                                        </button>
                                    </div>
                                    <button type="button"
                                            onclick="closeBulkUploadModal()"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                        Close
                                    </button>
                                </div>
                                <!-- Main Content -->
                                <div class="flex flex-col lg:flex-row gap-6 min-h-0">
                                    <!-- Left Side: Invoice List (30% width) -->
                                    <div class="w-full lg:flex-1 lg:basis-0" style="flex-basis: 30%;">
                                        <div class="bg-gray-50 rounded-lg p-4 h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="text-sm font-medium text-gray-900">Uploaded Invoices</h4>
                                                <button onclick="submitAllInvoices()"
                                                        id="bulkSubmitBtn"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg class="-ml-1 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                    </svg>
                                                    Submit All
                                                </button>
                                            </div>
                                            <div id="invoiceList" class="space-y-2 max-h-96 overflow-y-auto">
                                                <!-- Invoice list will be populated here -->
                                                <div class="text-sm text-gray-500">No invoices uploaded yet.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Right Side: Invoice Preview (70% width) -->
                                    <div class="w-full lg:flex-1 lg:basis-0" style="flex-basis: 70%;">
                                        <div class="bg-gray-50 rounded-lg p-4 h-full">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Invoice Preview</h4>
                                            <div id="invoicePreview" class="max-h-96 overflow-y-auto">
                                                <!-- Selected invoice details will be displayed here -->
                                                <div class="text-sm text-gray-500">Select an invoice to preview details.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            const isHidden = filters.classList.contains('hidden');

            if (isHidden) {
                filters.classList.remove('hidden');
                // Auto-expand if any filters are active
                const hasActiveFilters = document.querySelector('#invoice_status').value ||
                                       document.querySelector('#payment_method').value ||
                                       document.querySelector('#date_from').value ||
                                       document.querySelector('#date_to').value ||
                                       document.querySelector('#amount_min').value ||
                                       document.querySelector('#amount_max').value;

                if (hasActiveFilters) {
                    filters.classList.add('block');
                }
            } else {
                filters.classList.add('hidden');
            }
        }

        function clearFilters() {
            // Clear all form fields
            document.querySelector('#search').value = '';
            document.querySelector('#invoice_status').value = '';
            document.querySelector('#payment_method').value = '';
            document.querySelector('#date_from').value = '';
            document.querySelector('#date_to').value = '';
            document.querySelector('#amount_min').value = '';
            document.querySelector('#amount_max').value = '';

            // Reset per page to default
            document.querySelector('#per_page').value = '15';

            // Submit the form to reload with cleared filters
            document.querySelector('form').submit();
        }

        // Auto-expand advanced filters if any are active on page load
        document.addEventListener('DOMContentLoaded', function() {
            const hasActiveFilters = '{{ request()->hasAny(['invoice_status', 'payment_method', 'date_from', 'date_to', 'amount_min', 'amount_max']) }}';

            if (hasActiveFilters === '1') {
                const filters = document.getElementById('advancedFilters');
                filters.classList.remove('hidden');
            }
        });

        // Auto-submit form when per page changes
        document.querySelector('#per_page').addEventListener('change', function() {
            this.form.submit();
        });

        function openBulkUploadModal() {
            document.getElementById('bulkUploadModal').classList.remove('hidden');
        }



        function closeBulkUploadModal() {
            document.getElementById('bulkUploadModal').classList.add('hidden');
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const uploadButton = document.getElementById('uploadButton');

            if (file) {
                fileNameDisplay.textContent = file.name;
                uploadButton.disabled = false;
            } else {
                fileNameDisplay.textContent = 'No file selected';
                uploadButton.disabled = true;
            }
        }

        function uploadFile() {
            const fileInput = document.getElementById('excelFileInput');
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select a file first.');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', file);
            formData.append('_token', '{{ csrf_token() }}');

            const uploadButton = document.getElementById('uploadButton');
            const originalText = uploadButton.innerHTML;
            uploadButton.disabled = true;
            uploadButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            fetch('{{ route("user.invoices.bulk-upload-process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayProcessedData(data.invoice_count, data.line_item_count, data.invoice_data, data.line_item_data);
                    //alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                alert('An error occurred during upload.');
            })
            .finally(() => {
                uploadButton.disabled = false;
                uploadButton.innerHTML = originalText;
            });
        }

        // Global variables to store processed data
        let processedInvoiceData = [];
        let processedLineItemData = [];
        let selectedInvoiceIndex = -1;

        function displayProcessedData(invoiceCount, lineItemCount, invoiceData, lineItemData) {
            // Store the data globally
            processedInvoiceData = invoiceData || [];
            processedLineItemData = lineItemData || [];

            const invoiceList = document.getElementById('invoiceList');
            const invoicePreview = document.getElementById('invoicePreview');

            // Update invoice list with clickable invoice cards
            let invoiceListHtml = '<div class="space-y-2">';
            if (processedInvoiceData.length > 0) {
                processedInvoiceData.forEach((invoice, index) => {
                    const invoiceNumber = invoice['Invoice Number'] || `Invoice ${index + 1}`;
                    const customerName = invoice['Customer Name'] || 'Unknown Customer';
                    const totalAmount = invoice['Total Amount'] || '0.00';

                    invoiceListHtml += `
                        <div class="bg-white p-3 rounded border cursor-pointer hover:bg-gray-50 transition-colors ${
                            selectedInvoiceIndex === index ? 'ring-2 ring-brand-500 bg-brand-50' : ''
                        }" onclick="selectInvoice(${index})">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">${invoiceNumber}</div>
                                    <div class="text-sm text-gray-500">${customerName}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">RM ${parseFloat(totalAmount).toFixed(2)}</div>
                                    <div class="text-xs text-gray-500">Click to preview</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                invoiceListHtml += '<div class="text-sm text-gray-500">No invoices found</div>';
            }
            invoiceListHtml += '</div>';
            invoiceList.innerHTML = invoiceListHtml;

            // Update preview section - show first invoice by default if available
            if (processedInvoiceData.length > 0) {
                selectInvoice(0);
            } else {
                invoicePreview.innerHTML = `
                    <div class="text-sm text-gray-500">Upload an Excel file to view invoice details.</div>
                `;
            }
        }

        function selectInvoice(index) {
            selectedInvoiceIndex = index;
            const invoice = processedInvoiceData[index];
            const invoicePreview = document.getElementById('invoicePreview');

            if (!invoice) return;

            // Get line items for this invoice
            const invoiceNumber = invoice['Invoice Number'];
            const relatedLineItems = processedLineItemData.filter(item =>
                item['Invoice Number'] === invoiceNumber
            );

            // Update preview with selected invoice details and line items
            let lineItemsHtml = '';
            if (relatedLineItems.length > 0) {
                lineItemsHtml = `
                    <div class="mt-4">
                        <h6 class="font-medium text-gray-900 mb-3">Line Items</h6>
                        <div class="space-y-2">
                            ${relatedLineItems.map(item => `
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">${item['Description'] || 'No description'}</div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                Quantity: ${item['Quantity'] || 0} × RM ${parseFloat(item['Unit Price'] || 0).toFixed(2)}
                                                ${item['Tax Rate'] ? ` | Tax: ${item['Tax Rate']}%` : ''}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium text-gray-900">RM ${parseFloat(item['Line Total'] || 0).toFixed(2)}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            invoicePreview.innerHTML = `
                <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white">INVOICE</h1>
                                <p class="text-brand-100 text-sm">#${invoice['Invoice Number'] || 'N/A'}</p>
                            </div>
                            <div class="text-right text-white">
                                <div class="text-3xl font-bold">RM ${parseFloat(invoice['Total Amount'] || 0).toFixed(2)}</div>
                                <div class="text-sm opacity-90">Total Amount</div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-left">
                                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Invoice Details</h3>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Document Type:</span> ${invoice['Document Type'] || 'N/A'}</div>
                                    <div><span class="font-medium">Invoice Date:</span> ${invoice['Invoice Date'] || 'N/A'}</div>
                                    <div><span class="font-medium">Billing Start:</span> ${invoice['Billing Start'] || 'N/A'}</div>
                                    <div><span class="font-medium">Billing End:</span> ${invoice['Billing End'] || 'N/A'}</div>
                                    <div><span class="font-medium">Currency:</span> ${invoice['Currency'] || 'N/A'}</div>
                                    <div><span class="font-medium">Payment Method:</span> ${invoice['Payment Method '] || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="md:col-span-2 text-right">
                                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Bill To</h3>
                                <div class="space-y-1 text-sm">
                                    <div class="font-semibold text-gray-900 text-right">${invoice['Customer Name'] || 'N/A'}</div>
                                    ${invoice['Customer Email'] ? `<div class="text-gray-600 text-right">${invoice['Customer Email']}</div>` : ''}
                                    ${invoice['Customer Phone'] ? `<div class="text-gray-600 text-right">${invoice['Customer Phone']}</div>` : ''}
                                    <div class="text-gray-600 mt-2 text-right">${invoice['Customer Street Address'] || ''} ${invoice['Customer City'] || ''} ${invoice['Customer State'] || ''} ${invoice['Customer Postal Code'] || ''}</div>
                                    ${invoice['Customer Country'] ? `<div class="text-gray-600 text-right">${invoice['Customer Country']}</div>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Rate</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${relatedLineItems.map(item => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">${item['Description'] || 'No description'}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${item['Quantity'] || 0}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">RM ${parseFloat(item['Unit Price'] || 0).toFixed(2)}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${item['Tax Rate'] || 0}%</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">RM ${parseFloat(item['Line Total'] || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <div class="max-w-sm ml-auto text-right">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Subtotal:</span>
                                    <span class="text-gray-900">RM ${parseFloat(invoice['Subtotal'] || 0).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Tax Amount:</span>
                                    <span class="text-gray-900">RM ${parseFloat(invoice['Tax Amount'] || 0).toFixed(2)}</span>
                                </div>
                                ${parseFloat(invoice['Discount Amount'] || 0) > 0 ? `
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Discount:</span>
                                    <span class="text-red-600">-RM ${parseFloat(invoice['Discount Amount'] || 0).toFixed(2)}</span>
                                </div>
                                ` : ''}
                                <div class="flex justify-between pt-2 border-t border-gray-300">
                                    <span class="font-bold text-gray-900 text-lg">Total:</span>
                                    <span class="font-bold text-brand-600 text-lg">RM ${parseFloat(invoice['Total Amount'] || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    ${invoice['Notes'] ? `
                    <div class="px-6 py-4 border-t bg-gray-50">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">Notes</h4>
                        <p class="text-sm text-gray-700">${invoice['Notes']}</p>
                    </div>
                    ` : ''}
                </div>
            `;

            // Update the selected styling in the list
            const invoiceListItems = document.querySelectorAll('#invoiceList > div > div');
            invoiceListItems.forEach((item, idx) => {
                if (idx === index) {
                    item.classList.add('ring-2', 'ring-brand-500', 'bg-brand-50');
                } else {
                    item.classList.remove('ring-2', 'ring-brand-500', 'bg-brand-50');
                }
            });
        }

        function submitAllInvoices() {
            if (processedInvoiceData.length === 0) {
                alert('No invoices to submit. Please upload an Excel file first.');
                return;
            }

            const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
            const originalText = bulkSubmitBtn.innerHTML;
            bulkSubmitBtn.disabled = true;
            bulkSubmitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Submitting...';

            // Prepare invoice data for bulk submission
            const invoiceIds = processedInvoiceData.map(invoice => ({
                invoice_number: invoice['Invoice Number'],
                // Add other necessary fields for bulk submission
            }));

            fetch('{{ route("user.invoices.bulk-create-and-submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('All invoices submitted successfully!');
                    // Optionally refresh the page or update UI
                    location.reload();
                } else {
                    alert('Error submitting invoices: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Bulk submit error:', error);
                alert('An error occurred during bulk submission.');
            })
            .finally(() => {
                bulkSubmitBtn.disabled = false;
                bulkSubmitBtn.innerHTML = originalText;
            });
        }
    </script>
@endsection
