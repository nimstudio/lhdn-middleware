@extends('layouts.user-app', ['title' => 'Add LHDN Credentials'])

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">LHDN MyInvois API Credentials</h3>
                <p class="text-sm text-gray-600 mb-6">Enter your LHDN MyInvois API credentials to start submitting invoices to LHDN.</p>

                <form method="POST" action="{{ route('user.credentials.store') }}" class="space-y-6">
                    @csrf

                    @if($errors->any())
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Mode -->
                    <div>
                        <label for="mode" class="block text-sm font-medium text-gray-700">Mode</label>
                        <select name="mode" id="mode"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('mode') border-red-300 @enderror" required>
                            <option value="">Select Mode</option>
                            <option value="sandbox" {{ old('mode') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                            <option value="production" {{ old('mode') == 'production' ? 'selected' : '' }}>Production</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">Choose Sandbox for testing or Production for live submissions.</p>
                        @error('mode')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Client ID -->
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700">Client ID</label>
                        <input type="text" name="client_id" id="client_id" value="{{ old('client_id') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('client_id') border-red-300 @enderror"
                               placeholder="Enter your Client ID" required>
                        @error('client_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Client Secret -->
                    <div>
                        <label for="client_secret" class="block text-sm font-medium text-gray-700">Client Secret</label>
                        <input type="password" name="client_secret" id="client_secret" value="{{ old('client_secret') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm @error('client_secret') border-red-300 @enderror"
                               placeholder="Enter your Client Secret" required>
                        @error('client_secret')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('user.credentials.index') }}"
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            Save Credentials
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
