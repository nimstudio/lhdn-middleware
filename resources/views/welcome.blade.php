<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LHDN Middleware - MyInvois Invoice Submission Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                    </svg>
                    <span class="ml-2 text-xl font-bold text-gray-900">LHDN Middleware</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#pricing" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium">Pricing</a>
                    <a href="#features" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium">Features</a>
                    @auth
                        <a href="{{ url('/app') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">Get Started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-white py-20 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Streamline Your <span class="text-blue-600">MyInvois</span> Submissions
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
                    Effortlessly submit invoices to LHDN's MyInvois platform. Automated, secure, and compliant invoice management for Malaysian businesses.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-base font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-600/50">
                        Start Free Trial
                    </a>
                    <a href="#features" class="text-gray-900 px-8 py-3 rounded-lg text-base font-semibold hover:text-blue-600 transition">
                        Learn more →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Everything you need for LHDN compliance</h2>
                <p class="mt-4 text-lg text-gray-600">Powerful features to simplify your invoice submissions</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Automated Submission</h3>
                    <p class="text-gray-600">Submit invoices to LHDN MyInvois automatically with a single click. No manual data entry required.</p>
                </div>

                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure Credentials</h3>
                    <p class="text-gray-600">Your LHDN API credentials are encrypted and stored securely with industry-standard protection.</p>
                </div>

                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Real-time Tracking</h3>
                    <p class="text-gray-600">Monitor submission status in real-time with detailed logs and LHDN response tracking.</p>
                </div>

                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Activity Logs</h3>
                    <p class="text-gray-600">Complete audit trail of all actions with detailed activity logs for compliance.</p>
                </div>

                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Multi-User Support</h3>
                    <p class="text-gray-600">Manage your team with role-based access control and user management.</p>
                </div>

                <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">UAT & Production</h3>
                    <p class="text-gray-600">Test in UAT environment before going live with production submissions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Simple, transparent pricing</h2>
                <p class="mt-4 text-lg text-gray-600">Choose the plan that fits your business needs</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php
                    $plans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('price_annually')->get();
                @endphp

                @foreach($plans as $plan)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden {{ $plan->slug === 'business' ? 'ring-2 ring-blue-600 transform scale-105' : '' }}">
                    @if($plan->slug === 'business')
                    <div class="bg-blue-600 text-white text-center py-2 text-sm font-semibold">
                        MOST POPULAR
                    </div>
                    @endif

                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-4 flex items-baseline">
                            <span class="text-5xl font-bold text-gray-900">RM{{ number_format($plan->price_annually, 0) }}</span>
                            <span class="ml-2 text-gray-500">/year</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            @if($plan->invoice_limit_monthly === 999999)
                                Unlimited invoices per month
                            @else
                                Up to {{ $plan->invoice_limit_monthly }} invoices/month
                            @endif
                        </p>

                        <ul class="mt-8 space-y-4">
                            @foreach($plan->features as $feature)
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="ml-3 text-gray-600">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->id]) }}" class="mt-8 block w-full text-center {{ $plan->slug === 'business' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }} px-6 py-3 rounded-lg font-semibold transition">
                            Get Started
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <p class="mt-12 text-center text-sm text-gray-500">
                All plans include email support and access to UAT environment for testing
            </p>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white">Ready to streamline your LHDN submissions?</h2>
            <p class="mt-4 text-xl text-blue-100">Join Malaysian businesses using our platform for MyInvois compliance</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-base font-semibold hover:bg-gray-100 transition inline-block shadow-xl">
                    Get Started Now
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="ml-2 text-lg font-bold text-gray-900">LHDN Middleware</span>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">
                        Simplifying MyInvois invoice submissions for Malaysian businesses.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Product</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#features" class="hover:text-gray-900">Features</a></li>
                        <li><a href="#pricing" class="hover:text-gray-900">Pricing</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-gray-900">Login</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-gray-900">Documentation</a></li>
                        <li><a href="#" class="hover:text-gray-900">API Reference</a></li>
                        <li><a href="#" class="hover:text-gray-900">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Legal</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-gray-900">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-gray-900">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} LHDN Middleware. All rights reserved. Laravel v{{ Illuminate\Foundation\Application::VERSION }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
