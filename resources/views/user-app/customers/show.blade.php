@extends('layouts.user-app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('user.customers.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-block">
            ← Back to Customers
        </a>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Customer details and invoice history</p>
            </div>
            <div class="flex items-center gap-3">
                @if($customer->is_active)
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                @endif
                <a href="{{ route('user.customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Customer
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customer Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}" class="text-primary-600 hover:text-primary-700">{{ $customer->email }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}" class="text-primary-600 hover:text-primary-700">{{ $customer->phone }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Address -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Address</h2>
                @if($customer->street_address || $customer->city || $customer->state || $customer->postal_code)
                    <address class="not-italic text-sm text-gray-700 space-y-1">
                        @if($customer->street_address)
                            <p>{{ $customer->street_address }}</p>
                        @endif
                        <p>
                            @if($customer->postal_code){{ $customer->postal_code }}@endif
                            @if($customer->city) {{ $customer->city }}@endif
                        </p>
                        @if($customer->state)
                            <p>{{ $customer->state->name }}</p>
                        @endif
                        <p>{{ $customer->country }}</p>
                    </address>
                @else
                    <p class="text-sm text-gray-400">No address on file</p>
                @endif
            </div>

            <!-- eInvoice Information -->
            @if($customer->tin || $customer->document_type || $customer->document_number)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">eInvoice Information</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TIN</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->tin ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Document Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($customer->document_type)
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $customer->document_type }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Document Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->document_number ?: '—' }}</dd>
                        </div>
                    </dl>

                    @if($customer->tin && $customer->document_type && $customer->document_number)
                        <div class="mt-6 border-t pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">TIN Validation</h3>
                                    <p class="text-sm text-gray-500">Validate taxpayer information with LHDN</p>
                                </div>
                                <button type="button"
                                        onclick="validateTaxPayer({{ $customer->id }}, '{{ $customer->tin }}', '{{ $customer->document_type }}', '{{ $customer->document_number }}')"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Validate
                                </button>
                            </div>

                            <div id="validation-result" class="mt-4 hidden">
                                <div class="rounded-md p-4" id="validation-result-content">
                                    <!-- Validation result will be displayed here -->
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Notes -->
            @if($customer->notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $customer->notes }}</p>
                </div>
            @endif

            <!-- Invoice History -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Invoice History</h2>
                    <p class="mt-1 text-sm text-gray-600">Recent invoices for this customer</p>
                </div>

                @if($customer->invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($customer->invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $invoice->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->invoice_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->due_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            RM {{ number_format($invoice->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($invoice->invoice_status)
                                                @case('draft')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                                    @break
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                    @break
                                                @case('paid')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('user.invoices.show', $invoice) }}" class="text-primary-600 hover:text-primary-900">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
                        <p class="mt-1 text-sm text-gray-500">This customer has no invoices yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('user.invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                Create Invoice
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Stats</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">Total Invoices</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $customer->invoices->count() }}</dd>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <dt class="text-xs text-gray-500">Customer Since</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $customer->created_at->format('d M Y') }}</dd>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <dt class="text-xs text-gray-500">Last Updated</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $customer->updated_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('user.invoices.create') }}" class="block w-full px-4 py-2 text-center bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        Create Invoice
                    </a>
                    <a href="{{ route('user.customers.edit', $customer) }}" class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Edit Details
                    </a>
                    <form action="{{ route('user.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full px-4 py-2 text-center border border-red-300 text-red-700 font-medium rounded-lg hover:bg-red-50 transition-colors">
                            Delete Customer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateTaxPayer(customerId, tin, documentType, documentNumber) {
    const button = event.target;
    const originalText = button.innerHTML;
    const resultDiv = document.getElementById('validation-result');
    const resultContent = document.getElementById('validation-result-content');

    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Validating...';

    // Hide previous result
    resultDiv.classList.add('hidden');

    // Make AJAX request
    fetch(`/app/customers/${customerId}/validate-tin`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            tin: tin,
            document_type: documentType,
            document_number: documentNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.classList.remove('hidden');

        if (data.success) {
            resultContent.className = 'rounded-md p-4 bg-green-50 border border-green-200';
            resultContent.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Validation Successful</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>${data.message || 'Taxpayer information is valid.'}</p>
                        </div>
                    </div>
                </div>
            `;
        } else {
            resultContent.className = 'rounded-md p-4 bg-red-50 border border-red-200';
            resultContent.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Validation Failed</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>${data.message || 'Taxpayer information validation failed.'}</p>
                            ${data.details ? `<p class="mt-1">${data.details}</p>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.classList.remove('hidden');
        resultContent.className = 'rounded-md p-4 bg-yellow-50 border border-yellow-200';
        resultContent.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Validation Error</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Unable to validate taxpayer information. Please try again later.</p>
                    </div>
                </div>
            </div>
        `;
        console.error('Validation error:', error);
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>
@endsection
