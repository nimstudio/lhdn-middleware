<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete Payment - {{ \App\Config\Branding::APP_NAME }}</title>
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
            <div class="max-w-4xl w-full">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-center">
                        <div class="flex items-center">
                            <div class="flex items-center text-{{ \App\Config\Branding::PRIMARY_COLOR }}">
                                <div class="flex items-center justify-center w-10 h-10 bg-{{ \App\Config\Branding::PRIMARY_COLOR }} rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium">Register</span>
                            </div>
                            <div class="w-16 h-1 bg-{{ \App\Config\Branding::PRIMARY_COLOR }} mx-2"></div>
                            <div class="flex items-center text-{{ \App\Config\Branding::PRIMARY_COLOR }}">
                                <div class="flex items-center justify-center w-10 h-10 bg-{{ \App\Config\Branding::PRIMARY_COLOR }} rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium">Verify Email</span>
                            </div>
                            <div class="w-16 h-1 bg-{{ \App\Config\Branding::PRIMARY_COLOR }} mx-2"></div>
                            <div class="flex items-center text-{{ \App\Config\Branding::PRIMARY_COLOR }}">
                                <div class="flex items-center justify-center w-10 h-10 bg-{{ \App\Config\Branding::PRIMARY_COLOR }} rounded-full text-white font-semibold">
                                    3
                                </div>
                                <span class="ml-2 text-sm font-medium">Payment</span>
                            </div>
                            <div class="w-16 h-1 bg-gray-300 mx-2"></div>
                            <div class="flex items-center text-gray-400">
                                <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full text-gray-600 font-semibold">
                                    4
                                </div>
                                <span class="ml-2 text-sm font-medium">Dashboard</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Complete Your Payment</h2>
                    <p class="text-gray-600 mb-6">Choose your preferred payment method to activate your subscription</p>

                    <!-- Selected Plan -->
                    <div class="bg-{{ \App\Config\Branding::PRIMARY_LIGHT_COLOR }} border border-{{ \App\Config\Branding::PRIMARY_BORDER_COLOR }} rounded-lg p-4 mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $plan->name }} Plan</h3>
                                <p class="text-sm text-gray-600">
                                    @if($plan->invoice_limit_monthly === 999999)
                                        Unlimited invoices per month
                                    @else
                                        Up to {{ $plan->invoice_limit_monthly }} invoices/month
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }}">RM{{ number_format($plan->price_annually, 2) }}</p>
                                <p class="text-sm text-gray-500">per year</p>
                                <a href="{{ url('/#pricing') }}" class="inline-block mt-2 text-sm font-medium text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }} hover:text-{{ \App\Config\Branding::PRIMARY_TEXT_HOVER_COLOR }}">
                                    Change Plan
                                </a>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('payment.store') }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                        @csrf
                        <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">

                        <!-- Payment Methods -->
                        <div class="space-y-4 mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Method</label>

                            <!-- Bank Transfer -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-{{ \App\Config\Branding::PRIMARY_COLOR }} transition payment-method-option">
                                <input type="radio" name="payment_method" value="bank_transfer" class="payment-method-radio" required>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-900">Bank Transfer</p>
                                            <p class="text-sm text-gray-500">Upload payment receipt for manual verification</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="checkmark hidden">
                                    <svg class="w-6 h-6 text-{{ \App\Config\Branding::PRIMARY_COLOR }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </label>

                            <!-- Card Payment (Coming Soon) -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-lg opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="card" disabled>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-900">Credit/Debit Card</p>
                                            <p class="text-sm text-gray-500">Coming Soon</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- FPX (Coming Soon) -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-lg opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="fpx" disabled>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-900">FPX Online Banking</p>
                                            <p class="text-sm text-gray-500">Coming Soon</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- E-Wallet (Coming Soon) -->
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-lg opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="ewallet" disabled>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-900">E-Wallet</p>
                                            <p class="text-sm text-gray-500">Coming Soon</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Transfer Details -->
                        <div id="bankTransferDetails" class="hidden mb-6">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-4">
                                <h3 class="font-semibold text-gray-900 mb-4">Bank Transfer Instructions</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Bank Name:</span>
                                        <span class="font-medium">Maybank</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Account Name:</span>
                                        <span class="font-medium">LHDN Middleware Sdn Bhd</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Account Number:</span>
                                        <span class="font-medium">5641 2345 6789</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Amount:</span>
                                        <span class="font-medium text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }}">RM{{ number_format($plan->price_annually, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">Upload Payment Receipt</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-{{ \App\Config\Branding::PRIMARY_COLOR }} transition">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-{{ \App\Config\Branding::PRIMARY_TEXT_COLOR }} hover:text-{{ \App\Config\Branding::PRIMARY_TEXT_HOVER_COLOR }}">
                                                <span>Upload a file</span>
                                                <input id="payment_proof" name="payment_proof" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                                    </div>
                                </div>
                                <p id="fileName" class="mt-2 text-sm text-gray-600"></p>
                                @error('payment_proof')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @error('payment_method')
                            <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            id="submitButton"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-{{ \App\Config\Branding::PRIMARY_COLOR }} hover:bg-{{ \App\Config\Branding::PRIMARY_HOVER_COLOR }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ \App\Config\Branding::FOCUS_RING_COLOR }} transition disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            Complete Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle payment method selection
        const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
        const bankTransferDetails = document.getElementById('bankTransferDetails');
        const submitButton = document.getElementById('submitButton');
        const paymentForm = document.getElementById('paymentForm');
        const fileInput = document.getElementById('payment_proof');
        const fileName = document.getElementById('fileName');

        paymentMethodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Update visual state
                document.querySelectorAll('.payment-method-option').forEach(option => {
                    option.classList.remove('border-{{ \App\Config\Branding::PRIMARY_COLOR }}', 'bg-{{ \App\Config\Branding::PRIMARY_LIGHT_COLOR }}');
                    option.querySelector('.checkmark').classList.add('hidden');
                });

                if (this.checked) {
                    this.closest('.payment-method-option').classList.add('border-{{ \App\Config\Branding::PRIMARY_COLOR }}', 'bg-{{ \App\Config\Branding::PRIMARY_LIGHT_COLOR }}');
                    this.closest('.payment-method-option').querySelector('.checkmark').classList.remove('hidden');
                }

                // Show/hide bank transfer details
                if (this.value === 'bank_transfer') {
                    bankTransferDetails.classList.remove('hidden');
                    submitButton.disabled = true;
                } else {
                    bankTransferDetails.classList.add('hidden');
                    submitButton.disabled = false;
                }
            });
        });

        // Handle file upload
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileName.textContent = 'Selected: ' + e.target.files[0].name;
                submitButton.disabled = false;
            } else {
                fileName.textContent = '';
                submitButton.disabled = true;
            }
        });
    </script>
</body>
</html>
