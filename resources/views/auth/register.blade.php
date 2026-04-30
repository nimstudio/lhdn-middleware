<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - LHDN Middleware</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Registration Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-md w-full space-y-8">
                <!-- Logo and Header -->
                <div>
                    <a href="{{ url('/') }}" class="flex items-center justify-center">
                        <svg class="h-10 w-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="ml-2 text-2xl font-bold text-gray-900">LHDN Middleware</span>
                    </a>
                    <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Create your account</h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">Sign in</a>
                    </p>
                </div>

                @php
                    $selectedPlan = null;
                    if (request('plan')) {
                        $selectedPlan = \App\Models\SubscriptionPlan::find(request('plan'));
                    }
                @endphp

                <!-- Selected Plan Display -->
                @if($selectedPlan)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Selected Plan: {{ $selectedPlan->name }}</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                <p class="font-semibold">RM{{ number_format($selectedPlan->price_annually, 2) }}/year</p>
                                <p class="text-xs">
                                    @if($selectedPlan->invoice_limit_monthly === 999999)
                                        Unlimited invoices per month
                                    @else
                                        Up to {{ $selectedPlan->invoice_limit_monthly }} invoices/month
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ url('/#pricing') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Change</a>
                    </div>
                </div>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">No plan selected</h3>
                            <div class="mt-1 text-sm text-yellow-700">
                                <a href="{{ url('/#pricing') }}" class="font-medium underline hover:text-yellow-600">Choose a subscription plan</a> before registering
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Registration Form -->
                <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf

                    <input type="hidden" name="subscription_plan_id" value="{{ $selectedPlan?->id }}">

                    <div class="space-y-4">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                                placeholder="John Doe"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-300 @enderror"
                                placeholder="john@company.com"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="mt-1 flex rounded-lg shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    +60
                                </span>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') ? (str_starts_with(old('phone'), '+60') ? substr(old('phone'), 3) : old('phone')) : '' }}"
                                    required
                                    maxlength="10"
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-r-lg placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('phone') border-red-300 @enderror"
                                    placeholder="123456789"
                                >
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter 9-10 digits without the leading 0 (e.g., 123456789)</p>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-300 @enderror"
                                placeholder="••••••••"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            @if(!$selectedPlan) disabled title="Please select a plan first" @endif
                        >
                            Create Account
                        </button>
                    </div>

                    <div class="text-xs text-center text-gray-500">
                        By creating an account, you agree to our
                        <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and
                        <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Feature Highlight -->
        <div class="hidden lg:block relative w-0 flex-1 bg-gradient-to-br from-blue-600 to-blue-800">
            <div class="absolute inset-0 flex items-center justify-center p-12">
                <div class="max-w-md text-white">
                    <h2 class="text-3xl font-bold mb-6">Start your LHDN MyInvois journey today</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-200 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Automated Invoice Submission</h3>
                                <p class="text-blue-100 text-sm mt-1">Submit invoices to LHDN with a single click</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-200 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Secure & Compliant</h3>
                                <p class="text-blue-100 text-sm mt-1">Encrypted credentials and full LHDN compliance</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-200 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Real-time Tracking</h3>
                                <p class="text-blue-100 text-sm mt-1">Monitor all submissions with detailed logs</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 p-6 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20">
                        <p class="text-sm italic">"LHDN Middleware has simplified our invoice submission process tremendously. Highly recommended!"</p>
                        <p class="mt-2 text-sm font-semibold">- Malaysian Business Owner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');

            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;

                // Remove all non-digit characters
                value = value.replace(/\D/g, '');

                // Remove leading zeros
                value = value.replace(/^0+/, '');

                // Limit to 10 digits
                value = value.substring(0, 10);

                e.target.value = value;
            });
        });
    </script>
</body>
</html>
