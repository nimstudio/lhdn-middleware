@extends('layouts.user-app', ['title' => 'Edit Company'])

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900">Edit Company Information</h1>
        <p class="text-gray-500 mt-2">Update your registered details and keep your business profile accurate</p>
        <a href="{{ route('user.company.show') }}"
           class="inline-flex items-center mt-5 px-5 py-2.5 border border-gray-200 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Company
        </a>
    </div>

     <!-- Card -->
     <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
         <div class="px-8 py-6 bg-gradient-to-r from-primary-600 to-brand-500 text-white">
             <h2 class="text-xl font-semibold">Company Profile</h2>
             <p class="text-sm opacity-90">Provide accurate details for compliance and business correspondence</p>
         </div>

        <form method="POST" action="{{ route('user.company.update') }}" class="p-8 space-y-12">
            @csrf
            @method('PUT')

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Success</h3>
                            <div class="mt-2 text-sm text-green-700">
                                {{ session('success') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Company Information -->
            <div class="space-y-6">
                 <h3 class="flex items-center text-lg font-semibold text-gray-900">
                     <span class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-primary-100 text-primary-600">🏢</span>
                     Company Information
                 </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-input name="name" label="Company Name *" placeholder="Acme Sdn Bhd" value="{{ old('name', $company->name ?? '') }}" required />
                    <x-input name="registration_number" label="Registration Number (SSM) *" placeholder="202301234567" value="{{ old('registration_number', $company->registration_number ?? '') }}" required />
                    <div>
                        <x-input name="tin_number" label="Tax Identification Number (TIN) *" placeholder="C1234567890" value="{{ old('tin_number', $company->tin_number ?? '') }}" required />
                        @if($company && $company->lhdnCredential && $company->lhdnCredential->status === 'active')

                        @endif
                        @if($company && $company->tin_status)
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $company->tin_status === 'valid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $company->tin_status === 'invalid' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $company->tin_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    TIN Status: {{ ucfirst($company->tin_status) }}
                                    @if($company->tin_source === 'sdk')
                                        (from LHDN)
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="space-y-6">
                 <h3 class="flex items-center text-lg font-semibold text-gray-900">
                     <span class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-brand-100 text-brand-600">📍</span>
                     Address Information
                 </h3>
                <x-textarea name="address_line_1" label="Address Line 1 *" placeholder="123 Business Street, Suite 100" value="{{ old('address_line_1', $company->address_line_1 ?? '') }}" required />
                <x-input name="address_line_2" label="Address Line 2" placeholder="Unit 5-2, Level 5" value="{{ old('address_line_2', $company->address_line_2 ?? '') }}" />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-input name="city" label="City *" placeholder="Kuala Lumpur" value="{{ old('city', $company->city ?? '') }}" required />
                    <x-select name="state_id" label="State *" :options="$states" :selected="old('state_id', $company->state_id ?? '')" required />
                    <x-input name="postcode" label="Postcode *" placeholder="50000" value="{{ old('postcode', $company->postcode ?? '') }}" required />
                </div>
                <x-searchable-select
                    name="business_type_id"
                    label="Business Type (MSIC) *"
                    :options="$msics->map(fn($m) => ['id' => $m->id, 'label' => $m->code . ' - ' . $m->description, 'code' => $m->code, 'description' => $m->description])->toArray()"
                    :selected="old('business_type_id', $company->business_type_id ?? '')"
                    value-key="id"
                    label-key="label"
                    :search-keys="['code', 'description']"
                    required
                />
            </div>

            <!-- Invoice Settings -->
            <div class="space-y-6">


                <x-searchable-select
                    name="default_item_classification_id"
                    label="Default Item Classification *"
                    :options="$itemClassifications->map(fn($c) => ['id' => $c->id, 'label' => $c->code . ' - ' . $c->description, 'code' => $c->code, 'description' => $c->description])->toArray()"
                    :selected="old('default_item_classification_id', $company->default_item_classification_id ?? '')"
                    value-key="id"
                    label-key="label"
                    :search-keys="['code', 'description']"
                    required
                />
            </div>

            <!-- Contact Information -->
            <div class="space-y-6">
                 <h3 class="flex items-center text-lg font-semibold text-gray-900">
                     <span class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-accent-100 text-accent-600">📞</span>
                     Contact Information
                 </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-input name="phone" label="Phone *" placeholder="+60 3-1234 5678" value="{{ old('phone', $company->phone ?? '') }}" required />
                    <x-input type="email" name="email" label="Email *" placeholder="info@company.com" value="{{ old('email', $company->email ?? '') }}" required />
                </div>
            </div>


            <!-- Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                <p class="text-sm text-gray-500">* Required fields</p>
                <div class="flex gap-4">
                    <a href="{{ route('user.company.show') }}"
                       class="px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancel
                    </a>
                     <button type="submit"
                             class="px-8 py-2.5 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-md">
                         Save Company Information
                     </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
