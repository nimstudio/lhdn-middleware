@extends('layouts.user-app', ['title' => 'Dashboard'])

@section('content')
    <!-- Quick Actions temporarily hidden -->
    {{-- Quick Actions hidden per request --}}

    <!-- Getting Started Widget (only show if no company) -->
    @if (!$checklist['company'])
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-primary-50 to-brand-50 px-6 py-4 border-b border-primary-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Complete Your Setup</h3>
                        <p class="text-sm text-gray-600 mt-1">Set up your company to start creating invoices</p>
                    </div>
                </div>
            </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Activate Subscription (Moved to top) -->
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex-shrink-0">
                                @if($checklist['subscription'])
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-sm font-semibold {{ $checklist['subscription'] ? 'text-gray-500' : 'text-gray-900' }}">
                                    Activate Subscription
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Complete payment to access all features</p>
                            </div>
                            @if(!$checklist['subscription'])
                                <a href="{{ route('payment.index') }}" class="ml-4 inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Activate Now
                                </a>
                            @endif
                        </div>

                        <!-- Setup Company -->
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex-shrink-0">
                                @if($checklist['company'])
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-sm font-semibold {{ $checklist['company'] ? 'text-gray-500' : 'text-gray-900' }}">
                                    Setup Company Information
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Add your business details and registration information</p>
                            </div>
                            @if(!$checklist['company'])
                                <a href="{{ route('user.company.edit') }}" class="ml-4 inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Setup Now
                                </a>
                            @endif
                        </div>

                        <!-- Add Credentials -->
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex-shrink-0">
                                @if($checklist['credentials'])
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-sm font-semibold {{ $checklist['credentials'] ? 'text-gray-500' : 'text-gray-900' }}">
                                    Add LHDN API Credentials
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Configure your LHDN MyInvois API access</p>
                            </div>
                            @if(!$checklist['credentials'])
                                <a href="{{ route('user.credentials.create') }}" class="ml-4 inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Add Now
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    <!-- LHDN Credentials Notice (only show if user has company but no credentials) -->
    @if($checklist['company'] && !$checklist['credentials'])
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-accent-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">Ready to Submit to LHDN?</h3>
                        <p class="text-sm text-gray-600 mt-1">Add your LHDN API credentials to start submitting invoices directly to LHDN MyInvois system.</p>
                    </div>
                    <div class="ml-4">
                        <a href="{{ route('user.credentials.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                            Add LHDN Credentials
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Overview (only show if user has company) -->
    @if($checklist['company'])
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Invoices -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Invoices</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $invoiceStats['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">All time</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    </div>

        <!-- Pending Invoices -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $invoiceStats['pending'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Awaiting submission</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    </div>

        <!-- Submitted Invoices -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Submitted</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $invoiceStats['submitted'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Sent to LHDN</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                    </div>
                    </div>

        <!-- Approved Invoices -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Approved</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $invoiceStats['approved'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">LHDN accepted</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                </div>
            </div>
        </div>
        </div>
    @endif

    <!-- Recent Invoices (only show if user has company) -->
    @if($checklist['company'])
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-primary-50 to-primary-100 px-6 py-4 border-b border-primary-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-900">Recent Invoices</h3>
                            <p class="text-sm text-gray-600 mt-1">Your latest invoice activity</p>
                        </div>
                    </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('user.invoices.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-primary-200 text-sm font-medium rounded-lg text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        View All
                    </a>
                    <a href="{{ route('user.invoices.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200 shadow-sm">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Invoice
                    </a>
                </div>
                </div>
            </div>

            @if($recentInvoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentInvoices->take(10) as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $invoice->customer_name }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm 
                                    @if($invoice->due_date && $invoice->due_date < now() && $invoice->invoice_status !== 'paid')
                                        text-red-600 font-medium
                                    @else
                                        text-gray-500
                                    @endif">
                                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}
                                    @if($invoice->due_date && $invoice->due_date < now() && $invoice->invoice_status !== 'paid')
                                        <span class="ml-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Overdue</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">RM {{ number_format($invoice->total_amount, 2) }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @switch($invoice->invoice_status)
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
                                    @case('paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                    @break
                                    @case('cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Cancelled
                                    </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('user.invoices.show', $invoice) }}" 
                                       class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        View
                                    </a>
                                    @if(in_array($invoice->invoice_status, ['draft', 'pending']))
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('user.invoices.edit', $invoice) }}" 
                                       class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        Edit
                                    </a>
                                    @endif
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('user.invoices.pdf', $invoice) }}" 
                                       class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="text-center py-16">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No invoices yet</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Get started by creating your first invoice and submitting it to LHDN MyInvois.</p>
                <a href="{{ route('user.invoices.create') }}"
                   class="inline-flex items-center px-8 py-4 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="-ml-1 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create First Invoice
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- API Key Generator -->
    @if($checklist['company'])
    <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- API Key Generator -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-bold text-gray-900">API Key</h3>
                        <p class="text-sm text-gray-500">Generate API key for external integrations</p>
                    </div>
                </div>
            </div>

            <!-- API Key Display -->
            <div id="api-key-display" class="mb-4">
                @if($apiKeyInfo['has_key'])
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Your API Key</p>
                        <div class="flex items-center justify-between">
                            <code id="api-key-value" class="text-sm font-mono text-gray-800 bg-transparent border-none outline-none select-all">{{ $apiKeyInfo['masked_key'] }}</code>
                            <button onclick="copyApiKey()" class="ml-2 p-1.5 text-gray-500 hover:text-primary-600 hover:bg-gray-100 rounded transition-colors" title="Copy API Key">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        @if($apiKeyInfo['created_at'])
                            <p class="text-xs text-gray-400 mt-2">Created: {{ $apiKeyInfo['created_at'] }}</p>
                        @endif
                    </div>
                @else
                    <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                        <p class="text-sm text-yellow-800">No API key generated yet</p>
                    </div>
                @endif
            </div>

            <!-- Generate Button -->
            <button onclick="generateApiKey()" id="generate-key-btn" class="w-full px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors duration-200 flex items-center justify-center gap-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ $apiKeyInfo['has_key'] ? 'Regenerate Key' : 'Generate Key' }}
            </button>

            <!-- Loading State -->
            <div id="api-key-loading" class="hidden mt-3 text-center">
                <div class="inline-flex items-center px-3 py-2 rounded-lg text-sm text-gray-600">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generating...
                </div>
            </div>

            <!-- Success Message -->
            <div id="api-key-success" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800">API key generated! Copy it now - you won't see it again.</p>
            </div>

            <!-- Error Message -->
            <div id="api-key-error" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800" id="error-message">Failed to generate API key</p>
            </div>
        </div>
        </div>
    @endif

    <!-- Helpful Tips Section (only show if user has company and no invoices) -->
    {{-- @if($checklist['company'] && $invoiceStats['total'] == 0)
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Getting Started Tips</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>• Ensure your company information is complete and accurate</p>
                        <p>• Configure your LHDN MyInvois API credentials</p>
                        <p>• Create your first invoice and submit it to LHDN</p>
                        <p>• Monitor invoice status and handle any rejections</p>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}

@push('scripts')
<script>
    // Store the generated API key in memory (only shown once)
    let tempApiKey = null;

    async function generateApiKey() {
        const generateBtn = document.getElementById('generate-key-btn');
        const loadingDiv = document.getElementById('api-key-loading');
        const successDiv = document.getElementById('api-key-success');
        const errorDiv = document.getElementById('api-key-error');
        const apiKeyDisplay = document.getElementById('api-key-display');
        const errorMessage = document.getElementById('error-message');

        // Hide previous messages
        successDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');

        // Show loading state
        generateBtn.disabled = true;
        generateBtn.classList.add('opacity-50', 'cursor-not-allowed');
        loadingDiv.classList.remove('hidden');

        try {
            const response = await fetch('{{ route('user.api-key.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Store the API key in memory for this session
                tempApiKey = data.api_key;

                // Show the full API key
                apiKeyDisplay.innerHTML = `
                    <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                        <p class="text-xs text-gray-500 mb-1">Your API Key (copy now!)</p>
                        <div class="flex items-center justify-between">
                            <code id="api-key-value" class="text-sm font-mono text-green-800 bg-transparent border-none outline-none select-all">${data.api_key}</code>
                            <button onclick="copyApiKey()" class="ml-2 p-1.5 text-gray-500 hover:text-primary-600 hover:bg-gray-100 rounded transition-colors" title="Copy API Key">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-green-600 mt-2">Created: ${new Date(data.created_at).toLocaleString()}</p>
                    </div>
                `;

                successDiv.classList.remove('hidden');

                // Auto-copy to clipboard
                copyApiKey();
            } else {
                errorMessage.textContent = data.message || 'Failed to generate API key';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error generating API key:', error);
            errorMessage.textContent = 'An error occurred while generating the API key';
            errorDiv.classList.remove('hidden');
        } finally {
            // Reset button state
            generateBtn.disabled = false;
            generateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            loadingDiv.classList.add('hidden');
        }
    }

    function copyApiKey() {
        // Try to use the temporary key first, otherwise try the displayed value
        let textToCopy = tempApiKey;
        
        if (!textToCopy) {
            const apiKeyElement = document.getElementById('api-key-value');
            if (apiKeyElement) {
                textToCopy = apiKeyElement.textContent;
            }
        }

        // Don't copy if it looks like a masked key (contains '...')
        if (textToCopy && !textToCopy.includes('...')) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Show brief success indicator
                const btn = document.querySelector('[onclick="copyApiKey()"]');
                if (btn) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                    }, 2000);
                }
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    }
</script>
@endpush
@endsection
