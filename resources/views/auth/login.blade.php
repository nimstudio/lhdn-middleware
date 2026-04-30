<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-md w-full space-y-8">
                <!-- Logo and Header -->
                <div>
                    <a href="{{ url('/') }}" class="flex items-center justify-center">
                        <svg class="h-10 w-10 text-{!! \App\Config\Branding::PRIMARY_COLOR !!}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="ml-2 text-2xl font-bold text-gray-900">{{ \App\Config\Branding::APP_NAME }}</span>
                    </a>
                    <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Welcome back</h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-{!! \App\Config\Branding::PRIMARY_COLOR !!} hover:text-{!! \App\Config\Branding::PRIMARY_HOVER_COLOR !!}">Register now</a>
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Login Form -->
                <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="space-y-4">
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" autocomplete="email" required autofocus value="{{ old('email') }}"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-{!! \App\Config\Branding::PRIMARY_COLOR !!} focus:border-{!! \App\Config\Branding::PRIMARY_COLOR !!} sm:text-sm @error('email') border-red-300 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-{!! \App\Config\Branding::PRIMARY_COLOR !!} focus:border-{!! \App\Config\Branding::PRIMARY_COLOR !!} sm:text-sm @error('password') border-red-300 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me and Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox"
                                   class="h-4 w-4 text-{!! \App\Config\Branding::PRIMARY_COLOR !!} focus:ring-{!! \App\Config\Branding::PRIMARY_COLOR !!} border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-sm">
                                <a href="{{ route('password.request') }}" class="font-medium text-{!! \App\Config\Branding::PRIMARY_COLOR !!} hover:text-{!! \App\Config\Branding::PRIMARY_HOVER_COLOR !!}">
                                    Forgot your password?
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white {!! \App\Config\Branding::primaryButton() !!} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{!! \App\Config\Branding::PRIMARY_COLOR !!}">
                            Sign in
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-50 text-gray-500">Test Credentials</span>
                        </div>
                    </div>
                </div>

                <!-- Test Credentials -->
                <div class="mt-4 space-y-2 text-xs text-gray-600 bg-gray-100 p-4 rounded-lg">
                    <p class="font-semibold text-gray-700">Super Admin:</p>
                    <p>Email: admin@lhdn-middleware.test</p>
                    <p>Password: password</p>
                    <p class="font-semibold text-gray-700 mt-3">Test User:</p>
                    <p>Email: user@test.com</p>
                    <p>Password: password</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Feature Highlights -->
        <div class="hidden lg:flex lg:flex-1 bg-{!! \App\Config\Branding::PRIMARY_COLOR !!} items-center justify-center p-12">
            <div class="max-w-md text-white">
                <h2 class="text-3xl font-bold mb-6">MyInvois Integration Made Easy</h2>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="ml-3">Seamless LHDN MyInvois submission</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="ml-3">Real-time invoice validation</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="ml-3">Automated compliance tracking</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="ml-3">Comprehensive reporting dashboard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
