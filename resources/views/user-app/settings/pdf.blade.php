@extends('layouts.user-app', ['title' => 'PDF Settings'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500">
        <a href="{{ route('user.settings') }}" class="hover:text-gray-700 transition-colors">Settings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">PDF Settings</span>
    </nav>

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">PDF Invoice Settings</h1>
            <p class="mt-1 text-sm text-gray-600">Customize your invoice PDF appearance and layout</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('user.settings') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Settings
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- PDF Settings Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-primary-50 to-brand-50 border-b border-primary-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v4a1 1 0 001 1h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">PDF Customization</h2>
                    <p class="text-sm text-gray-600">Design professional invoice PDFs with customizable templates</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('user.settings.pdf.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Template Selection -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Template Selection</h3>
                <p class="text-sm text-gray-600 mt-1">Choose from our professional invoice templates</p>
            </div>
            <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($templates as $key => $template)
                <div class="template-option cursor-pointer border-2 rounded-lg p-4 transition-all duration-200 {{ ($currentSettings['template'] ?? 'malaysian') === $key ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="pdf_settings[template]" value="{{ $key }}" id="template_{{ $key }}"
                           class="sr-only" {{ ($currentSettings['template'] ?? 'malaysian') === $key ? 'checked' : '' }}>
                    <label for="template_{{ $key }}" class="cursor-pointer">
                        <div class="h-24 bg-gray-100 rounded mb-3 flex items-center justify-center">
                            <span class="text-gray-500 text-sm">{{ $template['name'] }}</span>
                        </div>
                        <h4 class="font-medium text-sm">{{ $template['name'] }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $template['description'] }}</p>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Color Settings -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Color Scheme</h3>
                <p class="text-sm text-gray-600 mt-1">Customize your invoice colors and branding</p>
            </div>
            <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                    <input type="color" name="pdf_settings[colors][primary]"
                           value="{{ $currentSettings['colors']['primary'] ?? '#3B82F6' }}"
                           class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                    <input type="color" name="pdf_settings[colors][secondary]"
                           value="{{ $currentSettings['colors']['secondary'] ?? '#6B7280' }}"
                           class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                    <input type="color" name="pdf_settings[colors][accent]"
                           value="{{ $currentSettings['colors']['accent'] ?? '#10B981' }}"
                           class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Layout Settings -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Layout Settings</h3>
                <p class="text-sm text-gray-600 mt-1">Adjust fonts, spacing, and layout preferences</p>
            </div>
            <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Font Family</label>
                    <select name="pdf_settings[layout][font_family]" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="Arial" {{ ($currentSettings['layout']['font_family'] ?? 'Arial') === 'Arial' ? 'selected' : '' }}>Arial</option>
                        <option value="Times" {{ ($currentSettings['layout']['font_family'] ?? 'Arial') === 'Times' ? 'selected' : '' }}>Times New Roman</option>
                        <option value="Helvetica" {{ ($currentSettings['layout']['font_family'] ?? 'Arial') === 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                        <option value="Georgia" {{ ($currentSettings['layout']['font_family'] ?? 'Arial') === 'Georgia' ? 'selected' : '' }}>Georgia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Font Size</label>
                    <input type="number" name="pdf_settings[layout][font_size]"
                           value="{{ $currentSettings['layout']['font_size'] ?? 12 }}"
                           min="8" max="24"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Line Spacing</label>
                    <input type="number" name="pdf_settings[layout][line_spacing]"
                           value="{{ $currentSettings['layout']['line_spacing'] ?? 1.2 }}"
                           min="1" max="2" step="0.1"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
        </div>

        <!-- Section Settings -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Content Sections</h3>
                <p class="text-sm text-gray-600 mt-1">Choose which sections to display on your invoices</p>
            </div>
            <div class="p-6">
            <div class="space-y-3">
                @php
                    $sections = [
                        'show_customer_details' => 'Show Customer Details',
                        'show_payment_terms' => 'Show Payment Terms',
                        'show_notes' => 'Show Notes Section',
                        'show_item_descriptions' => 'Show Item Descriptions',
                        'show_tax_breakdown' => 'Show Tax Breakdown'
                    ];
                @endphp

                @foreach($sections as $key => $label)
                <div class="flex items-center">
                    <input type="checkbox" name="pdf_settings[sections][{{ $key }}]" value="1"
                           id="section_{{ $key }}"
                           {{ ($currentSettings['sections'][$key] ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="section_{{ $key }}" class="ml-2 text-sm text-gray-700">{{ $label }}</label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center space-x-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Settings
                </button>

                <button type="button" onclick="previewPdf()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview PDF
                </button>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('user.settings.pdf.reset') }}"
                   onclick="return confirm('Are you sure you want to reset all PDF settings to defaults?')"
                   class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 font-medium rounded-lg hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset to Defaults
                </a>
            </div>
        </div>
    </form>

    <!-- Logo Upload Section -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Company Logo for PDF</h3>
            <p class="text-sm text-gray-600 mt-1">Upload your company logo to appear on invoices</p>
        </div>
        <div class="p-6">

        @if($company->hasMedia('pdf_logo'))
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Current Logo:</p>
            <img src="{{ $company->getFirstMediaUrl('pdf_logo', 'thumb') }}" alt="Company Logo" class="h-20 w-auto border border-gray-200 rounded" onerror="this.style.display='none'">
            <form action="{{ route('user.settings.pdf.logo.remove') }}" method="POST" class="inline ml-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Remove Logo</button>
            </form>
        </div>
        @endif

        <form action="{{ route('user.settings.pdf.logo.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center space-x-4">
                <input type="file" name="logo" accept="image/jpeg,image/png,image/svg+xml"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Upload Logo
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Supported formats: JPEG, PNG, SVG. Max size: 2MB</p>
        </form>
    </div>
</div>

<script>
function previewPdf() {
    // Get form data
    const form = document.querySelector('form');
    const formData = new FormData(form);

    // Create preview form
    const previewForm = document.createElement('form');
    previewForm.method = 'POST';
    previewForm.action = '{{ route("user.settings.pdf.preview") }}';
    previewForm.target = '_blank';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    previewForm.appendChild(csrfToken);

    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        previewForm.appendChild(input);
    }

    document.body.appendChild(previewForm);
    previewForm.submit();
    document.body.removeChild(previewForm);
}

// Template selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const templateOptions = document.querySelectorAll('.template-option');

    templateOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            templateOptions.forEach(opt => {
                opt.classList.remove('border-primary-500', 'bg-primary-50');
                opt.classList.add('border-gray-200');
            });

            // Add active class to clicked option
            this.classList.remove('border-gray-200');
            this.classList.add('border-primary-500', 'bg-primary-50');

            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });
});
</script>
@endsection
