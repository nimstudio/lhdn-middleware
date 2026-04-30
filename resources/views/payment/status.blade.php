<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Status - {{ \App\Config\Branding::APP_NAME }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-{{ \App\Config\Branding::PRIMARY_COLOR }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zm0 2v16h6V4H9zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-900">{{ \App\Config\Branding::APP_NAME }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Log out</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-2xl w-full">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-lg p-8">
                    @if($payment)
                        @if($payment->status === 'pending')
                            <!-- Pending Status -->
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
                                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Under Review</h2>
                                <p class="mt-2 text-gray-600">Your payment is being verified by our team</p>

                                <div class="mt-8 bg-gray-50 rounded-lg p-6 text-left">
                                    <h3 class="font-semibold text-gray-900 mb-4">Payment Details</h3>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Plan:</dt>
                                            <dd class="font-medium text-gray-900">{{ $payment->subscriptionPlan->name }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Amount:</dt>
                                            <dd class="font-medium text-gray-900">RM{{ number_format($payment->amount, 2) }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Payment Method:</dt>
                                            <dd class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Submitted:</dt>
                                            <dd class="font-medium text-gray-900">{{ $payment->created_at->format('M d, Y g:i A') }}</dd>
                                        </div>
                                        @if($payment->payment_proof)
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Receipt:</dt>
                                            <dd>
                                                <a href="{{ Storage::url($payment->payment_proof) }}" target="_blank" class="font-medium text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }} hover:text-{{ \App\Config\Branding::PRIMARY_TEXT_HOVER_COLOR }}">
                                                    View Receipt
                                                </a>
                                            </dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>

                                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                Our admin team will review your payment within 24 hours. You'll receive an email notification once approved.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($payment->status === 'approved')
                            <!-- Approved Status -->
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Approved!</h2>
                                <p class="mt-2 text-gray-600">Your subscription is now active</p>

                                <div class="mt-8 bg-gray-50 rounded-lg p-6 text-left">
                                    <h3 class="font-semibold text-gray-900 mb-4">Subscription Details</h3>
                                    <dl class="space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Plan:</dt>
                                            <dd class="font-medium text-gray-900">{{ $payment->subscriptionPlan->name }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Approved By:</dt>
                                            <dd class="font-medium text-gray-900">{{ $payment->approver?->name ?? 'Admin' }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Approved On:</dt>
                                            <dd class="font-medium text-gray-900">{{ $payment->approved_at->format('M d, Y g:i A') }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <a href="{{ route('dashboard') }}" class="mt-6 inline-flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-{{ \App\Config\Branding::PRIMARY_COLOR }} hover:bg-{{ \App\Config\Branding::PRIMARY_HOVER_COLOR }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }}">
                                    Go to Dashboard
                                </a>
                            </div>
                        @else
                            <!-- Rejected Status -->
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Rejected</h2>
                                <p class="mt-2 text-gray-600">There was an issue with your payment</p>

                                @if($payment->admin_notes)
                                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-sm text-red-700"><strong>Admin Notes:</strong> {{ $payment->admin_notes }}</p>
                                </div>
                                @endif

                                <a href="{{ route('payment.index') }}" class="mt-6 inline-flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-{{ \App\Config\Branding::PRIMARY_COLOR }} hover:bg-{{ \App\Config\Branding::PRIMARY_HOVER_COLOR }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }}">
                                    Try Again
                                </a>
                            </div>
                        @endif
                    @else
                        <!-- No Payment Found -->
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100">
                                <svg class="h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h2 class="mt-4 text-2xl font-bold text-gray-900">No Payment Found</h2>
                            <p class="mt-2 text-gray-600">Please complete your payment to access the dashboard</p>

                            <a href="{{ route('payment.index') }}" class="mt-6 inline-flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-{{ \App\Config\Branding::PRIMARY_COLOR }} hover:bg-{{ \App\Config\Branding::PRIMARY_HOVER_COLOR }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }}">
                                Complete Payment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
