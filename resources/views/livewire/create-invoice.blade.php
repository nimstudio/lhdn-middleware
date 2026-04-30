<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
            <p class="text-gray-600 mt-1" wire:loading.remove>
                @if($documentType === 'credit_note')
                    Create a credit note to reduce the amount owed by the customer.
                @elseif($documentType === 'debit_note')
                    Create a debit note to increase the amount owed by the customer.
                @elseif($documentType === 'self_billed_invoice')
                    Create a self-billed invoice where the buyer issues the invoice to themselves.
                @else
                    Create a new invoice to bill your customer.
                @endif
            </p>
        </div>

        <form wire:submit.prevent="save('draft')" class="divide-y divide-gray-200">
            <!-- Document Type & Invoice Details -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Document Type</label>
                        <select wire:model="documentType" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="invoice">Invoice</option>
                            <option value="credit_note">Credit Note</option>
                            <option value="debit_note">Debit Note</option>
                            <option value="self_billed_invoice">Self-Billed Invoice</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
                        <input type="text" wire:model="invoiceNumber" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Invoice Date</label>
                        <input type="date" wire:model="invoiceDate" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" wire:model="dueDate" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>

                <!-- Customer Search -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Search Customer</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Search by name, TIN, or phone..."
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <div wire:loading.delay class="absolute right-3 top-3">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-500"></div>
                        </div>
                    </div>

                    <!-- Search Results -->
                    @if($showDropdown && !empty($searchResults))
                    <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none">
                        @foreach($searchResults as $customer)
                        <div wire:click="selectCustomer({{ $customer['id'] }})"
                             class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">
                            <div class="flex items-center">
                                <span class="ml-3 block font-normal truncate">
                                    {{ $customer['name'] }} - {{ $customer['phone'] }}
                                    @if($customer['tin']) ({{ $customer['tin'] }}) @endif
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Customer Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name *</label>
                        <input type="text" wire:model="customerName" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('customerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone *</label>
                        <input type="text" wire:model="customerPhone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('customerPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="customerEmail" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">TIN</label>
                        <input type="text" wire:model="customerTin" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Street Address *</label>
                        <input type="text" wire:model="customerStreetAddress" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('customerStreetAddress') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">City *</label>
                        <input type="text" wire:model="customerCity" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('customerCity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">State *</label>
                        <select wire:model="customerStateId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select State</option>
                            @foreach($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('customerStateId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Postal Code *</label>
                        <input type="text" wire:model="customerPostalCode" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('customerPostalCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>

                <div class="space-y-4">
                    @foreach($items as $index => $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Description *</label>
                                <input type="text" wire:model="items.{{ $index }}.description" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error("items.{$index}.description") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error("items.{$index}.quantity") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error("items.{$index}.unit_price") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax Rate *</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.tax_rate" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error("items.{$index}.tax_rate") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Classification *</label>
                                <select wire:model="items.{{ $index }}.item_classification_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    @foreach($itemClassifications as $classification)
                                    <option value="{{ $classification->id }}">{{ $classification->code }} - {{ Str::limit($classification->description, 30) }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.item_classification_id") <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($loop->iteration > 1)
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-800 text-sm">Remove Item</button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button type="button" wire:click="addItem" class="bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600">Add Item</button>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount Amount</label>
                        <input type="number" step="0.01" wire:model="discountAmount" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select wire:model="paymentMethod" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Online Payment">Online Payment</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea wire:model="notes" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="px-6 py-6 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-lg font-medium text-gray-900">Total: RM {{ number_format($this->calculateTotal(), 2) }}</p>
                        <p class="text-sm text-gray-600">Subtotal: RM {{ number_format(collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']), 2) }}</p>
                        <p class="text-sm text-gray-600">Tax: RM {{ number_format(collect($items)->sum(fn($item) => ($item['quantity'] * $item['unit_price']) * ($item['tax_rate'] / 100)), 2) }}</p>
                        @if($discountAmount > 0)
                        <p class="text-sm text-gray-600">Discount: RM {{ number_format($discountAmount, 2) }}</p>
                        @endif
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" wire:loading.attr="disabled" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 disabled:opacity-50">
                            <span wire:loading.remove>Save as Draft</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button type="button" wire:click="save('pending')" wire:loading.attr="disabled" class="bg-primary-500 text-white px-6 py-2 rounded-lg hover:bg-primary-600 disabled:opacity-50">
                            <span wire:loading.remove>Save & Continue</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if (session()->has('message'))
    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('message') }}
    </div>
    @endif
</div>