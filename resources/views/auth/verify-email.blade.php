<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - {{ \App\Config\Branding::APP_NAME }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Verification Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-md w-full space-y-8">
                <!-- Logo and Header -->
                <div>
                    <a href="{{ url('/') }}" class="flex items-center justify-center">
                        <svg class="h-10 w-10 text-{{ \App\Config\Branding::PRIMARY_COLOR }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="ml-2 text-2xl font-bold text-gray-900">{{ \App\Config\Branding::APP_NAME }}</span>
                    </a>
                    <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Verify your email</h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        We've sent you a verification link
                    </p>
                </div>

                <!-- Email Icon -->
                <div class="flex justify-center">
                    <div class="w-20 h-20 bg-{{ \App\Config\Branding::PRIMARY_LIGHT_COLOR }} rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-{{ \App\Config\Branding::PRIMARY_COLOR }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Information Box -->
                <div class="bg-{{ \App\Config\Branding::PRIMARY_LIGHT_COLOR }} border border-{{ \App\Config\Branding::PRIMARY_BORDER_COLOR }} rounded-lg p-6">
                    <p class="text-sm text-gray-700 text-center">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to:
                    </p>
                    <p class="text-base font-semibold text-gray-900 text-center mt-3">
                        {{ auth()->user()->email }}
                    </p>
                    <p class="text-sm text-gray-600 text-center mt-3">
                        If you didn't receive the email, we will gladly send you another.
                    </p>
                </div>

                <!-- Success Message -->
                @if (session('status') == 'verification-link-sent')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    A new verification link has been sent to the email address you provided during registration.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-{{ \App\Config\Branding::PRIMARY_COLOR }} hover:bg-{{ \App\Config\Branding::PRIMARY_HOVER_COLOR }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }} transition"
                        >
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }} transition"
                        >
                            Log Out
                        </button>
                    </form>
                </div>

                <!-- Help Text -->
                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        Having trouble? Check your spam folder or
                        <a href="#" class="text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }} hover:text-{{ \App\Config\Branding::PRIMARY_TEXT_HOVER_COLOR }} font-medium">contact support</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Feature Highlight -->
        <div class="hidden lg:block relative w-0 flex-1 bg-gradient-to-br from-{{ \App\Config\Branding::PRIMARY_COLOR }} to-{{ \App\Config\Branding::SECONDARY_COLOR }}">
            <div class="absolute inset-0 flex items-center justify-center p-12">
                <div class="max-w-md text-white">
                    <h2 class="text-3xl font-bold mb-6">You're almost there!</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Secure Account</h3>
                                <p class="text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} text-sm mt-1">Email verification helps protect your account and data</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Quick Setup</h3>
                                <p class="text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} text-sm mt-1">Just one click and you'll be ready to start submitting invoices</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Full Access</h3>
                                <p class="text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} text-sm mt-1">Get instant access to all features once verified</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 p-6 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold mb-1">Didn't receive the email?</p>
                                <p class="text-sm text-{{ \App\Config\Branding::SECONDARY_LIGHT_COLOR }}">Check your spam folder or click the "Resend" button to get a new verification link.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
