@extends('layouts.user-app', ['title' => 'Invoice #' . $invoice->invoice_number])

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
                    @switch($invoice->invoice_status)
                        @case('draft')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Draft
                            </span>
                            @break
                        @case('pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                            @break
                        @case('paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Paid
                            </span>
                            @break
                        @case('cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Cancelled
                            </span>
                            @break
                    @endswitch
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Created {{ $invoice->created_at->format('d M Y, g:i A') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-2">
                <a href="{{ route('user.invoices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Invoices
                </a>

                @if(in_array($invoice->lhdn_status, ['draft', 'rejected', 'invalid']))
                    <a href="{{ route('user.invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                @endif

                <a href="{{ route('user.invoices.pdf', $invoice) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Download PDF
                </a>

                @if($invoice->invoice_status === 'pending')
                    <form action="{{ route('user.invoices.mark-as-paid', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mark as Paid
                        </button>
                    </form>
                @endif

                @if($invoice->invoice_status === 'paid' && in_array($invoice->lhdn_status, ['draft', 'rejected', 'invalid']))
                    <form action="{{ route('user.invoices.submit', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            @if($invoice->lhdn_status === 'rejected' || $invoice->lhdn_status === 'invalid')
                                Resubmit to LHDN
                            @else
                                Submit to LHDN
                            @endif
                        </button>
                    </form>
                @endif

                @if(in_array($invoice->lhdn_status, ['submitted']))
                    <form action="{{ route('user.invoices.check-status', $invoice) }}" method="POST" class="inline ml-2">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Check Status
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Invoice Details Card -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- From & Bill To -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-200">
                <!-- From -->
                <div>
                    <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3">From</h3>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->company->name }}</p>
                        <p class="text-sm text-gray-600">{{ $invoice->company->address_line_1 }}@if($invoice->company->address_line_2), {{ $invoice->company->address_line_2 }}@endif</p>
                        <p class="text-sm text-gray-600">{{ $invoice->company->postcode }} {{ $invoice->company->city }}, {{ $invoice->company->state->name ?? '' }}</p>
                        <p class="text-sm text-gray-600 mt-2">Phone: {{ $invoice->company->phone }}</p>
                        <p class="text-sm text-gray-600">Email: {{ $invoice->company->email }}</p>
                        <p class="text-sm text-gray-600">SSM: {{ $invoice->company->registration_number }}</p>
                        <p class="text-sm text-gray-600">TIN: {{ $invoice->company->tin_number }}</p>
                    </div>
                </div>

                <!-- Bill To -->
                <div>
                    <h3 class="text-xs uppercase tracking-wide font-semibold text-gray-500 mb-3">Bill To</h3>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->customer ? $invoice->customer->name : 'N/A' }}</p>
                        @if($invoice->customer && $invoice->customer->email)
                            <p class="text-sm text-gray-600">{{ $invoice->customer->email }}</p>
                        @endif
                        @if($invoice->customer && $invoice->customer->phone)
                            <p class="text-sm text-gray-600">{{ $invoice->customer->phone }}</p>
                        @endif
                        @if($invoice->customer && ($invoice->customer->street_address || $invoice->customer->city || $invoice->customer->state_id || $invoice->customer->postal_code))
                            <p class="text-sm text-gray-600 mt-2">
                                {{ collect([$invoice->customer->street_address, $invoice->customer->city, $invoice->customer->state_id ? $invoice->customer->state->name : null, $invoice->customer->postal_code, $invoice->customer->country])->filter()->implode(', ') }}
                            </p>
                        @endif
                        @if($invoice->customer && $invoice->customer->tin)
                            <p class="text-sm text-gray-600 mt-2">TIN: {{ $invoice->customer->tin }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Meta -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 p-6 bg-gray-50 border-b border-gray-200">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Document Type</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $invoice->document_type)) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Invoice Date</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Due Date</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->due_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Payment Method</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->payment_method ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Currency</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice->currency }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax %</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">
                                    RM {{ number_format($item->line_total + ($item->line_total * $item->tax_rate / 100), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 px-6 py-4">
                <div class="flex justify-end">
                    <div class="w-full max-w-xs space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold text-gray-900">RM {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax:</span>
                            <span class="font-semibold text-gray-900">RM {{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        @if($invoice->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-red-600">- RM {{ number_format($invoice->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-900">Total:</span>
                            <span class="font-bold text-gray-900">RM {{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($invoice->notes)
                <!-- Notes -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Notes</h4>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif

            @if($invoice->lhdn_status !== 'draft')
                <!-- LHDN Submission Info -->
                <div class="px-6 py-4 bg-blue-50 border-t border-blue-100">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">LHDN Submission Details</h4>
                    <div class="space-y-1 text-sm text-blue-800">
                        @if($invoice->lhdn_submission_id)
                            <p><span class="font-medium">Submission ID:</span> {{ $invoice->lhdn_submission_id }}</p>
                        @endif
                        @if($invoice->lhdn_submitted_at)
                            <p><span class="font-medium">Submitted At:</span> {{ $invoice->lhdn_submitted_at->format('d M Y, g:i A') }}</p>
                        @endif
                        @if($invoice->submitter)
                            <p><span class="font-medium">Submitted By:</span> {{ $invoice->submitter->name }}</p>
                        @endif
                        <p><span class="font-medium">LHDN Status:</span>
                            @switch($invoice->lhdn_status)
                                @case('draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                    @break
                                @case('submitted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Submitted</span>
                                    @break
                                @case('accepted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Accepted</span>
                                    @break
                                @case('valid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Valid</span>
                                    @break
                                @case('invalid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Invalid</span>
                                    @break
                                @case('rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Rejected</span>
                                    @break
                                @case('cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Cancelled</span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($invoice->lhdn_status) }}</span>
                            @endswitch
                        </p>
                        @if($invoice->lhdn_error_message && in_array($invoice->lhdn_status, ['invalid', 'rejected']))
                            @php
                                $error = json_decode($invoice->lhdn_error_message, true);
                                if ($error) {
                                    $message = $error['details'][0]['message'] ?? $error['message'] ?? 'Unknown error';
                                } else {
                                    $message = $invoice->lhdn_error_message;
                                }
                            @endphp
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm font-medium text-red-800">Error Message:</p>
                                <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

