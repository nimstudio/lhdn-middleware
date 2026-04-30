@extends('layouts.user-app', ['title' => 'Settings'])

@section('content')
    <div class="max-w-7xl" x-data="{ activeTab: '{{ old('_tab', 'invoice') }}' }">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-600 mt-1">Manage your account preferences and invoice settings</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                    <button @click="activeTab = 'invoice'"
                            :class="activeTab === 'invoice' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Invoice Settings</span>
                    </button>
                    <a href="{{ route('user.settings.pdf') }}"
                       class="whitespace-nowrap py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v4a1 1 0 001 1h4"/>
                        </svg>
                        <span>PDF Settings</span>
                    </a>
                    <button @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profile</span>
                    </button>
                    <button @click="activeTab = 'notifications'"
                            :class="activeTab === 'notifications' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center space-x-2 opacity-50 cursor-not-allowed"
                            disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span>Notifications</span>
                        <span class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded">Soon</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Invoice Settings Tab -->
        <div x-show="activeTab === 'invoice'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 bg-gradient-to-r from-primary-50 to-brand-50 border-b border-primary-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Invoice Settings</h2>
                            <p class="text-sm text-gray-600">Configure invoice defaults and preferences</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.settings.update-invoice') }}" class="p-6 space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="invoice">

                    <!-- Invoice Prefix -->
                    <div x-data="invoicePrefixManager()">
                        <label for="invoice_prefix" class="block text-sm font-semibold text-gray-900 mb-2">
                            Invoice Number Format <span class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-gray-500 mb-3">Customize how invoice numbers are generated using placeholders</p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Format Input -->
                            <div>
                                <input type="text" name="invoice_prefix" id="invoice_prefix" required x-model="format" @input="updatePreview()"
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono @error('invoice_prefix') border-red-500 @enderror"
                                       placeholder="INV-{####}" value="{{ old('invoice_prefix', $company->invoice_prefix ?? 'INV-{####}') }}">
                                @error('invoice_prefix')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Preview -->
                                <div class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <p class="text-xs font-medium text-gray-600 mb-1">Preview:</p>
                                    <p class="font-mono text-sm text-gray-900" x-text="preview"></p>
                                </div>
                            </div>

                            <!-- Available Placeholders -->
                            <div>
                                <p class="text-xs font-semibold text-gray-700 mb-2">Available Placeholders:</p>
                                <div class="space-y-2">
                                    <button type="button" @click="insertPlaceholder('{YYYY}')" class="w-full flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                        <span class="font-mono text-sm text-gray-900">{YYYY}</span>
                                        <span class="text-xs text-gray-500">Year (2025)</span>
                                    </button>
                                    <button type="button" @click="insertPlaceholder('{YY}')" class="w-full flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                        <span class="font-mono text-sm text-gray-900">{YY}</span>
                                        <span class="text-xs text-gray-500">Year (25)</span>
                                    </button>
                                    <button type="button" @click="insertPlaceholder('{MM}')" class="w-full flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                        <span class="font-mono text-sm text-gray-900">{MM}</span>
                                        <span class="text-xs text-gray-500">Month (10)</span>
                                    </button>
                                    <button type="button" @click="insertPlaceholder('{DD}')" class="w-full flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                        <span class="font-mono text-sm text-gray-900">{DD}</span>
                                        <span class="text-xs text-gray-500">Day (03)</span>
                                    </button>
                                    <button type="button" @click="insertPlaceholder('{####}')" class="w-full flex items-center justify-between p-2 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                        <span class="font-mono text-sm text-gray-900">{####}</span>
                                        <span class="text-xs text-gray-500">Sequence (0001)</span>
                                    </button>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">
                                    <strong>Tip:</strong> Click a placeholder to insert it, or type directly.
                                </p>
                            </div>
                        </div>

                        <!-- Common Examples -->
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs font-semibold text-blue-900 mb-2">Common Formats:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <button type="button" @click="format = 'INV-{YYYY}-{####}'; updatePreview()" class="text-left p-2 bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                                    <span class="font-mono text-xs text-gray-900">INV-{YYYY}-{####}</span>
                                    <span class="text-xs text-gray-500 block">→ INV-2025-0001</span>
                                </button>
                                <button type="button" @click="format = 'INV{YYYY}{MM}{####}'; updatePreview()" class="text-left p-2 bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                                    <span class="font-mono text-xs text-gray-900">INV{YYYY}{MM}{####}</span>
                                    <span class="text-xs text-gray-500 block">→ INV2025100001</span>
                                </button>
                                <button type="button" @click="format = '{YY}{MM}-{####}'; updatePreview()" class="text-left p-2 bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                                    <span class="font-mono text-xs text-gray-900">{YY}{MM}-{####}</span>
                                    <span class="text-xs text-gray-500 block">→ 2510-0001</span>
                                </button>
                                <button type="button" @click="format = 'SI-{YYYY}/{MM}/{####}'; updatePreview()" class="text-left p-2 bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                                    <span class="font-mono text-xs text-gray-900">SI-{YYYY}/{MM}/{####}</span>
                                    <span class="text-xs text-gray-500 block">→ SI-2025/10/0001</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Default Tax Rates -->
                    <div x-data="taxRatesManager()" class="border-t border-gray-200 pt-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Default Tax Rates
                        </label>
                        <p class="text-sm text-gray-500 mb-4">Configure the tax rates that appear in the dropdown when creating invoices</p>

                        <div class="space-y-3 mb-4">
                            <template x-for="(rate, index) in rates" :key="index">
                                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                                    <div class="flex-1 max-w-[200px]">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Percentage</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" step="0.01" min="0" max="100" x-model.number="rate.value"
                                                   class="flex-1 !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                   placeholder="0.00">
                                            <span class="text-sm font-medium text-gray-600">%</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                                        <input type="text" x-model="rate.label"
                                               class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                               placeholder="e.g., SST, Service Tax">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">LHDN Tax Type <span class="text-red-500">*</span></label>
                                        <select x-model="rate.tax_type_id" required
                                                class="w-full !px-3 !py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                            @foreach($taxTypes as $taxType)
                                                <option value="{{ $taxType->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $taxType->code }} - {{ $taxType->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="pt-5">
                                        <button type="button" @click="removeRate(index)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                :disabled="rates.length === 1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addRate()" class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-primary-700 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors border border-primary-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                            </svg>
                            Add Tax Rate
                        </button>

                        <!-- Hidden inputs for submission -->
                        <input type="hidden" name="default_tax_rates" :value="JSON.stringify(rates)">
                    </div>


                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Changes will apply to new invoices only</p>
                        <button type="submit" class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Invoice Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Settings Tab -->
        <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 bg-gradient-to-r from-primary-50 to-brand-50 border-b border-primary-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Profile Settings</h2>
                            <p class="text-sm text-gray-600">Update your personal information and password</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.settings.update-profile') }}" class="p-6 space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="profile">

                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                                       value="{{ old('name', $user->name) }}">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" required
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                                       value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="text" name="phone" id="phone"
                                   class="block w-full md:w-1/2 !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('phone') border-red-500 @enderror"
                                   placeholder="+60 12-345 6789" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="border-t border-gray-200 pt-8">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Change Password</h3>
                        <p class="text-sm text-gray-500 mb-4">Leave blank if you don't want to change your password</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Password
                                </label>
                                <input type="password" name="current_password" id="current_password"
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('current_password') border-red-500 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password
                                </label>
                                <input type="password" name="password" id="password"
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm New Password
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="block w-full !px-4 !py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-gray-200 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifications Tab (Placeholder) -->
        <div x-show="activeTab === 'notifications'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Notification Settings Coming Soon</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Configure email notifications, invoice alerts, and system updates in a future release.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoicePrefixManager() {
            return {
                format: '{{ old('invoice_prefix', $company->invoice_prefix ?? 'INV-{####}') }}',
                preview: '',
                init() {
                    this.updatePreview();
                },
                insertPlaceholder(placeholder) {
                    const input = document.getElementById('invoice_prefix');
                    const start = input.selectionStart;
                    const end = input.selectionEnd;
                    const text = this.format;
                    this.format = text.substring(0, start) + placeholder + text.substring(end);
                    this.updatePreview();

                    // Set cursor position after the inserted placeholder
                    this.$nextTick(() => {
                        input.focus();
                        input.setSelectionRange(start + placeholder.length, start + placeholder.length);
                    });
                },
                updatePreview() {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');

                    let preview = this.format
                        .replace(/{YYYY}/g, year)
                        .replace(/{YY}/g, String(year).slice(-2))
                        .replace(/{MM}/g, month)
                        .replace(/{DD}/g, day)
                        .replace(/{####}/g, '0001')
                        .replace(/{###}/g, '001')
                        .replace(/{##}/g, '01');

                    this.preview = preview || 'Enter a format...';
                }
            };
        }

        function taxRatesManager() {
            const defaultRates = [
                { value: 0, label: 'No Tax', tax_type_id: {{ \App\Models\TaxType::where('code', '06')->first()->id ?? 'null' }}, tax_type_code: '06' },
                { value: 6, label: 'Sales Tax (6%)', tax_type_id: {{ \App\Models\TaxType::where('code', '01')->first()->id ?? 'null' }}, tax_type_code: '01' },
                { value: 8, label: 'Service Tax (8%)', tax_type_id: {{ \App\Models\TaxType::where('code', '02')->first()->id ?? 'null' }}, tax_type_code: '02' }
            ];

            const savedRates = @json($company->default_tax_rates ?? null);

            return {
                rates: savedRates && savedRates.length > 0 ? savedRates : defaultRates,
                addRate() {
                    // Get the first tax type ID for default selection
                    const firstTaxTypeId = {{ $taxTypes->first()->id ?? 'null' }};
                    this.rates.push({ value: 0, label: '', tax_type_id: firstTaxTypeId, tax_type_code: '' });
                },
                removeRate(index) {
                    if (this.rates.length > 1) {
                        this.rates.splice(index, 1);
                    }
                }
            };
        }
    </script>
@endsection
