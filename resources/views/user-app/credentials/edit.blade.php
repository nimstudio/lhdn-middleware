@extends('layouts.user-app', ['title' => 'Edit LHDN Credentials'])

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-primary-50 to-brand-50 border-b border-primary-200">
                <h1 class="text-lg font-semibold text-gray-900">Edit LHDN API Credentials</h1>
                <p class="text-sm text-gray-600 mt-1">Update your credentials to submit invoices to LHDN MyInvois.</p>
            </div>

            <form method="POST" action="{{ route('user.credentials.update', $credential) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700">Client ID</label>
                        <input type="text" name="client_id" id="client_id"
                               value="{{ old('client_id', $credential->client_id) }}"
                               class="mt-2 block w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('client_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               required>
                        @error('client_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="client_secret" class="block text-sm font-medium text-gray-700">Client Secret</label>
                        <input type="text" name="client_secret" id="client_secret"
                               value="{{ old('client_secret', $credential->client_secret) }}"
                               class="mt-2 block w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('client_secret') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               required>
                        @error('client_secret')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="mode" class="block text-sm font-medium text-gray-700">Mode</label>
                        <select name="mode" id="mode"
                                class="mt-2 block w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('mode') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                required>
                            <option value="sandbox" {{ old('mode', $credential->mode) === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="production" {{ old('mode', $credential->mode) === 'production' ? 'selected' : '' }}>Production</option>
                        </select>
                        @error('mode')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('user.credentials.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">Cancel</a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
