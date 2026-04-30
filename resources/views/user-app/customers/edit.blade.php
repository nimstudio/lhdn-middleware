@extends('layouts.user-app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('user.customers.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-block">
            ← Back to Customers
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Customer</h1>
        <p class="mt-1 text-sm text-gray-600">Update customer information</p>
    </div>

    <!-- Form -->
    <form action="{{ route('user.customers.update', $customer) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Customer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $customer->name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                           placeholder="John Doe / ABC Sdn Bhd">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $customer->email) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                           placeholder="customer@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phone"
                           value="{{ old('phone', $customer->phone) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('phone') border-red-500 @enderror"
                           placeholder="+60123456789">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Active Customer</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">Inactive customers won't appear in invoice customer dropdowns</p>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Address</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Street Address -->
                <div class="md:col-span-2">
                    <label for="street_address" class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input type="text" name="street_address" id="street_address"
                           value="{{ old('street_address', $customer->street_address) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('street_address') border-red-500 @enderror"
                           placeholder="123 Jalan Example">
                    @error('street_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" name="city" id="city"
                           value="{{ old('city', $customer->city) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('city') border-red-500 @enderror"
                           placeholder="Kuala Lumpur">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label for="state_id" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <select name="state_id" id="state_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('state_id') border-red-500 @enderror">
                        <option value="">Select State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ old('state_id', $customer->state_id) == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code"
                           value="{{ old('postal_code', $customer->postal_code) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('postal_code') border-red-500 @enderror"
                           placeholder="50000">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select name="country" id="country"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('country') border-red-500 @enderror">
                        <option value="MYS" {{ old('country', $customer->country) == 'MYS' ? 'selected' : '' }}>Malaysia</option>
                        <option value="SG" {{ old('country', $customer->country) == 'SG' ? 'selected' : '' }}>Singapore</option>
                        <option value="ID" {{ old('country', $customer->country) == 'ID' ? 'selected' : '' }}>Indonesia</option>
                        <option value="TH" {{ old('country', $customer->country) == 'TH' ? 'selected' : '' }}>Thailand</option>
                        <option value="VN" {{ old('country', $customer->country) == 'VN' ? 'selected' : '' }}>Vietnam</option>
                        <option value="PH" {{ old('country', $customer->country) == 'PH' ? 'selected' : '' }}>Philippines</option>
                    </select>
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- eInvoice Information (Optional) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">eInvoice Information <span class="text-sm font-normal text-gray-500">(Optional)</span></h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- TIN -->
                <div>
                    <label for="tin" class="block text-sm font-medium text-gray-700 mb-2">
                        Tax Identification Number (TIN)
                    </label>
                    <input type="text" name="tin" id="tin"
                           value="{{ old('tin', $customer->tin) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('tin') border-red-500 @enderror"
                           placeholder="C12345678901234">
                    @error('tin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Type -->
                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select name="document_type" id="document_type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('document_type') border-red-500 @enderror">
                        <option value="">Select Type</option>
                        <option value="BRN" {{ old('document_type', $customer->document_type) == 'BRN' ? 'selected' : '' }}>BRN (Business Registration)</option>
                        <option value="NRIC" {{ old('document_type', $customer->document_type) == 'NRIC' ? 'selected' : '' }}>NRIC (IC Number)</option>
                        <option value="PASSPORT" {{ old('document_type', $customer->document_type) == 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                        <option value="ARMY" {{ old('document_type', $customer->document_type) == 'ARMY' ? 'selected' : '' }}>Army ID</option>
                    </select>
                    @error('document_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Number -->
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700 mb-2">Document Number</label>
                    <input type="text" name="document_number" id="document_number"
                           value="{{ old('document_number', $customer->document_number) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('document_number') border-red-500 @enderror"
                           placeholder="201234567890">
                    @error('document_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h2>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('notes') border-red-500 @enderror"
                          placeholder="Any additional information about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('user.customers.show', $customer) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Update Customer
            </button>
        </div>
    </form>
</div>
@endsection
